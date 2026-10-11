<?php
// Jumlah pesanan baru (badge di menu). Dibungkus try/catch supaya admin tetap
// jalan walaupun tabel pesanan belum dibuat.
$pesanan_baru = 0;
try {
    $pesanan_baru = (int) $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'menunggu'")->fetchColumn();
} catch (PDOException $e) {}

$menu_items = [
    ['page' => 'dashboard', 'url' => 'dashboard.php', 'icon' => 'fa-chart-line', 'label' => 'Dashboard'],
    ['page' => 'pesanan', 'url' => 'pesanan.php', 'icon' => 'fa-shopping-bag', 'label' => 'Pesanan', 'badge' => $pesanan_baru],
    ['page' => 'portfolio', 'url' => 'portfolio.php', 'icon' => 'fa-images', 'label' => 'Portfolio'],
    ['page' => 'produk', 'url' => 'produk.php', 'icon' => 'fa-tshirt', 'label' => 'Produk Kaos'],
    ['page' => 'tingkat', 'url' => 'tingkat.php', 'icon' => 'fa-palette', 'label' => 'Tingkat Sablon'],
    ['page' => 'pengaturan', 'url' => 'pengaturan.php', 'icon' => 'fa-cog', 'label' => 'Pengaturan'],
];
?>
<aside class="admin-sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">N</div>
        <div class="sidebar-logo-text">
            NARCISM
            <small>ADMIN</small>
        </div>
    </div>
    
    <ul class="sidebar-menu">
        <li class="sidebar-section">Menu Utama</li>
        <?php foreach ($menu_items as $item): ?>
        <li>
            <a href="<?= $item['url'] ?>" class="<?= strpos($current_page ?? '', $item['page']) === 0 ? 'active' : '' ?>">
                <i class="fas <?= $item['icon'] ?>"></i>
                <span><?= $item['label'] ?></span>
                <?php if (!empty($item['badge'])): ?>
                <span class="badge-count"><?= (int)$item['badge'] ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endforeach; ?>
        
        <li class="sidebar-section">Lainnya</li>
        <li>
            <a href="<?= BASE_URL ?>/index.php" target="_blank">
                <i class="fas fa-external-link-alt"></i>
                <span>Lihat Website</span>
            </a>
        </li>
        <li>
            <a href="logout.php" onclick="return confirm('Yakin mau logout?')">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('sidebarOverlay').classList.toggle('active');
}
</script>