<?php
$user_id = $_SESSION['id_user'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $user_id"))['c'];
$pending       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $user_id AND status = 'pending'"))['c'];
$sudah_bayar   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $user_id AND status = 'paid'"))['c'];
$total_tiket   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN orders o ON od.id_order = o.id_order WHERE o.id_user = $user_id"))['c'];
$events        = mysqli_query($conn, "SELECT e.*, v.nama_venue FROM event e JOIN venue v ON e.id_venue = v.id_venue ORDER BY e.tanggal DESC LIMIT 6");
?>

<div class="container py-4">
    <div class="row g-4">

        <!-- ── User Sidebar ─────────────────────── -->
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-body text-center p-4">
                    <div class="mb-3 mx-auto d-flex align-items-center justify-content-center"
                         style="width:72px;height:72px;background:var(--g-primary);border-radius:50%;">
                        <i class="bi bi-person-fill text-white fs-2"></i>
                    </div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($_SESSION['nama']) ?></h6>
                    <p class="text-muted mb-3" style="font-size:.8rem;">
                        <?= htmlspecialchars($_SESSION['email'] ?? 'Member') ?>
                    </p>
                    <span class="badge px-3 py-2 mb-3"
                          style="background:var(--g-primary);border-radius:50px;font-size:.75rem;">
                        <i class="bi bi-person-check me-1"></i>Member Aktif
                    </span>
                </div>
            </div>

            <!-- Nav menu -->
            <div class="card">
                <div class="list-group list-group-flush" style="border-radius:var(--r-lg);">
                    <a href="?p=dashboard_user"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 fw-600 py-3"
                       style="font-weight:600;border:none;background:rgba(99,102,241,.08);color:var(--c-primary);border-radius:var(--r-lg) var(--r-lg) 0 0;">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="?p=riwayat"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3"
                       style="border:none;color:var(--txt-muted);">
                        <i class="bi bi-clock-history"></i> Riwayat Pesanan
                    </a>
                    <a href="?p=home"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3"
                       style="border:none;color:var(--txt-muted);">
                        <i class="bi bi-calendar-event"></i> Lihat Event
                    </a>
                    <a href="?p=logout"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 text-danger"
                       style="border:none;border-radius:0 0 var(--r-lg) var(--r-lg);">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Main Content ─────────────────────── -->
        <div class="col-md-9">
            <!-- Greeting banner -->
            <div class="card mb-4" style="background:var(--g-primary);border:none !important;">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div>
                        <h5 class="text-white fw-bold mb-1">
                            Hai, <?= htmlspecialchars(explode(' ', $_SESSION['nama'])[0]) ?>! 👋
                        </h5>
                        <p class="text-white mb-0" style="opacity:.82;font-size:.88rem;">
                            Selamat datang kembali. Yuk cek tiket dan event favoritmu.
                        </p>
                    </div>
                    <i class="bi bi-emoji-smile-fill text-white ms-auto d-none d-md-block"
                       style="font-size:3rem;opacity:.35;"></i>
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
                    <div class="stat-card <?= $s[0] ?> h-100" style="padding:1.2rem;">
                        <i class="bi <?= $s[1] ?> mb-2 d-block" style="font-size:1.6rem;opacity:.85;"></i>
                        <h3 class="mb-0" style="font-size:1.8rem;"><?= $s[2] ?></h3>
                        <p class="mb-0" style="font-size:.78rem;opacity:.85;"><?= $s[3] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Event terbaru -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-fire me-2" style="color:var(--c-warning)"></i>Event Tersedia</span>
                    <a href="?p=home" class="btn btn-sm btn-primary" style="border-radius:50px;">Lihat Semua</a>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <?php while ($ev = mysqli_fetch_assoc($events)):
                            $tgl = date('d M Y', strtotime($ev['tanggal']));
                        ?>
                        <div class="col-md-6">
                            <div class="card h-100" style="border-radius:var(--r-md)!important;">
                                <div class="card-body p-3">
                                    <span class="badge mb-2"
                                          style="background:var(--g-primary);border-radius:50px;font-size:.72rem;">
                                        <i class="bi bi-calendar3 me-1"></i><?= $tgl ?>
                                    </span>
                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($ev['nama_event']) ?></h6>
                                    <p class="text-muted mb-3" style="font-size:.82rem;">
                                        <i class="bi bi-geo-alt-fill" style="color:var(--c-danger)"></i>
                                        <?= htmlspecialchars($ev['nama_venue']) ?>
                                    </p>
                                    <a href="?p=event_detail&id=<?= $ev['id_event'] ?>"
                                       class="btn btn-outline-primary btn-sm w-100" style="border-radius:50px;">
                                        <i class="bi bi-ticket me-1"></i>Lihat Tiket
                                    </a>
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
