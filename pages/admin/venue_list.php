<?php
// Hapus venue
if (isset($_GET['hapus'])) {
    $id_venue = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM venue WHERE id_venue = $id_venue");
    $_SESSION['alert'] = [
        'type' => 'success',
        'title' => 'Dihapus',
        'text' => 'Venue berhasil dihapus!'
    ];
    header("Location: ?p=admin_venue");
    exit;
}

$search      = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where       = $search ? "WHERE nama_venue LIKE '%$search%' OR alamat LIKE '%$search%'" : '';
$limit       = 10;
$cur_page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset      = ($cur_page - 1) * $limit;
$total_data  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM venue $where"))['total'];
$total_pages = ceil($total_data / $limit);
$query       = mysqli_query($conn, "SELECT * FROM venue $where ORDER BY nama_venue ASC LIMIT $limit OFFSET $offset");
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title"><i class="bi bi-geo-alt"></i> Data Venue</h2>
                    <p class="text-muted mb-0">Kelola venue penyelenggaraan event</p>
                </div>
                <a href="?p=admin_venue_tambah" class="btn btn-success btn-lg"><i class="bi bi-plus-circle"></i> Tambah Venue</a>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="p" value="admin_venue">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Cari venue..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button></div>
                    </form>
                </div>
            </div>

            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-list"></i> Daftar Venue</h5>
                    <span class="badge bg-primary fs-6">Total: <?= $total_data ?> venue</span>
                </div>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="text-center" width="60">No</th>
                                    <th>Nama Venue</th>
                                    <th>Alamat</th>
                                    <th class="text-center">Kapasitas</th>
                                    <th class="text-center" width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($query)): ?>
                                    <tr>
                                        <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3">
                                                    <i class="bi bi-geo-alt text-danger"></i>
                                                </div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($row['nama_venue']) ?></h6>
                                            </div>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td class="text-center"><span class="badge bg-info"><?= number_format($row['kapasitas']) ?> orang</span></td>
                                        <td class="text-center">
                                            <a href="?p=admin_venue_edit&id=<?= $row['id_venue'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                                            <a href="?p=admin_venue&hapus=<?= $row['id_venue'] ?>&page=<?= $cur_page ?>&search=<?= urlencode($search) ?>" class="btn btn-danger btn-sm btn-hapus"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($total_pages > 1): ?>
                    <nav class="mt-4"><ul class="pagination justify-content-center">
                        <li class="page-item <?= $cur_page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?p=admin_venue&page=<?= $cur_page-1 ?>&search=<?= urlencode($search) ?>"><i class="bi bi-chevron-left"></i></a></li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $cur_page ? 'active' : '' ?>"><a class="page-link" href="?p=admin_venue&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= $cur_page >= $total_pages ? 'disabled' : '' ?>"><a class="page-link" href="?p=admin_venue&page=<?= $cur_page+1 ?>&search=<?= urlencode($search) ?>"><i class="bi bi-chevron-right"></i></a></li>
                    </ul></nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-geo-alt fs-1 text-muted"></i>
                        <h5 class="text-muted mt-3"><?= $search ? 'Venue tidak ditemukan' : 'Belum ada data venue' ?></h5>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
