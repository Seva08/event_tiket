<?php
$user_id = $_SESSION['id_user'];

// Query riwayat pesanan user
$query = mysqli_query($conn, "SELECT o.*, od.qty, t.nama_tiket, e.nama_event, e.tanggal, v.kode_voucher, v.potongan 
    FROM orders o 
    JOIN order_detail od ON o.id_order = od.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    LEFT JOIN voucher v ON o.id_voucher = v.id_voucher
    WHERE o.id_user = $user_id
    ORDER BY o.tanggal_order DESC");

// Query tiket yang dimiliki user
$attendees = mysqli_query($conn, "SELECT a.*, t.nama_tiket, e.nama_event, e.tanggal 
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    WHERE o.id_user = $user_id
    ORDER BY e.tanggal DESC");

// Statistik
$total_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE id_user = $user_id"))['total'];
$berhasil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE id_user = $user_id AND status = 'paid'"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE id_user = $user_id AND status = 'pending'"))['total'];
$sudah_checkin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN orders o ON od.id_order = o.id_order WHERE o.id_user = $user_id AND a.status_checkin = 'sudah'"))['total'];
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=home">Home</a></li>
            <li class="breadcrumb-item"><a href="?p=dashboard_user">Dashboard</a></li>
            <li class="breadcrumb-item active">Riwayat</li>
        </ol>
    </nav>

    <h3 class="mb-4"><i class="bi bi-clock-history"></i> Riwayat Pembelian</h3>

    <!-- Statistik -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card primary h-100">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="bi bi-cart fs-4 text-primary"></i>
                    </div>
                    <h4 class="fw-bold mb-0"><?= $total_order ?></h4>
                    <small class="text-muted">Total Order</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card success h-100">
                <div class="card-body text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="bi bi-check-circle fs-4 text-success"></i>
                    </div>
                    <h4 class="fw-bold mb-0"><?= $berhasil ?></h4>
                    <small class="text-muted">Berhasil</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card warning h-100">
                <div class="card-body text-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="bi bi-clock fs-4 text-warning"></i>
                    </div>
                    <h4 class="fw-bold mb-0"><?= $pending ?></h4>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card info h-100">
                <div class="card-body text-center">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="bi bi-qr-code-scan fs-4 text-info"></i>
                    </div>
                    <h4 class="fw-bold mb-0"><?= $sudah_checkin ?></h4>
                    <small class="text-muted">Check-in</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="card mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill" id="riwayatTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3" id="order-tab" data-bs-toggle="tab" data-bs-target="#order" type="button">
                        <i class="bi bi-cart me-2"></i> Daftar Pesanan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3" id="tiket-tab" data-bs-toggle="tab" data-bs-target="#tiket" type="button">
                        <i class="bi bi-ticket me-2"></i> Tiket Saya
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="riwayatTabContent">
        <!-- Tab Pesanan -->
        <div class="tab-pane fade show active" id="order" role="tabpanel">
            <?php if (mysqli_num_rows($query) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event & Tiket</th>
                                <th class="text-center">Qty</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php mysqli_data_seek($query, 0); while ($row = mysqli_fetch_assoc($query)):
                                $badge_class = $row['status'] == 'paid' ? 'success' : ($row['status'] == 'pending' ? 'warning' : 'danger');
                            ?>
                                <tr>
                                    <td class="fw-bold text-muted">#<?= $row['id_order'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                <i class="bi bi-calendar-event text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($row['nama_event'] ?? '-') ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($row['nama_tiket'] ?? '-') ?></small>
                                                <?php if ($row['kode_voucher']): ?>
                                                    <br><span class="badge bg-warning text-dark mt-1"><i class="bi bi-tag"></i> <?= $row['kode_voucher'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-info"><?= $row['qty'] ?></span></td>
                                    <td class="fw-bold">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                    <td><span class="badge bg-<?= $badge_class ?> px-3 py-2"><?= ucfirst($row['status']) ?></span></td>
                                    <td><small class="text-muted"><?= date('d M Y H:i', strtotime($row['tanggal_order'])) ?></small></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="bi bi-cart-x fs-1 text-muted"></i>
                    </div>
                    <h5 class="text-muted">Belum Ada Pesanan</h5>
                    <p class="text-muted">Mulai jelajahi event dan pesan tiket sekarang!</p>
                    <a href="?p=home" class="btn btn-primary"><i class="bi bi-calendar-event"></i> Lihat Event</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Tiket -->
        <div class="tab-pane fade" id="tiket" role="tabpanel">
            <div class="row">
                <?php if (mysqli_num_rows($attendees) > 0):
                    mysqli_data_seek($attendees, 0);
                    while ($tiket = mysqli_fetch_assoc($attendees)):
                        $status_class = $tiket['status_checkin'] == 'sudah' ? 'success' : 'warning';
                        $status_text = $tiket['status_checkin'] == 'sudah' ? 'Sudah Check-in' : 'Menunggu Check-in';
                        $status_icon = $tiket['status_checkin'] == 'sudah' ? 'check-circle' : 'qr-code-scan';
                ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 card-event position-relative overflow-hidden">
                                <div class="card-header text-white py-3" style="background: var(--<?= $tiket['status_checkin'] == 'sudah' ? 'success' : 'warning' ?>-gradient);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold"><i class="bi bi-ticket-perforated"></i> <?= htmlspecialchars($tiket['nama_tiket']) ?></span>
                                        <i class="bi bi-<?= $status_icon ?> fs-4"></i>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="text-muted mb-1">Event</h6>
                                    <h5 class="fw-bold mb-3"><?= htmlspecialchars($tiket['nama_event']) ?></h5>
                                    <p class="text-muted"><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($tiket['tanggal'])) ?></p>

                                    <div class="bg-light rounded-3 p-3 text-center mb-3">
                                        <small class="text-muted d-block mb-1">Kode Tiket Anda</small>
                                        <h4 class="ticket-code mb-0"><?= $tiket['kode_tiket'] ?></h4>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?= $status_class ?> px-3 py-2"><?= $status_text ?></span>
                                        <?php if ($tiket['status_checkin'] == 'sudah'): ?>
                                            <small class="text-muted"><i class="bi bi-clock"></i> <?= date('d M Y H:i', strtotime($tiket['waktu_checkin'])) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="col-12 text-center py-5">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                                <i class="bi bi-ticket-perforated fs-1 text-muted"></i>
                            </div>
                            <h5 class="text-muted">Belum Memiliki Tiket</h5>
                            <p class="text-muted">Pesan tiket untuk event favoritmu!</p>
                            <a href="?p=home" class="btn btn-primary"><i class="bi bi-calendar-event"></i> Jelajahi Event</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
