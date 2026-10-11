<?php
/**
 * order-submit.php
 * Endpoint penyimpan pesanan. Dipanggil via fetch() dari assets/js/script.js
 * Balasan selalu JSON.
 */
session_start();
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json; charset=utf-8');

function jsonOut(array $data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['success' => false, 'message' => 'Metode tidak diizinkan.'], 405);
}

// ---------- Anti spam ----------
// 1) Honeypot: kolom "website" disembunyikan, bot biasanya mengisinya
if (!empty($_POST['website'])) {
    jsonOut(['success' => false, 'message' => 'Permintaan ditolak.'], 400);
}
// 2) Batasi: 1 pesanan tiap 20 detik per pengunjung
if (!empty($_SESSION['last_order_at']) && (time() - $_SESSION['last_order_at']) < 20) {
    jsonOut(['success' => false, 'message' => 'Mohon tunggu beberapa detik sebelum membuat pesanan lagi.'], 429);
}

// ---------- Ambil & bersihkan input ----------
function clean($key, $max = 255) {
    $v = trim((string)($_POST[$key] ?? ''));
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $v); // buang karakter kontrol
    return mb_substr($v, 0, $max);
}

$nama      = clean('nama', 100);
$no_wa_raw = clean('no_wa', 20);
$email     = clean('email', 100);
$metode    = clean('metode', 10);
$alamat    = clean('alamat', 500);
$kecamatan = clean('kecamatan', 100);
$kota      = clean('kota', 100);
$provinsi  = clean('provinsi', 100);
$kode_pos  = clean('kode_pos', 10);
$jenis     = clean('jenis', 50);
$tingkat   = clean('tingkat', 50);
$jumlah    = (int)($_POST['jumlah'] ?? 0);
$warna     = clean('warna_kaos', 100);
$ukuran    = clean('rincian_ukuran', 500);
$tanggal   = clean('tanggal_dibutuhkan', 10);
$catatan   = clean('catatan', 1000);
$setuju    = !empty($_POST['setuju']);

// ---------- Validasi ----------
$errors = [];

if (mb_strlen($nama) < 3) $errors['nama'] = 'Nama lengkap minimal 3 karakter.';

$no_wa = normalisasiNomorWA($no_wa_raw);
if (!$no_wa) $errors['no_wa'] = 'Nomor WhatsApp tidak valid. Contoh: 081234567890';

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Format email tidak valid.';
}

if (!in_array($metode, ['kirim', 'ambil'], true)) {
    $errors['metode'] = 'Pilih metode pengambilan.';
}

if ($metode === 'kirim') {
    if (mb_strlen($alamat) < 10) $errors['alamat']    = 'Alamat lengkap minimal 10 karakter (jalan, RT/RW, nomor rumah).';
    if ($kecamatan === '')       $errors['kecamatan'] = 'Kecamatan wajib diisi.';
    if ($kota === '')            $errors['kota']      = 'Kota/Kabupaten wajib diisi.';
    if ($provinsi === '')        $errors['provinsi']  = 'Provinsi wajib diisi.';
    if ($kode_pos !== '' && !preg_match('/^\d{5}$/', $kode_pos)) {
        $errors['kode_pos'] = 'Kode pos harus 5 angka.';
    }
} else {
    // ambil di toko: alamat tidak diperlukan
    $alamat = $kecamatan = $kota = $provinsi = $kode_pos = '';
}

if ($jumlah < MIN_ORDER)  $errors['jumlah'] = 'Minimal pemesanan ' . MIN_ORDER . ' pcs.';
if ($jumlah > 9999)       $errors['jumlah'] = 'Jumlah maksimal 9999 pcs.';

if ($warna === '') $errors['warna_kaos'] = 'Isi warna kaos yang diinginkan.';

$tanggal_db = null;
if ($tanggal !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$d || $d->format('Y-m-d') !== $tanggal) {
        $errors['tanggal_dibutuhkan'] = 'Format tanggal tidak valid.';
    } elseif ($d < new DateTime('today')) {
        $errors['tanggal_dibutuhkan'] = 'Tanggal tidak boleh sebelum hari ini.';
    } else {
        $tanggal_db = $tanggal;
    }
}

if (!$setuju) $errors['setuju'] = 'Centang persetujuan terlebih dahulu.';

