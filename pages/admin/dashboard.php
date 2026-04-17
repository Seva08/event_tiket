<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title"><i class="bi bi-speedometer2"></i> Dashboard Admin</h2>
                    <p class="text-muted mb-0">Kelola sistem dan pantau performa bisnis Anda</p>
                </div>
                <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-calendar3"></i> <?= date('d M Y') ?></span>
            </div>

            <!-- Welcome Banner -->
            <div class="alert alert-primary d-flex align-items-center mb-4" style="background: var(--primary-gradient); color: white; border: none; border-radius: 16px;">
                <i class="bi bi-person-circle fs-1 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?>! 👋</h5>
                    <p class="mb-0">Kelola event, tiket, dan pantau transaksi dari dashboard ini.</p>
                </div>
            </div>

            <!-- Statistik Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-people me-2"></i>Total User</p>
                                    <h2 class="mb-0 fw-bold">
                                        <?php
                                        $total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'];
                                        echo $total_user;
                                        ?>
                                    </h2>
                                    <small class="opacity-75">Pengguna terdaftar</small>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-people fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-cart me-2"></i>Total Order</p>
                                    <h2 class="mb-0 fw-bold">
                                        <?php
                                        $total_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];
                                        echo $total_order;
                                        ?>
                                    </h2>
                                    <small class="opacity-75">Transaksi masuk</small>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-cart fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-cash-stack me-2"></i>Pendapatan</p>
                                    <h4 class="mb-0 fw-bold">
                                        Rp <?= number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as total FROM orders WHERE status='paid'"))['total'] ?? 0, 0, ',', '.') ?>
                                    </h4>
                                    <small class="opacity-75">Total terkumpul</small>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-cash-stack fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card danger h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-calendar-check me-2"></i>Event Aktif</p>
                                    <h2 class="mb-0 fw-bold">
                                        <?php
                                        $event_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM event WHERE tanggal >= CURDATE()"))['total'];
                                        echo $event_aktif;
                                        ?>
                                    </h2>
                                    <small class="opacity-75">Event berlangsung</small>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-calendar-check fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laporan Transaksi Terbaru -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Transaksi Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID Order</th>
                                    <th>User</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $res = mysqli_query($conn, "SELECT orders.*, users.nama FROM orders JOIN users ON orders.id_user = users.id_user ORDER BY tanggal_order DESC LIMIT 10");
                                while ($row = mysqli_fetch_assoc($res)):
                                    $badge_class = $row['status'] == 'paid' ? 'success' : ($row['status'] == 'pending' ? 'warning' : 'danger');
                                ?>
                                    <tr>
                                        <td>#<?= $row['id_order'] ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= date('d M Y H:i', strtotime($row['tanggal_order'])) ?></td>
                                        <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                        <td><span class="badge bg-<?= $badge_class ?>"><?= ucfirst($row['status']) ?></span></td>
                                        <td><a href="?p=admin_order_detail&id=<?= $row['id_order'] ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
