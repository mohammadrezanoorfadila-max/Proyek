<?php
$page_title = 'Form Portfolio';
$page_subtitle = 'Tambah atau edit data portfolio';
require_once __DIR__ . '/includes/header.php';

$id = $_GET['id'] ?? null;
$edit_mode = false;
$data = [
    'judul' => '',
    'kategori' => 'Komunitas',
    'deskripsi' => '',
    'gambar' => '',
    'urutan' => 0
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) {
        $data = $found;
        $edit_mode = true;
    }
}

// Kategori yang tersedia
$kategori_list = ['Komunitas', 'Seragam', 'Merchandise', 'Event', 'Corporate', 'Distro', 'Lainnya'];

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $kategori = $_POST['kategori'] ?? 'Lainnya';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $urutan = (int)($_POST['urutan'] ?? 0);
    $gambar = $data['gambar']; // default gambar lama
    
    // Handle upload gambar
    if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $ext = strtolower(pathinfo($_FILES['gambar_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($ext, $allowed)) {
            $_SESSION['flash_error'] = 'Format gambar tidak didukung! Gunakan JPG, PNG, GIF, atau WEBP.';
            header('Location: portfolio-form.php' . ($id ? '?id=' . $id : ''));
            exit;
        }
        
        if ($_FILES['gambar_file']['size'] > 5 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'Ukuran gambar maksimal 5MB!';
            header('Location: portfolio-form.php' . ($id ? '?id=' . $id : ''));
            exit;
        }
        
        $filename = 'portfolio_' . time() . '_' . uniqid() . '.' . $ext;
        $upload_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['gambar_file']['tmp_name'], $upload_path)) {
            // Hapus gambar lama kalau ada & bukan URL
            if ($gambar && !filter_var($gambar, FILTER_VALIDATE_URL) && file_exists(__DIR__ . '/../' . $gambar)) {
                @unlink(__DIR__ . '/../' . $gambar);
            }
            $gambar = 'assets/uploads/' . $filename;
        }
    } elseif (!empty($_POST['gambar_url'])) {
        $gambar = trim($_POST['gambar_url']);
    }
    
    if (!$judul || !$gambar) {
        $_SESSION['flash_error'] = 'Judul dan gambar wajib diisi!';
    } else {
        if ($edit_mode) {
            $stmt = $pdo->prepare("UPDATE portfolio SET judul=?, kategori=?, deskripsi=?, gambar=?, urutan=? WHERE id=?");
            $stmt->execute([$judul, $kategori, $deskripsi, $gambar, $urutan, $id]);
            $_SESSION['flash_success'] = 'Portfolio berhasil diperbarui!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO portfolio (judul, kategori, deskripsi, gambar, urutan) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $kategori, $deskripsi, $gambar, $urutan]);
            $_SESSION['flash_success'] = 'Portfolio berhasil ditambahkan!';
        }
        header('Location: portfolio.php');
        exit;
    }
}
?>

<div class="page-header">
    <div>
        <h2><?= $edit_mode ? 'Edit Portfolio' : 'Tambah Portfolio' ?></h2>
        <p><?= $edit_mode ? 'Ubah data portfolio' : 'Tambahkan portfolio baru' ?></p>
    </div>
    <a href="portfolio.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<form method="POST" enctype="multipart/form-data" class="form-card">
    <div class="form-group">
        <label>Judul Portfolio <span class="required">*</span></label>
        <input type="text" name="judul" class="form-control" 
               value="<?= htmlspecialchars($data['judul']) ?>" 
               placeholder="Contoh: Kaos Komunitas Motor" required>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label>Kategori <span class="required">*</span></label>
            <select name="kategori" class="form-control" required>
                <?php foreach ($kategori_list as $k): ?>
                <option value="<?= $k ?>" <?= $data['kategori'] === $k ? 'selected' : '' ?>>
                    <?= $k ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Urutan Tampil</label>
            <input type="number" name="urutan" class="form-control" 
                   value="<?= (int)$data['urutan'] ?>" min="0">
            <p class="form-hint">Semakin kecil angka, semakin atas posisinya</p>
        </div>
    </div>
    
    <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control" 
                  placeholder="Deskripsi singkat portfolio..."><?= htmlspecialchars($data['deskripsi']) ?></textarea>
    </div>
    
    <div class="form-group">
        <label>Gambar <span class="required">*</span></label>
        
        <?php if ($data['gambar']): ?>
        <div class="image-preview" id="currentImage">
            <img src="<?= htmlspecialchars($data['gambar']) ?>" alt="Current">
        </div>
        <p class="form-hint" style="margin-bottom:15px">Gambar saat ini. Upload baru untuk mengganti.</p>
        <?php endif; ?>
        
        <label for="gambar_file" class="image-upload" style="display:block">
            <div class="image-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <strong>Klik untuk upload gambar</strong>
            <p class="form-hint">JPG, PNG, GIF, WEBP (max 5MB)</p>
            <input type="file" name="gambar_file" id="gambar_file" 
                   accept="image/*" onchange="previewImage(this, 'preview')">
        </label>
        <div id="preview" class="image-preview" style="display:none"></div>
        
        <p class="form-hint" style="margin:15px 0 8px">Atau gunakan URL gambar:</p>
        <input type="text" name="gambar_url" class="form-control" 
               value="<?= filter_var($data['gambar'], FILTER_VALIDATE_URL) ? htmlspecialchars($data['gambar']) : '' ?>"
               placeholder="https://contoh.com/gambar.jpg">
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= $edit_mode ? 'Update' : 'Simpan' ?>
        </button>
        <a href="portfolio.php" class="btn btn-secondary">Batal</a>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>