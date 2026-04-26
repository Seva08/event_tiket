<?php
// Hapus event
if (isset($_GET['hapus'])) {
    $id_event = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM event WHERE id_event = $id_event");
    $_SESSION['alert'] = [
        'type' => 'success',
        'title' => 'Dihapus',
        'text' => 'Event berhasil dihapus!'
    ];
    header("Location: ?p=admin_event");
    exit;
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
            <!-- Header Section -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-calendar-event text-primary me-2"></i>Master Event</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item small"><a href="?p=dashboard_admin" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item small active" aria-current="page">Master Data</li>
                            <li class="breadcrumb-item small active" aria-current="page">Event</li>
                        </ol>
                    </nav>
                </div>
                <a href="?p=admin_event_tambah" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                    <i class="bi bi-plus-lg me-2"></i>Tambah Event Baru
                </a>
            </div>

            <!-- Main Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom-0 py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-4">
                            <h5 class="mb-0 fw-bold">Daftar Event</h5>
                            <small class="text-muted">Total <?= number_format($total_data, 0, ',', '.') ?> data ditemukan</small>
                        </div>
                        <div class="col-md-8">
                            <form method="GET" class="d-flex gap-2 justify-content-md-end">
                                <input type="hidden" name="p" value="admin_event">
                                <div class="input-group" style="max-width: 300px;">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control bg-light border-start-0 ps-0 shadow-none" placeholder="Cari event..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <button type="submit" class="btn btn-dark rounded-pill px-4">Filter</button>
                                <?php if($search): ?>
                                    <a href="?p=admin_event" class="btn btn-light rounded-pill px-3 border"><i class="bi bi-x-lg"></i></a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 small text-uppercase fw-bold text-muted" width="80">No</th>
                                        <th class="py-3 small text-uppercase fw-bold text-muted">Info Event</th>
                                        <th class="py-3 small text-uppercase fw-bold text-muted">Jadwal</th>
                                        <th class="py-3 small text-uppercase fw-bold text-muted">Lokasi / Venue</th>
                                        <th class="py-3 text-center pe-4 small text-uppercase fw-bold text-muted" width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($query)): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-muted"><?= $no++ ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if($row['gambar']): ?>
                                                        <img src="uploads/<?= $row['gambar'] ?>" alt="" class="rounded-3 shadow-sm me-3 object-fit-cover border" width="50" height="50">
                                                    <?php else: ?>
                                                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 icon-box-sm me-3">
                                                            <i class="bi bi-calendar-event"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($row['nama_event']) ?></h6>
                                                        <small class="text-muted">ID: #EVT-<?= str_pad($row['id_event'], 4, '0', STR_PAD_LEFT) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><i class="bi bi-calendar3 text-primary me-2"></i><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
                                                <small class="text-muted"><?= date('H:i', strtotime($row['tanggal'])) ?> WIB</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger bg-opacity-10 text-danger-emphasis border border-danger border-opacity-10 px-2 py-1 rounded-pill small fw-normal">
                                                    <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($row['nama_venue']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center pe-4">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="?p=admin_event_edit&id=<?= $row['id_event'] ?>" 
                                                       class="btn btn-light btn-sm rounded-circle shadow-sm border icon-box-sm text-warning" 
                                                       title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <a href="?p=admin_event&hapus=<?= $row['id_event'] ?>&page=<?= $cur_page ?>&search=<?= urlencode($search) ?>" 
                                                       class="btn btn-light btn-sm rounded-circle shadow-sm border icon-box-sm text-danger btn-hapus" 
                                                       title="Hapus">
                                                        <i class="bi bi-trash3"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Section -->
                        <?php if ($total_pages > 1): ?>
                        <div class="card-footer bg-white border-top-0 py-4">
                            <nav>
                                <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                                    <li class="page-item <?= $cur_page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-circle border-0 shadow-sm mx-1" href="?p=admin_event&page=<?= $cur_page-1 ?>&search=<?= urlencode($search) ?>"><i class="bi bi-chevron-left"></i></a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $i == $cur_page ? 'active' : '' ?>">
                                            <a class="page-link rounded-circle border-0 shadow-sm mx-1" href="?p=admin_event&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $cur_page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-circle border-0 shadow-sm mx-1" href="?p=admin_event&page=<?= $cur_page+1 ?>&search=<?= urlencode($search) ?>"><i class="bi bi-chevron-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle icon-box d-inline-flex mb-3" style="width: 100px; height: 100px;">
                                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                            </div>
                            <h5 class="fw-bold text-dark"><?= $search ? 'Hasil Tidak Ditemukan' : 'Belum Ada Data' ?></h5>
                            <p class="text-muted small">Coba cari dengan kata kunci lain atau tambahkan data baru.</p>
                            <?php if($search): ?>
                                <a href="?p=admin_event" class="btn btn-outline-primary btn-sm rounded-pill px-4">Tampilkan Semua Data</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
        </main>
    </div>
</div>
