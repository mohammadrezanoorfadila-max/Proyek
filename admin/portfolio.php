<?php
$page_title = 'Portfolio';
$page_subtitle = 'Kelola foto & hasil karya sablon';
require_once __DIR__ . '/includes/header.php';

$portfolio = $pdo->query("SELECT * FROM portfolio ORDER BY urutan, id DESC")->fetchAll();
?>

<div class="page-header">
    <div>
        <h2>Daftar Portfolio</h2>
        <p>Total: <?= count($portfolio) ?> portfolio</p>
    </div>
    <a href="portfolio-form.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Portfolio
    </a>
</div>

<div class="table-wrapper">
    <?php if (empty($portfolio)): ?>
    <div class="empty-state">
        <i class="fas fa-images"></i>
        <h4>Belum ada portfolio</h4>
        <p>Klik tombol "Tambah Portfolio" untuk menambahkan</p>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th style="width:80px">Gambar</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Urutan</th>
                <th style="width:120px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($portfolio as $p): ?>
            <tr>
                <td><img src="<?= htmlspecialchars($p['gambar']) ?>" class="table-img"></td>
                <td><strong><?= htmlspecialchars($p['judul']) ?></strong></td>
                <td><span class="badge"><?= htmlspecialchars($p['kategori']) ?></span></td>
                <td style="max-width:300px;font-size:13px;color:#6B7280">
                    <?= htmlspecialchars(substr($p['deskripsi'], 0, 80)) ?>...
                </td>
                <td><?= $p['urutan'] ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="portfolio-form.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="portfolio-delete.php?id=<?= $p['id'] ?>" 
                           onclick="return confirmDelete('Hapus portfolio ini?')" 
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