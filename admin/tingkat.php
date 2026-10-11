<?php
$page_title = 'Tingkat Sablon';
$page_subtitle = 'Kelola tingkat kerumitan sablon';
require_once __DIR__ . '/includes/header.php';

$tingkat = $pdo->query("SELECT * FROM tingkat ORDER BY id")->fetchAll();
?>

<div class="page-header">
    <div>
        <h2>Daftar Tingkat Sablon</h2>
        <p>Total: <?= count($tingkat) ?> tingkat</p>
    </div>
    <a href="tingkat-form.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Tingkat
    </a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th style="width:60px">Ikon</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Tambahan Harga</th>
                <th>Status</th>
                <th style="width:120px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tingkat as $t): ?>
            <tr>
                <td style="font-size:28px"><?= $t['ikon'] ?></td>
                <td><code style="background:#F3F4F6;padding:3px 8px;border-radius:4px;font-size:12px"><?= htmlspecialchars($t['kode']) ?></code></td>
                <td><strong><?= htmlspecialchars($t['nama']) ?></strong></td>
                <td style="max-width:250px;font-size:13px;color:#6B7280">
                    <?= htmlspecialchars(substr($t['deskripsi'], 0, 60)) ?>...
                </td>
                <td><strong style="color:#FF5722">+ <?= formatRupiah($t['tambahan_harga']) ?></strong></td>
                <td>
                    <?php if ($t['aktif']): ?>
                    <span class="badge success">Aktif</span>
                    <?php else: ?>
                    <span class="badge gray">Nonaktif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="tingkat-form.php?id=<?= $t['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="tingkat-delete.php?id=<?= $t['id'] ?>" 
                           onclick="return confirmDelete('Hapus tingkat ini?')" 
                           class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>