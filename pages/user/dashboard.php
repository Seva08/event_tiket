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
            <div class="card border-0 shadow-sm" style="border-radius:var(--r-lg);">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-fire me-2" style="color:var(--c-warning)"></i>Event Tersedia</h5>
                    <a href="?p=home" class="btn btn-sm btn-outline-primary" style="border-radius:50px;">Lihat Semua</a>
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
                            <div class="card card-event h-100 <?= $is_passed ? 'opacity-75' : '' ?>">
                                <?php if($d['gambar']): ?>
                                    <!-- Image Banner -->
                                    <div style="position:relative; width:100%; padding-top:56.25%; overflow:hidden; border-radius: var(--r-xl) var(--r-xl) 0 0;">
                                        <img src="uploads/<?= $d['gambar'] ?>" alt="<?= htmlspecialchars($d['nama_event']) ?>" style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; <?= $is_passed ? 'filter: grayscale(100%);' : '' ?>">
                                        <!-- Floating Date Badge -->
                                        <div style="position:absolute; top:12px; right:12px; background:rgba(255,255,255,0.95); backdrop-filter:blur(4px); padding:0.4rem 0.6rem; border-radius:10px; text-align:center; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
                                            <div style="color:var(--c-primary); font-weight:800; font-size:1.1rem; line-height:1;"><?= $tgl_short ?></div>
                                            <div style="color:var(--txt-muted); font-size:0.6rem; font-weight:700; text-transform:uppercase; margin-top:2px;"><?= $bln_short ?></div>
                                        </div>
                                        <?php if($is_passed): ?>
                                        <div style="position:absolute; top:12px; left:12px; background:var(--c-danger); color:white; padding:0.25rem 0.6rem; border-radius:50px; font-size:0.7rem; font-weight:bold; box-shadow:0 4px 15px rgba(0,0,0,0.2);">
                                            Selesai
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Body -->
                                    <div class="card-body px-4 py-4">
                                        <h6 class="fw-bold mb-2 lh-sm" style="color:var(--txt); font-size:1.05rem; text-overflow:ellipsis; white-space:nowrap; overflow:hidden;"><?= htmlspecialchars($d['nama_event']) ?></h6>
                                        <div class="d-flex align-items-start gap-2 mb-1">
                                            <i class="bi bi-geo-alt-fill mt-1" style="color:var(--c-danger);font-size:.8rem;"></i>
                                            <div>
                                                <div class="fw-bold" style="font-size:.8rem;color:var(--txt);"><?= htmlspecialchars($d['nama_venue']) ?></div>
                                                <div class="text-muted text-truncate" style="font-size:.75rem; max-width: 150px;"><?= htmlspecialchars($d['alamat']) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- No Image Fallback -->
                                    <div class="card-header position-relative <?= $is_passed ? 'bg-secondary' : '' ?>" style="padding:1.2rem 1.2rem 0.8rem; <?= !$is_passed ? 'background: var(--g-primary);' : '' ?> border-radius: var(--r-lg) var(--r-lg) 0 0;">
                                        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index:1; color:#fff;">
                                            <div style="max-width: 70%;">
                                                <span class="badge mb-2 text-white" style="background:rgba(255,255,255,.2);backdrop-filter:blur(4px);border-radius:50px;font-size:.65rem;">
                                                    <i class="bi bi-calendar3 me-1"></i><?= $tanggal ?>
                                                </span>
                                                <?php if($is_passed): ?>
                                                <span class="badge mb-2 text-white ms-1 bg-danger" style="border-radius:50px;font-size:.65rem;">Selesai</span>
                                                <?php endif; ?>
                                                <h6 class="mb-0 fw-bold lh-sm text-white text-truncate" style="font-size:.9rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                                    <?= htmlspecialchars($d['nama_event']) ?>
                                                </h6>
                                            </div>
                                            <div class="text-center ms-2 flex-shrink-0 text-white" style="background:rgba(255,255,255,.2);backdrop-filter:blur(4px);border-radius:10px;padding:.3rem .5rem;min-width:40px;">
                                                <div style="font-size:1.1rem;font-weight:800;line-height:1;"><?= $tgl_short ?></div>
                                                <div style="font-size:.55rem;opacity:.9;text-transform:uppercase;"><?= date('M', strtotime($d['tanggal'])) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start gap-2 mb-1">
                                            <i class="bi bi-geo-alt-fill mt-1" style="color:var(--c-danger);font-size:.8rem;"></i>
                                            <div>
                                                <div class="fw-bold" style="font-size:.8rem;color:var(--txt);"><?= htmlspecialchars($d['nama_venue']) ?></div>
                                                <div class="text-muted text-truncate" style="font-size:.75rem; max-width: 150px;"><?= htmlspecialchars($d['alamat']) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <!-- Footer -->
                                <div class="card-footer bg-white border-0 px-3 pb-3 pt-0">
                                    <?php if($is_passed): ?>
                                        <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-secondary btn-sm w-100" style="border-radius:50px; font-size:0.8rem;">
                                            <i class="bi bi-calendar-x me-2"></i>Lihat Detail
                                        </a>
                                    <?php else: ?>
                                        <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-primary btn-sm w-100" style="border-radius:50px; font-size:0.8rem;">
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
