<?php
// Hapus event
if (isset($_GET['hapus'])) {
    $id_event = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM event WHERE id_event = $id_event");
    echo "<script>alert('Event berhasil dihapus!'); window.location='?p=admin_event';</script>";
}

$search      = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where       = $search ? "WHERE e.nama_event LIKE '%$search%' OR v.nama_venue LIKE '%$search%'" : '';
$limit       = 10;
$cur_page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset      = ($cur_page - 1) * $limit;
$total_data  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM event e JOIN venue v ON e.id_venue = v.id_venue $where"))['total'];
$total_pages = ceil($total_data / $limit);
$query       = mysqli_query($conn, "SELECT e.*, v.nama_venue FROM event e JOIN venue v ON e.id_venue = v.id_venue $where ORDER BY e.tanggal DESC LIMIT $limit OFFSET $offset");
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title"><i class="bi bi-calendar-event"></i> Data Event</h2>
                    <p class="text-muted mb-0">Kelola event yang tersedia</p>
                </div>
                <a href="?p=admin_event_tambah" class="btn btn-success btn-lg"><i class="bi bi-plus-circle"></i> Tambah Event</a>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="p" value="admin_event">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Cari event..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button></div>
                    </form>
                </div>
            </div>

            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-list"></i> Daftar Event</h5>
                    <span class="badge bg-primary fs-6">Total: <?= $total_data ?> event</span>
                </div>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="text-center" width="60">No</th>
                                    <th>Nama Event</th>
                                    <th>Tanggal</th>
                                    <th>Venue</th>
                                    <th class="text-center" width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($query)): ?>
                                    <tr>
                                        <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                    <i class="bi bi-calendar-event text-primary"></i>
                                                </div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($row['nama_event']) ?></h6>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-info"><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($row['tanggal'])) ?></span></td>
                                        <td><i class="bi bi-geo-alt text-danger"></i> <?= htmlspecialchars($row['nama_venue']) ?></td>
                                        <td class="text-center">
                                            <a href="?p=admin_event_edit&id=<?= $row['id_event'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                                            <a href="?p=admin_event&hapus=<?= $row['id_event'] ?>&page=<?= $cur_page ?>&search=<?= urlencode($search) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus event ini?')"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($total_pages > 1): ?>
                    <nav class="mt-4"><ul class="pagination justify-content-center">
                        <li class="page-item <?= $cur_page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?p=admin_event&page=<?= $cur_page-1 ?>&search=<?= urlencode($search) ?>"><i class="bi bi-chevron-left"></i></a></li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $cur_page ? 'active' : '' ?>"><a class="page-link" href="?p=admin_event&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= $cur_page >= $total_pages ? 'disabled' : '' ?>"><a class="page-link" href="?p=admin_event&page=<?= $cur_page+1 ?>&search=<?= urlencode($search) ?>"><i class="bi bi-chevron-right"></i></a></li>
                    </ul></nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-event fs-1 text-muted"></i>
                        <h5 class="text-muted mt-3"><?= $search ? 'Event tidak ditemukan' : 'Belum ada data event' ?></h5>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
