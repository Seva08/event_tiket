<?php
// Proses update status order
if (isset($_POST['update_status'])) {
    $id_order = (int)$_POST['id_order'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id_order=$id_order");
    echo "<script>alert('Status order berhasil diupdate!'); window.location='?p=dashboard_petugas';</script>";
}

// Proses check-in manual
if (isset($_POST['checkin_manual'])) {
    $kode_tiket = mysqli_real_escape_string($conn, $_POST['kode_tiket']);
    $attendee = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM attendee WHERE kode_tiket='$kode_tiket' AND status_checkin='belum'"));

    if ($attendee) {
        mysqli_query($conn, "UPDATE attendee SET status_checkin='sudah', waktu_checkin=NOW() WHERE kode_tiket='$kode_tiket'");
        echo "<script>alert('Check-in berhasil!'); window.location='?p=dashboard_petugas';</script>";
    } else {
        $error_checkin = "Kode tiket tidak ditemukan atau sudah check-in!";
    }
}

// Search & Pagination untuk orders
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = $search ? "WHERE o.id_order LIKE '%$search%' OR u.nama LIKE '%$search%' OR u.email LIKE '%$search%'" : '';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total orders
$total_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.id_user = u.id_user $where"))['total'];
$total_pages = ceil($total_data / $limit);

// Query orders dengan detail
$orders = mysqli_query($conn, "SELECT o.*, u.nama, u.email,
    (SELECT SUM(od.qty) FROM order_detail od WHERE od.id_order = o.id_order) as total_qty,
    v.kode_voucher, v.potongan
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    LEFT JOIN voucher v ON o.id_voucher = v.id_voucher
    $where
    ORDER BY o.tanggal_order DESC
    LIMIT $limit OFFSET $offset");

// Statistik
$total_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='pending'"))['total'];
$paid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='paid'"))['total'];
$cancelled = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='cancelled'"))['total'];
$total_checkin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendee WHERE status_checkin='sudah'"))['total'];
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Petugas -->
        <nav class="col-md-2 d-none d-md-block bg-dark sidebar py-4">
            <div class="sidebar-sticky">
                <h5 class="text-white px-3 mb-3"><i class="bi bi-person-badge"></i> Menu Petugas</h5>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white active" href="?p=dashboard_petugas"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="?p=petugas_checkin"><i class="bi bi-qr-code-scan"></i> Scan Check-in</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="?p=admin_laporan"><i class="bi bi-file-earmark-text"></i> Laporan</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title"><i class="bi bi-person-badge"></i> Dashboard Petugas</h2>
                    <p class="text-muted mb-0">Kelola transaksi dan check-in pengunjung</p>
                </div>
                <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-calendar3"></i> <?= date('d M Y') ?></span>
            </div>

            <!-- Welcome Banner -->
            <div class="alert alert-info d-flex align-items-center mb-4" style="background: var(--info-gradient); color: white; border: none; border-radius: 16px;">
                <i class="bi bi-person-circle fs-1 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Selamat Datang, Petugas <?= htmlspecialchars($_SESSION['nama']) ?>! 👋</h5>
                    <p class="mb-0">Anda dapat mengelola status order dan melakukan check-in pengunjung.</p>
                </div>
            </div>

            <!-- Statistik Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-2">
                    <div class="card stat-card primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-receipt me-2"></i>Total Order</p>
                                    <h2 class="mb-0 fw-bold"><?= $total_order ?></h2>
                                </div>
                                <i class="bi bi-receipt fs-3 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stat-card warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-clock me-2"></i>Pending</p>
                                    <h2 class="mb-0 fw-bold"><?= $pending ?></h2>
                                </div>
                                <i class="bi bi-clock fs-3 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stat-card success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-check-circle me-2"></i>Paid</p>
                                    <h2 class="mb-0 fw-bold"><?= $paid ?></h2>
                                </div>
                                <i class="bi bi-check-circle fs-3 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card stat-card danger h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-x-circle me-2"></i>Cancelled</p>
                                    <h2 class="mb-0 fw-bold"><?= $cancelled ?></h2>
                                </div>
                                <i class="bi bi-x-circle fs-3 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-qr-code-scan me-2"></i>Total Check-in</p>
                                    <h2 class="mb-0 fw-bold"><?= $total_checkin ?></h2>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-circle p-2">
                                    <i class="bi bi-person-check fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Check-in -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="mb-0"><i class="bi bi-qr-code-scan"></i> Check-in Manual</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error_checkin)): ?>
                                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $error_checkin ?></div>
                            <?php endif; ?>
                            <form method="POST" class="row g-3">
                                <div class="col-md-8">
                                    <input type="text" name="kode_tiket" class="form-control form-control-lg" placeholder="Masukkan kode tiket..." required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" name="checkin_manual" class="btn btn-success btn-lg w-100">
                                        <i class="bi bi-check-lg"></i> Check-in
                                    </button>
                                </div>
                            </form>
                            <div class="mt-3 text-center">
                                <a href="?p=petugas_checkin" class="btn btn-outline-primary">
                                    <i class="bi bi-camera"></i> Buka Scanner QR
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 rounded-circle p-3 me-4">
                                    <i class="bi bi-info-circle fs-1"></i>
                                </div>
                                <div>
                                    <h5 class="mb-2">Panduan Petugas</h5>
                                    <p class="mb-0 opacity-75">
                                        • Update status order (pending → paid)<br>
                                        • Lakukan check-in manual atau scan QR<br>
                                        • Pantau statistik harian
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Order -->
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Daftar Order</h5>
                    <span class="badge bg-primary fs-6">Total: <?= $total_data ?> order</span>
                </div>

                <!-- Search -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Cari order by ID, nama, atau email..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (mysqli_num_rows($orders) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th>Customer</th>
                                    <th class="text-center">Qty</th>
                                    <th>Total</th>
                                    <th>Voucher</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($orders)):
                                    $status_badge = $row['status'] == 'paid' ? 'success' : ($row['status'] == 'pending' ? 'warning' : 'danger');
                                ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted">#<?= $row['id_order'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($row['nama']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-info"><?= $row['total_qty'] ?? 0 ?> tiket</span></td>
                                    <td class="fw-bold">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if ($row['kode_voucher']): ?>
                                            <span class="badge bg-warning text-dark"><?= $row['kode_voucher'] ?> (-Rp <?= number_format($row['potongan']) ?>)</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" class="d-flex gap-2 justify-content-center">
                                            <input type="hidden" name="id_order" value="<?= $row['id_order'] ?>">
                                            <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                                <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="paid" <?= $row['status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                                <option value="cancelled" <?= $row['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-primary" title="Update">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <a href="?p=admin_order_detail&id=<?= $row['id_order'] ?>" class="btn btn-info btn-sm" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= $search ?>"><i class="bi bi-chevron-left"></i></a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= $search ?>"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                            <i class="bi bi-receipt fs-1 text-muted"></i>
                        </div>
                        <h5 class="text-muted">Tidak ada order ditemukan</h5>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
