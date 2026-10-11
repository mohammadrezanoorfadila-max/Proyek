<?php
// auth.php dimuat dulu (session + cek login) supaya redirect setelah POST aman
require_once __DIR__ . '/includes/auth.php';

$status_list = daftarStatusPesanan();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id = ?");
$stmt->execute([$id]);
$o = $stmt->fetch();

if (!$o) {
    $_SESSION['flash_error'] = 'Pesanan tidak ditemukan.';
    header('Location: pesanan.php');
    exit;
}

// ---- Handle POST: ubah status / hapus ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'update') {
        $status = $_POST['status'] ?? '';
        if (isset($status_list[$status])) {
            $catatan_admin = trim($_POST['catatan_admin'] ?? '');
            $pdo->prepare("UPDATE pesanan SET status = ?, catatan_admin = ? WHERE id = ?")
                ->execute([$status, $catatan_admin ?: null, $id]);
            $_SESSION['flash_success'] = 'Status pesanan berhasil diperbarui!';
        } else {
            $_SESSION['flash_error'] = 'Status tidak valid.';
        }
        header('Location: pesanan-detail.php?id=' . $id);
        exit;
    }

    if ($aksi === 'hapus') {
        if (!empty($o['file_desain'])) {
            $path = __DIR__ . '/../assets/uploads/desain/' . basename($o['file_desain']);
            if (is_file($path)) @unlink($path);
        }
        $pdo->prepare("DELETE FROM pesanan WHERE id = ?")->execute([$id]);
        $_SESSION['flash_success'] = 'Pesanan ' . $o['kode_pesanan'] . ' berhasil dihapus.';
        header('Location: pesanan.php');
        exit;
    }
}

$page_title = 'Detail Pesanan';
$page_subtitle = $o['kode_pesanan'];
require_once __DIR__ . '/includes/header.php';

$st = $status_list[$o['status']];
$file_url = $o['file_desain'] ? BASE_URL . '/assets/uploads/desain/' . rawurlencode($o['file_desain']) : null;
$file_is_pdf = $o['file_desain'] && strtolower(pathinfo($o['file_desain'], PATHINFO_EXTENSION)) === 'pdf';

// Pesan WA ke pelanggan
$pesan_wa = "Halo {$o['nama_pelanggan']}, kami dari Narcism Sablon. "
          . "Terkait pesanan Anda dengan kode {$o['kode_pesanan']} ({$o['produk_nama']} x {$o['jumlah']} pcs, total " . formatRupiah($o['total']) . "). ";
$wa_link = 'https://wa.me/' . $o['no_wa'] . '?text=' . rawurlencode($pesan_wa);
?>

