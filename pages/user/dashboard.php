<?php
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    echo "<script>alert('Akses ditolak!'); window.location='?p=login';</script>";
    exit;
}

$user_id       = $_SESSION['id_user'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $user_id"))['c'];
$pending       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $user_id AND status = 'pending'"))['c'];
$sudah_bayar   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $user_id AND status = 'paid'"))['c'];
$total_tiket   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN orders o ON od.id_order = o.id_order WHERE o.id_user = $user_id AND o.status = 'paid'"))['c'];
$events        = mysqli_query($conn, "SELECT e.*, v.nama_venue, v.alamat FROM event e JOIN venue v ON e.id_venue = v.id_venue ORDER BY e.tanggal DESC LIMIT 6");
?>

<div class="container py-4">
    <div class="row g-4">

        <!-- ── User Sidebar ─────────────────────── -->
        <div class="col-md-3">
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3 mx-auto d-flex align-items-center justify-content-center bg-primary text-white rounded-circle p-3 fs-3">
                        <i class="bi bi-person-fill fs-2"></i>
                    </div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($_SESSION['nama']) ?></h6>
                    <p class="text-muted mb-3 small">
                        <?= htmlspecialchars($_SESSION['email'] ?? 'Member') ?>
                    </p>
                    <span class="badge bg-primary rounded-pill px-3 py-2 mb-3 small">
                        <i class="bi bi-person-check me-1"></i>Member Aktif
                    </span>
                </div>
            </div>

            <!-- Nav menu -->
            <div class="card border-0 shadow-sm">
                <div class="list-group list-group-flush rounded-3">
                    <a href="?p=dashboard_user"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 fw-semibold py-3 active">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="?p=riwayat"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3">
                        <i class="bi bi-clock-history"></i> Riwayat Pesanan
                    </a>
                    <a href="?p=home"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3">
                        <i class="bi bi-calendar-event"></i> Lihat Event
                    </a>
                    <a href="?p=logout"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 text-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Main Content ─────────────────────── -->
        <div class="col-md-9">
            <!-- Greeting banner -->
            <div class="card mb-4 border-0 bg-primary text-white shadow-sm">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div>
                        <h5 class="text-white fw-bold mb-1">
                            Hai, <?= htmlspecialchars(explode(' ', $_SESSION['nama'])[0]) ?>! 👋
                        </h5>
                        <p class="text-white mb-0 opacity-75 small">
                            Selamat datang kembali. Yuk cek tiket dan event favoritmu.
                        </p>
                    </div>
                    <i class="bi bi-emoji-smile-fill text-white ms-auto d-none d-md-block display-4 opacity-25"></i>
                </div>
            </div>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <?php $stats = [
                    ['primary','bi-receipt',         $total_pesanan, 'Total Pesanan'],
                    ['warning','bi-clock',            $pending,       'Pending'],
                    ['success','bi-check-circle-fill',$sudah_bayar,   'Sudah Bayar'],
                    ['info',   'bi-ticket-perforated',$total_tiket,   'Tiket Saya'],
                ]; foreach ($stats as $s): ?>
                <div class="col-6 col-md-3">
                    <div class="card border-0 bg-<?= $s[0] ?> text-white shadow-sm h-100 p-3">
                        <i class="bi <?= $s[1] ?> mb-2 d-block fs-4"></i>
                        <h3 class="mb-0 display-6 fw-bold"><?= $s[2] ?></h3>
                        <p class="mb-0 small opacity-75"><?= $s[3] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Event terbaru -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-fire me-2 text-warning"></i>Event Tersedia</h5>
                    <a href="?p=home" class="btn btn-sm btn-outline-primary rounded-pill">Lihat Semua</a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <?php while ($d = mysqli_fetch_assoc($events)):
                            $tanggal   = date('d M Y', strtotime($d['tanggal']));
                            $tgl_short = date('d', strtotime($d['tanggal']));
                            $bln_short = date('M Y', strtotime($d['tanggal']));
                            $is_passed = strtotime($d['tanggal']) < strtotime('today');
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border shadow-sm <?= $is_passed ? 'opacity-75' : '' ?>">
                                <?php if($d['gambar']): ?>
                                    <div class="position-relative ratio ratio-16x9 overflow-hidden">
                                        <img src="uploads/<?= $d['gambar'] ?>" alt="<?= htmlspecialchars($d['nama_event']) ?>" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover">
                                        <div class="position-absolute top-0 end-0 m-2 bg-white rounded-3 shadow-sm p-2 text-center">
                                            <div class="text-primary fw-bold fs-6 lh-1"><?= $tgl_short ?></div>
                                            <div class="text-muted small fw-bold text-uppercase"><?= $bln_short ?></div>
                                        </div>
                                        <?php if($is_passed): ?>
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-danger rounded-pill small fw-bold">Selesai</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body px-3 py-3">
                                        <h6 class="fw-bold mb-2 lh-sm text-truncate"><?= htmlspecialchars($d['nama_event']) ?></h6>
                                        <div class="d-flex align-items-start gap-2 mb-1">
                                            <i class="bi bi-geo-alt-fill mt-1 text-danger small"></i>
                                            <div>
                                                <div class="fw-bold small"><?= htmlspecialchars($d['nama_venue']) ?></div>
                                                <div class="text-muted small text-truncate col-8"><?= htmlspecialchars($d['alamat']) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="card-header position-relative <?= $is_passed ? 'bg-secondary' : 'bg-primary' ?> text-white p-3">
                                        <div class="d-flex justify-content-between align-items-start position-relative z-1">
                                            <div class="col-8">
                                                <span class="badge bg-white bg-opacity-25 mb-2 rounded-pill small">
                                                    <i class="bi bi-calendar3 me-1"></i><?= $tanggal ?>
                                                </span>
                                                <?php if($is_passed): ?>
                                                <span class="badge mb-2 ms-1 bg-danger rounded-pill small">Selesai</span>
                                                <?php endif; ?>
                                                <h6 class="mb-0 fw-bold lh-sm text-white text-truncate small"><?= htmlspecialchars($d['nama_event']) ?></h6>
                                            </div>
                                            <div class="text-center ms-2 flex-shrink-0 bg-white bg-opacity-25 rounded-3 p-2 px-3">
                                                <div class="fs-5 fw-bold lh-1"><?= $tgl_short ?></div>
                                                <div class="small opacity-75 text-uppercase"><?= date('M', strtotime($d['tanggal'])) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start gap-2 mb-1">
                                            <i class="bi bi-geo-alt-fill mt-1 text-danger small"></i>
                                            <div>
                                                <div class="fw-bold small"><?= htmlspecialchars($d['nama_venue']) ?></div>
                                                <div class="text-muted small text-truncate col-8"><?= htmlspecialchars($d['alamat']) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <!-- Footer -->
                                <div class="card-footer bg-white border-0 px-3 pb-3 pt-0">
                                    <?php if($is_passed): ?>
                                        <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-secondary btn-sm w-100 rounded-pill">
                                            <i class="bi bi-calendar-x me-2"></i>Lihat Detail
                                        </a>
                                    <?php else: ?>
                                        <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-primary btn-sm w-100 rounded-pill">
                                            <i class="bi bi-ticket-perforated me-2"></i>Lihat Tiket
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div><!-- /col-md-9 -->
    </div>
</div>
