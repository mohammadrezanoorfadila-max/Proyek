<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    // Ambil gambar dulu untuk dihapus
    $stmt = $pdo->prepare("SELECT gambar FROM portfolio WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    
    if ($data) {
        // Hapus file gambar kalau bukan URL eksternal
        if (!filter_var($data['gambar'], FILTER_VALIDATE_URL)) {
            $file = __DIR__ . '/../' . $data['gambar'];
            if (file_exists($file)) @unlink($file);
        }
        
        $pdo->prepare("DELETE FROM portfolio WHERE id = ?")->execute([$id]);
        $_SESSION['flash_success'] = 'Portfolio berhasil dihapus!';
    }
}

header('Location: portfolio.php');
exit;