<?php
require_once __DIR__ . '/config.php';

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function getProdukAktif() {
    global $pdo;
    return $pdo->query("SELECT * FROM produk WHERE aktif = 1 ORDER BY urutan, id")->fetchAll();
}

function getTingkatAktif() {
    global $pdo;
    return $pdo->query("SELECT * FROM tingkat WHERE aktif = 1 ORDER BY id")->fetchAll();
}

function getPortfolioList($limit = null) {
    global $pdo;
    $sql = "SELECT * FROM portfolio ORDER BY urutan, id DESC";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    return $pdo->query($sql)->fetchAll();
}

function hitungHarga($jenis_kode, $jumlah, $tingkat_kode) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT harga_dasar FROM produk WHERE kode = ? AND aktif = 1");
    $stmt->execute([$jenis_kode]);
    $produk = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT tambahan_harga FROM tingkat WHERE kode = ? AND aktif = 1");
    $stmt->execute([$tingkat_kode]);
    $tingkat = $stmt->fetch();
    
    if (!$produk || !$tingkat) return 0;
    
    $harga_satuan = $produk['harga_dasar'] + $tingkat['tambahan_harga'];
    $total = $harga_satuan * $jumlah;
    
    if ($jumlah >= 100) $total *= 0.85;
    elseif ($jumlah >= 50) $total *= 0.90;
    elseif ($jumlah >= 24) $total *= 0.95;
    
    return round($total);
}

// ============================================================
// FITUR PESANAN (data diri pemesan)
// ============================================================

/**
 * Hitung rincian harga lengkap (dipakai server saat menyimpan pesanan).
 * Aturan diskon sama persis dengan hitungHarga() & script.js.
 * Return null kalau produk / tingkat tidak ditemukan.
 */
function hitungRincianHarga($jenis_kode, $jumlah, $tingkat_kode) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT nama, harga_dasar FROM produk WHERE kode = ? AND aktif = 1");
    $stmt->execute([$jenis_kode]);
    $produk = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT nama, tambahan_harga FROM tingkat WHERE kode = ? AND aktif = 1");
    $stmt->execute([$tingkat_kode]);
    $tingkat = $stmt->fetch();

    if (!$produk || !$tingkat) return null;

    $harga_satuan = (int)$produk['harga_dasar'] + (int)$tingkat['tambahan_harga'];
    $subtotal     = $harga_satuan * $jumlah;

    $diskon_persen = 0;
    if ($jumlah >= 100)     $diskon_persen = 15;
    elseif ($jumlah >= 50)  $diskon_persen = 10;
    elseif ($jumlah >= 24)  $diskon_persen = 5;

    $diskon_nominal = (int) round($subtotal * $diskon_persen / 100);

    return [
        'produk_nama'    => $produk['nama'],
        'tingkat_nama'   => $tingkat['nama'],
        'harga_satuan'   => $harga_satuan,
        'subtotal'       => $subtotal,
        'diskon_persen'  => $diskon_persen,
        'diskon_nominal' => $diskon_nominal,
        'total'          => $subtotal - $diskon_nominal,
    ];
}

/** Buat kode pesanan unik, contoh: NRC-261001-K7M2 */
function generateKodePesanan() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // tanpa 0/O/1/I biar tidak membingungkan
    $acak = '';
    for ($i = 0; $i < 4; $i++) {
        $acak .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return 'NRC-' . date('ymd') . '-' . $acak;
}

/** Normalisasi nomor WA ke format 62xxxxxxxxxx. Return null kalau tidak valid. */
function normalisasiNomorWA($input) {
    $no = preg_replace('/\D+/', '', (string)$input);
    if ($no === '') return null;

    if (strpos($no, '62') === 0)      { /* sudah 62... */ }
    elseif (strpos($no, '0') === 0)   $no = '62' . substr($no, 1);
    elseif (strpos($no, '8') === 0)   $no = '62' . $no;
    else return null;

    // nomor seluler Indonesia: 62 + 8xx + 7-11 digit
    if (!preg_match('/^628\d{8,12}$/', $no)) return null;
    return $no;
}

/** Daftar status pesanan: kode => [label, kelas badge admin] */
function daftarStatusPesanan() {
    return [
        'menunggu'     => ['Menunggu Konfirmasi', 'warning'],
        'dikonfirmasi' => ['Dikonfirmasi',        'info'],
        'diproses'     => ['Sedang Diproses',     'purple'],
        'selesai'      => ['Selesai',             'success'],
        'dibatalkan'   => ['Dibatalkan',          'danger'],
    ];
}

/** Susun teks pesan WhatsApp dari satu baris data pesanan */
function buatPesanWAPesanan(array $p) {
    $t  = "Halo *Narcism Sablon*! 👋\n";
    $t .= "Saya sudah membuat pesanan di website dengan kode *{$p['kode_pesanan']}*.\n\n";

    $t .= "👤 *DATA PEMESAN*\n";
    $t .= "Nama: {$p['nama_pelanggan']}\n";
    $t .= "No. WA: {$p['no_wa']}\n";
    if (!empty($p['email'])) $t .= "Email: {$p['email']}\n";

    $t .= "\n📍 *PENGAMBILAN*\n";
    if ($p['metode_pengambilan'] === 'kirim') {
        $t .= "Dikirim ke alamat:\n{$p['alamat']}\n";
        $wilayah = array_filter([$p['kecamatan'], $p['kota'], $p['provinsi'], $p['kode_pos']]);
        if ($wilayah) $t .= implode(', ', $wilayah) . "\n";
    } else {
        $t .= "Ambil sendiri di toko\n";
    }

    $t .= "\n🧾 *DETAIL PESANAN*\n";
    $t .= "Jenis Kaos: {$p['produk_nama']}\n";
    $t .= "Tingkat Sablon: {$p['tingkat_nama']}\n";
    $t .= "Jumlah: {$p['jumlah']} pcs\n";
    if (!empty($p['warna_kaos']))   $t .= "Warna Kaos: {$p['warna_kaos']}\n";
    if (!empty($p['rincian_ukuran'])) $t .= "Rincian Ukuran: {$p['rincian_ukuran']}\n";
    if (!empty($p['tanggal_dibutuhkan'])) $t .= "Dibutuhkan tanggal: " . date('d-m-Y', strtotime($p['tanggal_dibutuhkan'])) . "\n";
    if (!empty($p['catatan']))      $t .= "Catatan: {$p['catatan']}\n";
    if (!empty($p['file_desain']))  $t .= "File desain: sudah diunggah di website\n";

    $t .= "\n💵 Harga Satuan: " . formatRupiah($p['harga_satuan']) . "\n";
    $t .= "💰 Subtotal: " . formatRupiah($p['subtotal']) . "\n";
    if ($p['diskon_nominal'] > 0) {
        $t .= "🎁 Diskon ({$p['diskon_persen']}%): -" . formatRupiah($p['diskon_nominal']) . "\n";
    }
    $t .= "✅ *Total Estimasi: " . formatRupiah($p['total']) . "*\n\n";
    $t .= "Mohon dikonfirmasi ya. Terima kasih! 🙏";
    return $t;
}
