<?php
$page_title = 'Form Tingkat';
$page_subtitle = 'Tambah atau edit tingkat sablon';
require_once __DIR__ . '/includes/header.php';

$id = $_GET['id'] ?? null;
$edit_mode = false;
$data = [
    'kode' => '',
    'nama' => '',
    'deskripsi' => '',
    'tambahan_harga' => 15000,
    'ikon' => '🎨',
    'aktif' => 1
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM tingkat WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) { $data = $found; $edit_mode = true; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = strtolower(trim($_POST['kode'] ?? ''));
    $kode = preg_replace('/[^a-z0-9_]/', '', $kode);
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tambahan_harga = (int)($_POST['tambahan_harga'] ?? 0);
    $ikon = trim($_POST['ikon'] ?? '🎨');
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
    if (!$kode || !$nama || $tambahan_harga < 0) {
        $_SESSION['flash_error'] = 'Kode, nama, dan tambahan harga wajib diisi!';
    } else {
        try {
            if ($edit_mode) {
                $stmt = $pdo->prepare("UPDATE tingkat SET kode=?, nama=?, deskripsi=?, tambahan_harga=?, ikon=?, aktif=? WHERE id=?");
                $stmt->execute([$kode, $nama, $deskripsi, $tambahan_harga, $ikon, $aktif, $id]);
                $_SESSION['flash_success'] = 'Tingkat berhasil diperbarui!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO tingkat (kode, nama, deskripsi, tambahan_harga, ikon, aktif) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$kode, $nama, $deskripsi, $tambahan_harga, $ikon, $aktif]);
                $_SESSION['flash_success'] = 'Tingkat berhasil ditambahkan!';
            }
            header('Location: tingkat.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Kode sudah digunakan!';
        }
    }
}

$emoji_options = ['🎨','🖌️','✨','🖍️','🎭','🌈','⭐','💎','🔥','🌟'];
?>

<div class="page-header">
    <div>
        <h2><?= $edit_mode ? 'Edit Tingkat' : 'Tambah Tingkat' ?></h2>
        <p><?= $edit_mode ? 'Ubah data tingkat sablon' : 'Tambahkan tingkat kerumitan baru' ?></p>
    </div>
    <a href="tingkat.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<form method="POST" class="form-card">
    <div class="form-row">
        <div class="form-group">
            <label>Kode <span class="required">*</span></label>
            <input type="text" name="kode" class="form-control" 
                   value="<?= htmlspecialchars($data['kode']) ?>" 
                   placeholder="cth: normal, rumit" required>
        </div>
        <div class="form-group">
            <label>Nama <span class="required">*</span></label>
            <input type="text" name="nama" class="form-control" 
                   value="<?= htmlspecialchars($data['nama']) ?>" 
                   placeholder="cth: Sablon Normal" required>
        </div>
    </div>
    
    <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control"><?= htmlspecialchars($data['deskripsi']) ?></textarea>
    </div>
    
    <div class="form-group">
        <label>Tambahan Harga (Rp) <span class="required">*</span></label>
        <input type="number" name="tambahan_harga" class="form-control" 
               value="<?= (int)$data['tambahan_harga'] ?>" min="0" step="1000" required>
        <p class="form-hint">Harga ini akan ditambahkan ke setiap kaos</p>
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
            <span>Aktif</span>
        </label>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= $edit_mode ? 'Update' : 'Simpan' ?>
        </button>
        <a href="tingkat.php" class="btn btn-secondary">Batal</a>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>