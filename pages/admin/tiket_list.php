<?php
if (isset($_GET['hapus'])) {
    mysqli_query($conn, "DELETE FROM tiket WHERE id_tiket = " . (int)$_GET['hapus']);
    $_SESSION['alert'] = [
        'type' => 'success',
        'title' => 'Dihapus',
        'text' => 'Tiket berhasil dihapus!'
    ];
    header("Location: ?p=admin_tiket");
    exit;
}
$search      = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where       = $search ? "WHERE t.nama_tiket LIKE '%$search%' OR e.nama_event LIKE '%$search%'" : '';
$limit       = 10;
$cur_page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset      = ($cur_page - 1) * $limit;
$total_data  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tiket t JOIN event e ON t.id_event = e.id_event $where"))['total'];
$total_pages = ceil($total_data / $limit);
$query       = mysqli_query($conn, "SELECT t.*, e.nama_event FROM tiket t JOIN event e ON t.id_event = e.id_event $where ORDER BY e.tanggal DESC LIMIT $limit OFFSET $offset");
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h2 class="fw-bold"><i class="bi bi-ticket"></i> Data Tiket</h2><p class="text-muted mb-0">Kelola tiket untuk setiap event</p></div>
            <a href="?p=admin_tiket_tambah" class="btn btn-success btn-lg"><i class="bi bi-plus-circle"></i> Tambah Tiket</a>
        </div>
        <div class="card mb-4"><div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="p" value="admin_tiket">
                <div class="col-md-10"><div class="input-group"><span class="input-group-text bg-primary text-white"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Cari tiket..." value="<?= htmlspecialchars($search) ?>"></div></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Cari</button></div>
            </form>
        </div></div>
        <div class="card shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-list"></i> Daftar Tiket</h5>
                <span class="badge bg-primary fs-6">Total: <?= $total_data ?> tiket</span>
            </div>
            <?php if (mysqli_num_rows($query) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th width="60" class="text-center">No</th><th>Event</th><th>Nama Tiket</th><th class="text-end">Harga</th><th class="text-center">Kuota</th><th class="text-center" width="120">Aksi</th></tr></thead>
                        <tbody>
                            <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                <td><div class="d-flex align-items-center"><div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3"><i class="bi bi-calendar-event text-primary"></i></div><h6 class="mb-0 fw-bold"><?= htmlspecialchars($row['nama_event']) ?></h6></div></td>
                                <td><i class="bi bi-ticket text-success me-2"></i><?= htmlspecialchars($row['nama_tiket']) ?></td>
                                <td class="text-end fw-bold">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td class="text-center"><span class="badge bg-info"><?= $row['kuota'] ?> tiket</span></td>
                                <td class="text-center">
                                    <a href="?p=admin_tiket_edit&id=<?= $row['id_tiket'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                                    <a href="?p=admin_tiket&hapus=<?= $row['id_tiket'] ?>&page=<?= $cur_page ?>&search=<?= urlencode($search) ?>" class="btn btn-danger btn-sm btn-hapus"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4"><ul class="pagination justify-content-center">
                    <li class="page-item <?= $cur_page<=1?'disabled':'' ?>"><a class="page-link" href="?p=admin_tiket&page=<?= $cur_page-1 ?>&search=<?= urlencode($search) ?>"><i class="bi bi-chevron-left"></i></a></li>
                    <?php for ($i=1;$i<=$total_pages;$i++): ?><li class="page-item <?= $i==$cur_page?'active':'' ?>"><a class="page-link" href="?p=admin_tiket&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li><?php endfor; ?>
                    <li class="page-item <?= $cur_page>=$total_pages?'disabled':'' ?>"><a class="page-link" href="?p=admin_tiket&page=<?= $cur_page+1 ?>&search=<?= urlencode($search) ?>"><i class="bi bi-chevron-right"></i></a></li>
                </ul></nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5"><i class="bi bi-ticket fs-1 text-muted"></i><h5 class="text-muted mt-3"><?= $search ? 'Tiket tidak ditemukan' : 'Belum ada data tiket' ?></h5></div>
            <?php endif; ?>
        </div>
    </main>
</div></div>
