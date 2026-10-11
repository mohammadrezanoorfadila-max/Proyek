<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$id = $_GET['id'] ?? null;
if ($id) {
    $pdo->prepare("DELETE FROM tingkat WHERE id = ?")->execute([$id]);
    $_SESSION['flash_success'] = 'Tingkat berhasil dihapus!';
}
header('Location: tingkat.php');
exit;