// Harga SELALU dihitung ulang di server (jangan percaya angka dari browser)
$harga = null;
if (!isset($errors['jumlah'])) {
    $harga = hitungRincianHarga($jenis, $jumlah, $tingkat);
    if (!$harga) $errors['jenis'] = 'Jenis kaos atau tingkat sablon tidak valid. Silakan pilih ulang.';
}

// ---------- Upload file desain (opsional) ----------
$file_desain = null;
if (!empty($_FILES['file_desain']) && $_FILES['file_desain']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['file_desain'];

    if ($f['error'] !== UPLOAD_ERR_OK) {
        $errors['file_desain'] = ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE)
            ? 'Ukuran file terlalu besar (maks 5 MB).'
            : 'Gagal mengunggah file. Coba lagi.';
    } elseif ($f['size'] > 5 * 1024 * 1024) {
        $errors['file_desain'] = 'Ukuran file terlalu besar (maks 5 MB).';
    } else {
        // Cek tipe asli file dari isinya, bukan dari nama/ekstensi
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($f['tmp_name']);
        $allowed = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
            'application/pdf' => 'pdf',
        ];
        if (!isset($allowed[$mime])) {
            $errors['file_desain'] = 'Format file harus JPG, PNG, WEBP, atau PDF.';
        } else {
            $upload_dir = __DIR__ . '/assets/uploads/desain';
            if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
            $nama_file = 'desain_' . date('ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
            $file_desain = $nama_file; // dipindahkan setelah semua validasi lolos
            $tmp_upload  = $f['tmp_name'];
        }
    }
}

if ($errors) {
    jsonOut(['success' => false, 'message' => 'Data belum lengkap. Periksa kolom yang ditandai.', 'errors' => $errors], 422);
}

// ---------- Simpan ----------
if ($file_desain && !move_uploaded_file($tmp_upload, $upload_dir . '/' . $file_desain)) {
    jsonOut(['success' => false, 'message' => 'Gagal menyimpan file desain di server.'], 500);
}

$sql = "INSERT INTO pesanan
    (kode_pesanan, nama_pelanggan, no_wa, email, metode_pengambilan, alamat, kecamatan, kota, provinsi, kode_pos,
     produk_kode, produk_nama, tingkat_kode, tingkat_nama, jumlah, warna_kaos, rincian_ukuran,
     tanggal_dibutuhkan, catatan, file_desain, harga_satuan, subtotal, diskon_persen, diskon_nominal, total)
    VALUES (?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?, ?,?,?,?,?,?,?,?)";

$kode_pesanan = null;
for ($try = 0; $try < 5 && !$kode_pesanan; $try++) {
    $kode = generateKodePesanan();
    try {
        $pdo->prepare($sql)->execute([
            $kode, $nama, $no_wa, $email ?: null, $metode,
            $alamat ?: null, $kecamatan ?: null, $kota ?: null, $provinsi ?: null, $kode_pos ?: null,
            $jenis, $harga['produk_nama'], $tingkat, $harga['tingkat_nama'], $jumlah,
            $warna, $ukuran ?: null,
            $tanggal_db, $catatan ?: null, $file_desain,
            $harga['harga_satuan'], $harga['subtotal'], $harga['diskon_persen'], $harga['diskon_nominal'], $harga['total'],
        ]);
        $kode_pesanan = $kode;
    } catch (PDOException $e) {
        // 23000 = kode kembar -> coba kode lain. Error lain -> berhenti.
        if ($e->getCode() !== '23000') {
            if ($file_desain) @unlink($upload_dir . '/' . $file_desain);
            error_log('Order gagal: ' . $e->getMessage());
            jsonOut(['success' => false, 'message' => 'Terjadi kesalahan server. Silakan coba lagi.'], 500);
        }
    }
}

if (!$kode_pesanan) {
    if ($file_desain) @unlink($upload_dir . '/' . $file_desain);
    jsonOut(['success' => false, 'message' => 'Gagal membuat kode pesanan. Silakan coba lagi.'], 500);
}

$_SESSION['last_order_at'] = time();

$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE kode_pesanan = ?");
$stmt->execute([$kode_pesanan]);
$pesanan = $stmt->fetch();

jsonOut([
    'success'  => true,
    'kode'     => $kode_pesanan,
    'total'    => formatRupiah($pesanan['total']),
    'wa_text'  => buatPesanWAPesanan($pesanan),
]);
