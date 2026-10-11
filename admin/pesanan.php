<?php
$page_title = 'Pesanan';
$page_subtitle = 'Pesanan masuk dari website';
require_once __DIR__ . '/includes/header.php';

$status_list = daftarStatusPesanan();

// ---- Filter & pencarian ----
$filter = $_GET['status'] ?? 'semua';
if ($filter !== 'semua' && !isset($status_list[$filter])) $filter = 'semua';
$q = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($filter !== 'semua') { $where[] = 'status = ?'; $params[] = $filter; }
if ($q !== '') {
    $where[] = '(kode_pesanan LIKE ? OR nama_pelanggan LIKE ? OR no_wa LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ---- Pagination ----
$per_page = 20;
$page = max(1, (int)($_GET['page'] ?? 1));

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan $where_sql");
$stmt->execute($params);
$total_rows = (int)$stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_rows / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare("SELECT * FROM pesanan $where_sql ORDER BY id DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$pesanan = $stmt->fetchAll();

// Hitungan per status untuk tab
$counts = array_fill_keys(array_keys($status_list), 0);
foreach ($pdo->query("SELECT status, COUNT(*) c FROM pesanan GROUP BY status")->fetchAll() as $r) {
    $counts[$r['status']] = (int)$r['c'];
}
$count_all = array_sum($counts);

function urlPesanan($status, $q, $page = 1) {
    return 'pesanan.php?' . http_build_query(array_filter(['status' => $status === 'semua' ? null : $status, 'q' => $q ?: null, 'page' => $page > 1 ? $page : null]));
}
?>

<div class="page-header">
    <div>
        <h2>Daftar Pesanan</h2>
        <p>Total: <?= $count_all ?> pesanan</p>
    </div>
</div>

<div class="filter-tabs">
    <a href="<?= urlPesanan('semua', $q) ?>" class="<?= $filter === 'semua' ? 'active' : '' ?>">Semua <small><?= $count_all ?></small></a>
    <?php foreach ($status_list as $kode => $info): ?>
    <a href="<?= urlPesanan($kode, $q) ?>" class="<?= $filter === $kode ? 'active' : '' ?>">
        <?= $info[0] ?> <small><?= $counts[$kode] ?></small>
    </a>
    <?php endforeach; ?>
</div>

<form method="GET" class="search-bar">
    <?php if ($filter !== 'semua'): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>"><?php endif; ?>
    <input type="text" name="q" class="form-control" placeholder="Cari kode, nama, atau nomor WA..." value="<?= htmlspecialchars($q) ?>">
    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
</form>

<div class="table-wrapper">
    <?php if (empty($pesanan)): ?>
    <div class="empty-state">
        <i class="fas fa-shopping-bag"></i>
        <h4>Tidak ada pesanan</h4>
        <p><?= ($q !== '' || $filter !== 'semua') ? 'Coba ubah filter atau kata pencarian' : 'Pesanan dari website akan muncul di sini' ?></p>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Pemesan</th>
                <th>Pesanan</th>
                <th>Total</th>
                <th>Status</th>
                <th style="width:110px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pesanan as $o): ?>
            <tr>
                <td><code class="kode-pesanan"><?= htmlspecialchars($o['kode_pesanan']) ?></code></td>
                <td style="font-size:13px;color:#6B7280"><?= date('d M Y', strtotime($o['created_at'])) ?><br><?= date('H:i', strtotime($o['created_at'])) ?></td>
                <td>
                    <strong><?= htmlspecialchars($o['nama_pelanggan']) ?></strong><br>
                    <small style="color:#6B7280"><?= htmlspecialchars($o['no_wa']) ?></small>
                </td>
                <td style="font-size:13px">
                    <?= htmlspecialchars($o['produk_nama']) ?> &times; <?= (int)$o['jumlah'] ?><br>
                    <small style="color:#6B7280"><?= htmlspecialchars($o['tingkat_nama']) ?> &middot; <?= $o['metode_pengambilan'] === 'kirim' ? 'Dikirim' : 'Ambil di toko' ?></small>
                </td>
                <td><strong style="color:#FF5722"><?= formatRupiah($o['total']) ?></strong></td>
                <td><span class="badge <?= $status_list[$o['status']][1] ?>"><?= $status_list[$o['status']][0] ?></span></td>
                <td>
                    <div class="action-buttons">
                        <a href="pesanan-detail.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm" title="Lihat detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="https://wa.me/<?= htmlspecialchars($o['no_wa']) ?>" target="_blank" class="btn btn-success btn-sm" title="Chat pelanggan">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php if ($total_pages > 1): ?>
<div class="filter-tabs" style="margin-top:20px;justify-content:center">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <a href="<?= urlPesanan($filter, $q, $i) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
