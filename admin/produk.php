<?php
$page_title = 'Produk Kaos';
$page_subtitle = 'Kelola jenis kaos yang tersedia';
require_once __DIR__ . '/includes/header.php';

$produk = $pdo->query("SELECT * FROM produk ORDER BY urutan, id")->fetchAll();
?>

<div class="page-header">
    <div>
        <h2>Daftar Produk Kaos</h2>
        <p>Total: <?= count($produk) ?> produk</p>
    </div>
    <a href="produk-form.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Produk
    </a>
</div>

<div class="table-wrapper">
    <?php if (empty($produk)): ?>
    <div class="empty-state">
        <i class="fas fa-tshirt"></i>
        <h4>Belum ada produk</h4>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th style="width:60px">Ikon</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Harga Dasar</th>
                <th>Status</th>
                <th style="width:120px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produk as $p): ?>
            <tr>
                <td style="font-size:28px"><?= $p['ikon'] ?></td>
                <td><code style="background:#F3F4F6;padding:3px 8px;border-radius:4px;font-size:12px"><?= htmlspecialchars($p['kode']) ?></code></td>
                <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                <td style="max-width:250px;font-size:13px;color:#6B7280">
                    <?= htmlspecialchars(substr($p['deskripsi'], 0, 60)) ?>...
                </td>
                <td><strong style="color:#FF5722"><?= formatRupiah($p['harga_dasar']) ?></strong></td>
                <td>
                    <?php if ($p['aktif']): ?>
                    <span class="badge success">Aktif</span>
                    <?php else: ?>
                    <span class="badge gray">Nonaktif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="produk-form.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="produk-delete.php?id=<?= $p['id'] ?>" 
                           onclick="return confirmDelete('Hapus produk ini?')" 
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