<div class="page-header">
    <div>
        <h2>Pesanan <?= htmlspecialchars($o['kode_pesanan']) ?></h2>
        <p>Dibuat <?= date('d M Y, H:i', strtotime($o['created_at'])) ?> &middot;
           <span class="badge <?= $st[1] ?>"><?= $st[0] ?></span></p>
    </div>
    <a href="pesanan.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="detail-layout">
    <div>
        <div class="detail-card">
            <h3><i class="fas fa-user"></i> Data Pemesan</h3>
            <div class="detail-row"><span>Nama</span><strong><?= htmlspecialchars($o['nama_pelanggan']) ?></strong></div>
            <div class="detail-row"><span>WhatsApp</span><strong><?= htmlspecialchars($o['no_wa']) ?></strong></div>
            <div class="detail-row"><span>Email</span><strong><?= $o['email'] ? htmlspecialchars($o['email']) : '-' ?></strong></div>
        </div>

        <div class="detail-card">
            <h3><i class="fas fa-map-marker-alt"></i> Pengambilan</h3>
            <div class="detail-row">
                <span>Metode</span>
                <strong><?= $o['metode_pengambilan'] === 'kirim' ? 'Kirim ke alamat' : 'Ambil di toko' ?></strong>
            </div>
            <?php if ($o['metode_pengambilan'] === 'kirim'): ?>
            <div class="detail-row"><span>Alamat</span><strong><?= nl2br(htmlspecialchars($o['alamat'])) ?></strong></div>
            <div class="detail-row"><span>Kecamatan</span><strong><?= htmlspecialchars($o['kecamatan']) ?></strong></div>
            <div class="detail-row"><span>Kota / Kab.</span><strong><?= htmlspecialchars($o['kota']) ?></strong></div>
            <div class="detail-row"><span>Provinsi</span><strong><?= htmlspecialchars($o['provinsi']) ?></strong></div>
            <div class="detail-row"><span>Kode Pos</span><strong><?= $o['kode_pos'] ? htmlspecialchars($o['kode_pos']) : '-' ?></strong></div>
            <?php endif; ?>
        </div>

        <div class="detail-card">
            <h3><i class="fas fa-tshirt"></i> Detail Pesanan</h3>
            <div class="detail-row"><span>Jenis Kaos</span><strong><?= htmlspecialchars($o['produk_nama']) ?></strong></div>
            <div class="detail-row"><span>Tingkat Sablon</span><strong><?= htmlspecialchars($o['tingkat_nama']) ?></strong></div>
            <div class="detail-row"><span>Jumlah</span><strong><?= (int)$o['jumlah'] ?> pcs</strong></div>
            <div class="detail-row"><span>Warna Kaos</span><strong><?= htmlspecialchars($o['warna_kaos'] ?: '-') ?></strong></div>
            <div class="detail-row"><span>Rincian Ukuran</span><strong><?= htmlspecialchars($o['rincian_ukuran'] ?: '-') ?></strong></div>
            <div class="detail-row">
                <span>Tgl. Dibutuhkan</span>
                <strong><?= $o['tanggal_dibutuhkan'] ? date('d M Y', strtotime($o['tanggal_dibutuhkan'])) : '-' ?></strong>
            </div>
            <div class="detail-row"><span>Catatan</span><strong><?= $o['catatan'] ? nl2br(htmlspecialchars($o['catatan'])) : '-' ?></strong></div>
            <div class="detail-row">
                <span>File Desain</span>
                <strong>
                    <?php if ($file_url): ?>
                        <a href="<?= $file_url ?>" target="_blank" class="btn btn-secondary btn-sm">
                            <i class="fas fa-<?= $file_is_pdf ? 'file-pdf' : 'download' ?>"></i> Buka / Unduh Desain
                        </a>
                        <?php if (!$file_is_pdf): ?><br><img src="<?= $file_url ?>" alt="Desain" class="design-preview"><?php endif; ?>
                    <?php else: ?>-<?php endif; ?>
                </strong>
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card">
            <h3><i class="fas fa-receipt"></i> Rincian Harga</h3>
            <div class="detail-row"><span>Harga Satuan</span><strong><?= formatRupiah($o['harga_satuan']) ?></strong></div>
            <div class="detail-row"><span>Subtotal</span><strong><?= formatRupiah($o['subtotal']) ?></strong></div>
            <?php if ($o['diskon_nominal'] > 0): ?>
            <div class="detail-row"><span>Diskon (<?= (int)$o['diskon_persen'] ?>%)</span><strong>-<?= formatRupiah($o['diskon_nominal']) ?></strong></div>
            <?php endif; ?>
            <div class="detail-row total"><span>Total</span><strong><?= formatRupiah($o['total']) ?></strong></div>
            <p style="font-size:12px;color:#6B7280;margin-top:8px">*Belum termasuk ongkos kirim.</p>
        </div>

        <div class="detail-card">
            <h3><i class="fas fa-tasks"></i> Kelola Pesanan</h3>
            <form method="POST">
                <input type="hidden" name="aksi" value="update">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <?php foreach ($status_list as $kode => $info): ?>
                        <option value="<?= $kode ?>" <?= $o['status'] === $kode ? 'selected' : '' ?>><?= $info[0] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Catatan Admin <small style="font-weight:400;color:#6B7280">(internal)</small></label>
                    <textarea name="catatan_admin" class="form-control" placeholder="Mis. ongkir, DP, jadwal produksi..."><?= htmlspecialchars($o['catatan_admin'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </form>
            <a href="<?= $wa_link ?>" target="_blank" class="btn btn-success" style="width:100%;justify-content:center;margin-top:12px">
                <i class="fab fa-whatsapp"></i> Hubungi Pelanggan
            </a>
            <form method="POST" onsubmit="return confirmDelete('Hapus pesanan ini secara permanen?')" style="margin-top:12px">
                <input type="hidden" name="aksi" value="hapus">
                <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center">
                    <i class="fas fa-trash"></i> Hapus Pesanan
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
