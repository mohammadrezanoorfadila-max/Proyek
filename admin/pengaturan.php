<?php
$page_title = 'Pengaturan';
$page_subtitle = 'Konfigurasi umum website';
require_once __DIR__ . '/includes/header.php';

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['whatsapp_number', 'site_name', 'min_order', 'alamat', 'email'];
    foreach ($fields as $field) {
        $val = trim($_POST[$field] ?? '');
        $stmt = $pdo->prepare("INSERT INTO pengaturan (nama_key, nilai) VALUES (?, ?) 
                                ON DUPLICATE KEY UPDATE nilai = ?");
        $stmt->execute([$field, $val, $val]);
    }
    
    // Update password kalau diisi
    if (!empty($_POST['new_password'])) {
        $new_pass = $_POST['new_password'];
        if (strlen($new_pass) >= 6) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?")
                ->execute([$hash, $_SESSION['admin_id']]);
            $_SESSION['flash_success'] = 'Pengaturan & password berhasil diperbarui!';
        } else {
            $_SESSION['flash_error'] = 'Password minimal 6 karakter!';
        }
    } else {
        $_SESSION['flash_success'] = 'Pengaturan berhasil disimpan!';
    }
    header('Location: pengaturan.php');
    exit;
}

// Get current settings
$settings = [];
$rows = $pdo->query("SELECT nama_key, nilai FROM pengaturan")->fetchAll();
foreach ($rows as $r) $settings[$r['nama_key']] = $r['nilai'];
?>

<div class="page-header">
    <div>
        <h2>Pengaturan Website</h2>
        <p>Konfigurasi umum dan kontak</p>
    </div>
</div>

<form method="POST" class="form-card">
    <h3 style="margin-bottom:20px;font-size:16px">
        <i class="fas fa-globe" style="color:#FF5722"></i> Informasi Website
    </h3>
    
    <div class="form-row">
        <div class="form-group">
            <label>Nama Website</label>
            <input type="text" name="site_name" class="form-control" 
                   value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Nomor WhatsApp</label>
            <input type="text" name="whatsapp_number" class="form-control" 
                   value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '') ?>"
                   placeholder="6281234567890">
            <p class="form-hint">Format: 628xxx (tanpa + atau 0)</p>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" 
                   value="<?= htmlspecialchars($settings['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Minimal Order (pcs)</label>
            <input type="number" name="min_order" class="form-control" 
                   value="<?= (int)($settings['min_order'] ?? 12) ?>" min="1">
        </div>
    </div>
    
    <div class="form-group">
        <label>Alamat</label>
        <textarea name="alamat" class="form-control"><?= htmlspecialchars($settings['alamat'] ?? '') ?></textarea>
    </div>
    
    <h3 style="margin:30px 0 20px;font-size:16px">
        <i class="fas fa-lock" style="color:#FF5722"></i> Ganti Password (opsional)
    </h3>
    
    <div class="form-group">
        <label>Password Baru</label>
        <input type="password" name="new_password" class="form-control" 
               placeholder="Kosongkan kalau tidak ingin ganti">
        <p class="form-hint">Minimal 6 karakter</p>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Pengaturan
        </button>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>