<?php
$page_title = 'Dashboard';
$page_subtitle = 'Statistik dan overview website';
require_once __DIR__ . '/includes/header.php';

// Ambil statistik
$total_portfolio = $pdo->query("SELECT COUNT(*) FROM portfolio")->fetchColumn();
$total_produk = $pdo->query("SELECT COUNT(*) FROM produk WHERE aktif = 1")->fetchColumn();
$total_tingkat = $pdo->query("SELECT COUNT(*) FROM tingkat WHERE aktif = 1")->fetchColumn();
$total_admin = $pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn();

// Statistik pesanan (aman kalau tabel pesanan belum dibuat)
$total_pesanan = $pesanan_menunggu = 0;
$recent_pesanan = [];
try {
    $total_pesanan    = (int) $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
    $pesanan_menunggu = (int) $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'menunggu'")->fetchColumn();
    $recent_pesanan   = $pdo->query("SELECT * FROM pesanan ORDER BY id DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {}
$status_list = daftarStatusPesanan();

// Portfolio terbaru
$recent_portfolio = $pdo->query("SELECT * FROM portfolio ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-card-icon"><i class="fas fa-bell"></i></div>
        <h3><?= $pesanan_menunggu ?></h3>
        <p>Pesanan Menunggu Konfirmasi</p>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon"><i class="fas fa-shopping-bag"></i></div>
        <h3><?= $total_pesanan ?></h3>
        <p>Total Pesanan Masuk</p>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon"><i class="fas fa-images"></i></div>
        <h3><?= $total_portfolio ?></h3>
        <p>Total Portfolio</p>
    </div>
    <div class="stat-card blue">
        <div class="stat-card-icon"><i class="fas fa-tshirt"></i></div>
        <h3><?= $total_produk ?></h3>
        <p>Produk Kaos Aktif</p>
    </div>
    <div class="stat-card yellow">
        <div class="stat-card-icon"><i class="fas fa-palette"></i></div>
        <h3><?= $total_tingkat ?></h3>
        <p>Tingkat Sablon</p>
    </div>
    <div class="stat-card purple">
        <div class="stat-card-icon"><i class="fas fa-users"></i></div>
        <h3><?= $total_admin ?></h3>
        <p>Admin Terdaftar</p>
    </div>
</div>

<div class="table-wrapper" style="margin-bottom:30px">
    <div class="table-header">
        <h3>Pesanan Terbaru</h3>
        <a href="pesanan.php" class="btn btn-primary btn-sm">Lihat Semua</a>
    </div>
    <?php if (empty($recent_pesanan)): ?>
    <div class="empty-state">
        <i class="fas fa-shopping-bag"></i>
        <h4>Belum ada pesanan masuk</h4>
        <p>Pesanan dari website akan muncul di sini</p>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Pemesan</th>
                <th>Pesanan</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_pesanan as $o): ?>
            <tr>
                <td><code class="kode-pesanan"><?= htmlspecialchars($o['kode_pesanan']) ?></code></td>
                <td><strong><?= htmlspecialchars($o['nama_pelanggan']) ?></strong></td>
                <td><?= htmlspecialchars($o['produk_nama']) ?> &times; <?= (int)$o['jumlah'] ?></td>
                <td><strong style="color:#FF5722"><?= formatRupiah($o['total']) ?></strong></td>
                <td><span class="badge <?= $status_list[$o['status']][1] ?>"><?= $status_list[$o['status']][0] ?></span></td>
                <td>
                    <a href="pesanan-detail.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="table-wrapper">
    <div class="table-header">
        <h3>Portfolio Terbaru</h3>
        <a href="portfolio-form.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Portfolio
        </a>
    </div>
    
    <?php if (empty($recent_portfolio)): ?>
    <div class="empty-state">
        <i class="fas fa-images"></i>
        <h4>Belum ada portfolio</h4>
        <p>Klik tombol "Tambah Portfolio" untuk menambahkan</p>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Gambar</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_portfolio as $p): ?>
            <tr>
                <td>
                    <img src="<?= htmlspecialchars($p['gambar']) ?>" alt="" class="table-img">
                </td>
                <td><strong><?= htmlspecialchars($p['judul']) ?></strong></td>
                <td><span class="badge"><?= htmlspecialchars($p['kategori']) ?></span></td>
                <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="portfolio-form.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="portfolio-delete.php?id=<?= $p['id'] ?>" 
                           onclick="return confirmDelete()" 
                           class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>