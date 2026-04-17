<?php
$user_id = $_SESSION['id_user'];

// Statistik user
$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE id_user = $user_id"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE id_user = $user_id AND status = 'pending'"))['total'];
$sudah_bayar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE id_user = $user_id AND status = 'paid'"))['total'];
$total_tiket = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN orders o ON od.id_order = o.id_order WHERE o.id_user = $user_id"))['total'];

// Data event terbaru
$events = mysqli_query($conn, "SELECT e.*, v.nama_venue FROM event e JOIN venue v ON e.id_venue = v.id_venue ORDER BY e.tanggal DESC LIMIT 6");
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-person-circle fs-1 text-primary"></i>
                    </div>
                    <h5 class="mb-1"><?= htmlspecialchars($_SESSION['nama']) ?></h5>
                    <p class="text-muted mb-3"><?= htmlspecialchars($_SESSION['email'] ?? 'User') ?></p>
                    <a href="?p=logout" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="list-group list-group-flush">
                    <a href="?p=dashboard_user" class="list-group-item list-group-item-action active">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="?p=riwayat" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock-history"></i> Riwayat Pesanan
                    </a>
                    <a href="?p=home" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-event"></i> Lihat Event
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <h3 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard Saya</h3>

            <!-- Statistik Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card primary h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-receipt fs-2 mb-2"></i>
                            <h3 class="fw-bold"><?= $total_pesanan ?></h3>
                            <p class="mb-0">Total Pesanan</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card warning h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-clock fs-2 mb-2"></i>
                            <h3 class="fw-bold"><?= $pending ?></h3>
                            <p class="mb-0">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card success h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-check-circle fs-2 mb-2"></i>
                            <h3 class="fw-bold"><?= $sudah_bayar ?></h3>
                            <p class="mb-0">Sudah Bayar</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card info h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-ticket fs-2 mb-2"></i>
                            <h3 class="fw-bold"><?= $total_tiket ?></h3>
                            <p class="mb-0">Tiket Saya</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Terbaru -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Event Terbaru</h5>
                    <a href="?p=home" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php while ($event = mysqli_fetch_assoc($events)): 
                            $tanggal = date('d M Y', strtotime($event['tanggal']));
                        ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <span class="badge bg-primary mb-2"><i class="bi bi-calendar3"></i> <?= $tanggal ?></span>
                                    <h5 class="card-title"><?= htmlspecialchars($event['nama_event']) ?></h5>
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['nama_venue']) ?>
                                    </p>
                                    <a href="?p=event_detail&id=<?= $event['id_event'] ?>" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="bi bi-ticket"></i> Lihat Tiket
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
