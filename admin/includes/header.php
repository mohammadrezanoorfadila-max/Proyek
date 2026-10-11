<?php
require_once __DIR__ . '/auth.php';
$admin = currentAdmin();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Dashboard' ?> - Admin <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/admin/assets/admin.css">
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div style="display:flex;align-items:center;gap:15px">
            <button class="mobile-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-title">
                <h1><?= $page_title ?? 'Dashboard' ?></h1>
                <p><?= $page_subtitle ?? 'Selamat datang di panel admin' ?></p>
            </div>
        </div>
        <div class="topbar-actions">
            <div class="topbar-user">
                <div class="user-avatar"><?= strtoupper(substr($admin['nama'] ?: $admin['username'], 0, 1)) ?></div>
                <div class="user-info">
                    <strong><?= htmlspecialchars($admin['nama'] ?: $admin['username']) ?></strong>
                    <small>Administrator</small>
                </div>
            </div>
            <a href="logout.php" class="btn btn-secondary btn-sm" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
    
    <div class="admin-content">
        <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= $_SESSION['flash_success'] ?>
        </div>
        <?php unset($_SESSION['flash_success']); endif; ?>
        
        <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= $_SESSION['flash_error'] ?>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>