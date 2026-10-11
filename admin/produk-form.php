<?php
$page_title = 'Form Produk';
$page_subtitle = 'Tambah atau edit produk kaos';
require_once __DIR__ . '/includes/header.php';

$id = $_GET['id'] ?? null;
$edit_mode = false;
$data = [
    'kode' => '',
    'nama' => '',
    'deskripsi' => '',
    'harga_dasar' => 45000,
    'ikon' => '👕',
    'aktif' => 1,
    'urutan' => 0
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) {
        $data = $found;
        $edit_mode = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = strtolower(trim($_POST['kode'] ?? ''));
    $kode = preg_replace('/[^a-z0-9_]/', '', $kode);
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga_dasar = (int)($_POST['harga_dasar'] ?? 0);
    $ikon = trim($_POST['ikon'] ?? '👕');
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    $urutan = (int)($_POST['urutan'] ?? 0);
    
    if (!$kode || !$nama || $harga_dasar <= 0) {
        $_SESSION['flash_error'] = 'Kode, nama, dan harga wajib diisi dengan benar!';
    } else {
        try {
            if ($edit_mode) {
                $stmt = $pdo->prepare("UPDATE produk SET kode=?, nama=?, deskripsi=?, harga_dasar=?, ikon=?, aktif=?, urutan=? WHERE id=?");
                $stmt->execute([$kode, $nama, $deskripsi, $harga_dasar, $ikon, $aktif, $urutan, $id]);
                $_SESSION['flash_success'] = 'Produk berhasil diperbarui!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO produk (kode, nama, deskripsi, harga_dasar, ikon, aktif, urutan) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$kode, $nama, $deskripsi, $harga_dasar, $ikon, $aktif, $urutan]);
                $_SESSION['flash_success'] = 'Produk berhasil ditambahkan!';
            }
            header('Location: produk.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Kode produk sudah digunakan! Pakai kode lain.';
        }
    }
}

$emoji_options = ['👕','👔','🧥','👚','🎽','🥋','👗','🧣','🎩','👒','🩳','👖'];
?>

<div class="page-header">
    <div>
        <h2><?= $edit_mode ? 'Edit Produk' : 'Tambah Produk' ?></h2>
        <p><?= $edit_mode ? 'Ubah data produk kaos' : 'Tambahkan jenis kaos baru' ?></p>
    </div>
    <a href="produk.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<form method="POST" class="form-card">
    <div class="form-row">
        <div class="form-group">
            <label>Kode Produk <span class="required">*</span></label>
            <input type="text" name="kode" class="form-control" 
                   value="<?= htmlspecialchars($data['kode']) ?>" 
                   placeholder="cth: reguler, boxy" required>
            <p class="form-hint">Huruf kecil, tanpa spasi (pakai _ kalau perlu)</p>
        </div>
        <div class="form-group">
            <label>Nama Produk <span class="required">*</span></label>
            <input type="text" name="nama" class="form-control" 
                   value="<?= htmlspecialchars($data['nama']) ?>" 
                   placeholder="cth: Kaos Reguler" required>
        </div>
    </div>
    
    <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control" 
                  placeholder="Deskripsi produk..."><?= htmlspecialchars($data['deskripsi']) ?></textarea>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label>Harga Dasar (Rp) <span class="required">*</span></label>
            <input type="number" name="harga_dasar" class="form-control" 
                   value="<?= (int)$data['harga_dasar'] ?>" min="0" step="1000" required>
        </div>
        <div class="form-group">
            <label>Urutan Tampil</label>
            <input type="number" name="urutan" class="form-control" 
                   value="<?= (int)$data['urutan'] ?>" min="0">
        </div>
    </div>
    
    <div class="form-group">
        <label>Ikon</label>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <input type="text" name="ikon" class="form-control" 
                   value="<?= htmlspecialchars($data['ikon']) ?>" 
                   style="max-width:100px;text-align:center;font-size:24px" maxlength="4">
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                <?php foreach ($emoji_options as $emoji): ?>
                <button type="button" class="btn btn-secondary btn-sm" 
                        onclick="document.querySelector('[name=ikon]').value='<?= $emoji ?>'"
                        style="font-size:20px;padding:6px 10px">
                    <?= $emoji ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
            <input type="checkbox" name="aktif" value="1" <?= $data['aktif'] ? 'checked' : '' ?>
                   style="width:18px;height:18px;cursor:pointer">
            <span>Produk Aktif (tampil di website)</span>
        </label>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= $edit_mode ? 'Update' : 'Simpan' ?>
        </button>
        <a href="produk.php" class="btn btn-secondary">Batal</a>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>