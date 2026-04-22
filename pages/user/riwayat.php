<?php
// Validasi login & role
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    echo "<script>alert('Akses ditolak!'); window.location='?p=home';</script>";
    exit;
}

$user_id = $_SESSION['id_user'];

// Search Logic
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$where_order = "o.id_user = $user_id";
$where_tiket = "o.id_user = $user_id";

if ($search) {
    $where_order .= " AND (e.nama_event LIKE '%$search%' OR o.id_order LIKE '%$search%' OR t.nama_tiket LIKE '%$search%')";
    $where_tiket .= " AND (e.nama_event LIKE '%$search%' OR a.kode_tiket LIKE '%$search%' OR t.nama_tiket LIKE '%$search%')";
}

// Pagination Pesanan
$limit_o = 5;
$page_o = isset($_GET['po']) ? (int)$_GET['po'] : 1;
$offset_o = ($page_o - 1) * $limit_o;

$q_total_o = mysqli_query($conn, "SELECT COUNT(*) as c FROM orders o 
    JOIN order_detail od ON o.id_order = od.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket 
    JOIN event e ON t.id_event = e.id_event 
    WHERE $where_order");
$total_data_o = mysqli_fetch_assoc($q_total_o)['c'];
$total_page_o = ceil($total_data_o / $limit_o);

$query = mysqli_query($conn,
    "SELECT o.*, od.qty, t.nama_tiket, e.nama_event, e.tanggal, v.kode_voucher, v.potongan
     FROM orders o
     JOIN order_detail od ON o.id_order = od.id_order
     JOIN tiket t ON od.id_tiket = t.id_tiket
     JOIN event e ON t.id_event = e.id_event
     LEFT JOIN voucher v ON o.id_voucher = v.id_voucher
     WHERE $where_order ORDER BY o.tanggal_order DESC LIMIT $limit_o OFFSET $offset_o");

// Pagination Tiket
$limit_t = 6;
$page_t = isset($_GET['pt']) ? (int)$_GET['pt'] : 1;
$offset_t = ($page_t - 1) * $limit_t;

$q_total_t = mysqli_query($conn, "SELECT COUNT(*) as c FROM order_detail od 
    JOIN orders o ON od.id_order = o.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket 
    JOIN event e ON t.id_event = e.id_event 
    LEFT JOIN attendee a ON od.id_detail = a.id_detail
    WHERE $where_tiket");
$total_data_t = mysqli_fetch_assoc($q_total_t)['c'];
$total_page_t = ceil($total_data_t / $limit_t);

$attendees = mysqli_query($conn,
    "SELECT od.*, t.nama_tiket, e.nama_event, e.tanggal, o.status, o.id_order, a.kode_tiket, a.status_checkin, a.waktu_checkin
     FROM order_detail od
     JOIN orders o ON od.id_order = o.id_order
     JOIN tiket t ON od.id_tiket = t.id_tiket
     JOIN event e ON t.id_event = e.id_event
     LEFT JOIN attendee a ON od.id_detail = a.id_detail
     WHERE $where_tiket ORDER BY e.tanggal DESC LIMIT $limit_t OFFSET $offset_t");

// Overall Stats (Without search filter)
$total_order   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user=$user_id"))['c'];
$berhasil      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user=$user_id AND status='paid'"))['c'];
$pending       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user=$user_id AND status='pending'"))['c'];
$sudah_checkin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendee a JOIN order_detail od ON a.id_detail=od.id_detail JOIN orders o ON od.id_order=o.id_order WHERE o.id_user=$user_id AND a.status_checkin='sudah'"))['c'];
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=home">Home</a></li>
            <li class="breadcrumb-item"><a href="?p=dashboard_user">Dashboard</a></li>
            <li class="breadcrumb-item active">Riwayat</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="page-title mb-1"><i class="bi bi-clock-history me-2" style="color:var(--c-primary)"></i>Riwayat Pembelian</h2>
            <p class="text-muted mb-0" style="font-size:.87rem;">Semua transaksi dan tiket yang kamu miliki</p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100" style="max-width: 400px; justify-content: flex-md-end;">
            <form method="GET" action="index.php" class="position-relative flex-grow-1">
                <input type="hidden" name="p" value="riwayat">
                <input type="text" name="q" class="form-control border-0 shadow-sm" placeholder="Cari event, tiket..." value="<?= htmlspecialchars($search) ?>" style="border-radius:50px; padding-left:2.4rem; padding-right: <?= $search ? '2.5rem' : '1rem' ?>;">
                <i class="bi bi-search position-absolute text-muted" style="left:1rem; top:50%; transform:translateY(-50%); font-size:0.9rem;"></i>
                <?php if($search): ?>
                    <a href="?p=riwayat" class="btn btn-sm position-absolute" style="right:4px; top:50%; transform:translateY(-50%); border-radius:50%; padding:0.2rem 0.4rem;">
                        <i class="bi bi-x-circle-fill text-muted" style="font-size:1rem; opacity:0.7;"></i>
                    </a>
                <?php endif; ?>
            </form>
            <a href="?p=home" class="btn btn-primary shadow-sm d-flex align-items-center" style="border-radius:50px; white-space:nowrap; padding: 0.4rem 1.2rem;">
                <i class="bi bi-plus-circle me-2"></i>Pesan Tiket
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <?php $stats = [
            ['primary','bi-cart-check',    $total_order,   'Total Pesanan'],
            ['success','bi-check-circle-fill',$berhasil,   'Berhasil Bayar'],
            ['warning','bi-clock-fill',    $pending,       'Menunggu'],
            ['info',   'bi-qr-code-scan',  $sudah_checkin, 'Sudah Check-in'],
        ]; foreach ($stats as $s): ?>
        <div class="col-6 col-md-3">
            <div class="stat-card <?= $s[0] ?> h-100" style="padding:1.2rem;">
                <i class="bi <?= $s[1] ?> mb-2 d-block" style="font-size:1.6rem;opacity:.85;"></i>
                <h3 class="mb-0" style="font-size:1.9rem;"><?= $s[2] ?></h3>
                <p class="mb-0" style="font-size:.76rem;opacity:.85;"><?= $s[3] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabs -->
    <div class="card mb-0">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill gap-1" id="riwayatTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-2" id="order-tab" data-bs-toggle="tab" data-bs-target="#order" type="button"
                            style="border-radius:var(--r-md);font-weight:600;font-size:.875rem;" onclick="window.location.hash='#order'">
                        <i class="bi bi-cart me-2"></i>Daftar Pesanan
                        <span class="badge ms-1" style="background:rgba(255,255,255,.3);border-radius:50px;"><?= $total_data_o ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-2" id="tiket-tab" data-bs-toggle="tab" data-bs-target="#tiket" type="button"
                            style="border-radius:var(--r-md);font-weight:600;font-size:.875rem;" onclick="window.location.hash='#tiket'">
                        <i class="bi bi-ticket-perforated me-2"></i>Tiket Saya
                        <span class="badge bg-primary ms-1" style="border-radius:50px;"><?= $total_data_t ?></span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content mt-3" id="riwayatTabContent">
        <!-- ── Tab Pesanan ── -->
        <div class="tab-pane fade show active" id="order" role="tabpanel">
            <?php if (mysqli_num_rows($query) > 0): ?>
            <div class="row g-3">
                <?php while ($row = mysqli_fetch_assoc($query)):
                    $bc = $row['status']=='paid' ? 'success' : ($row['status']=='pending' ? 'warning' : 'danger');
                    $icon = $row['status']=='paid' ? 'check-circle-fill' : ($row['status']=='pending' ? 'clock-fill' : 'x-circle-fill');
                    $bg_color = $row['status']=='paid' ? 'var(--g-success)' : ($row['status']=='pending' ? 'var(--c-warning)' : 'var(--c-danger)');
                ?>
                <div class="col-12">
                    <div class="card border-0 position-relative" style="border-radius: var(--r-md); overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
                        <!-- Status bar left -->
                        <div style="position:absolute; top:0; left:0; bottom:0; width:8px; background: <?= $bg_color ?>;"></div>
                        
                        <div class="card-body p-4" style="padding-left: 1.5rem !important;">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                                <div>
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <span class="badge bg-<?= $bc ?>" style="border-radius:50px; font-weight:600; font-size:0.75rem; padding:0.4rem 0.8rem; color: <?= $bc == 'warning' ? '#000' : '#fff' ?>;">
                                            <i class="bi bi-<?= $icon ?> me-1"></i> <?= ucfirst($row['status']) ?>
                                        </span>
                                        <span class="text-muted" style="font-size:0.85rem; font-weight:600;">
                                            Order #<?= $row['id_order'] ?> &bull; <?= date('d M Y, H:i', strtotime($row['tanggal_order'])) ?>
                                        </span>
                                    </div>
                                    <h5 class="fw-bold mb-1" style="color:var(--txt);"><?= htmlspecialchars($row['nama_event'] ?? '-') ?></h5>
                                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                                        <i class="bi bi-ticket-perforated me-2"></i><?= htmlspecialchars($row['nama_tiket'] ?? '-') ?> <span class="badge bg-secondary ms-1"><?= $row['qty'] ?>x</span>
                                    </p>
                                    <?php if ($row['kode_voucher']): ?>
                                    <div class="mt-2">
                                        <span class="badge bg-warning text-dark" style="border-radius:50px;">
                                            <i class="bi bi-tag me-1"></i>Voucher dipakai: <?= $row['kode_voucher'] ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="text-md-end text-start" style="min-width:180px;">
                                    <div class="text-muted mb-1" style="font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Total Belanja</div>
                                    <h4 class="fw-bold mb-3" style="color:var(--c-primary);">Rp <?= number_format($row['total'], 0, ',', '.') ?></h4>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <a href="?p=order_bayar&id=<?= $row['id_order'] ?>" class="btn btn-primary w-100" style="border-radius:50px; font-weight:600;">
                                            <i class="bi bi-wallet2 me-2"></i>Bayar Sekarang
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination Pesanan -->
            <?php if($total_page_o > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page_o <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?p=riwayat&q=<?= urlencode($search) ?>&po=<?= $page_o - 1 ?>&pt=<?= $page_t ?>#order" style="border-radius:50px 0 0 50px;">Prev</a>
                    </li>
                    <?php for($i=1; $i<=$total_page_o; $i++): ?>
                        <li class="page-item <?= ($i == $page_o) ? 'active' : '' ?>">
                            <a class="page-link" href="?p=riwayat&q=<?= urlencode($search) ?>&po=<?= $i ?>&pt=<?= $page_t ?>#order"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page_o >= $total_page_o) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?p=riwayat&q=<?= urlencode($search) ?>&po=<?= $page_o + 1 ?>&pt=<?= $page_t ?>#order" style="border-radius:0 50px 50px 0;">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-5">
                <div style="width:80px;height:80px;background:var(--g-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-cart-x text-white fs-3"></i>
                </div>
                <h5 class="fw-bold mb-1"><?= $search ? 'Pesanan Tidak Ditemukan' : 'Belum Ada Pesanan' ?></h5>
                <p class="text-muted mb-3" style="font-size:.87rem;"><?= $search ? 'Coba gunakan kata kunci lain.' : 'Mulai jelajahi event dan pesan tiket sekarang!' ?></p>
                <?php if(!$search): ?>
                <a href="?p=home" class="btn btn-primary" style="border-radius:50px;">
                    <i class="bi bi-calendar-event me-2"></i>Lihat Event
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Tab Tiket ── -->
        <div class="tab-pane fade" id="tiket" role="tabpanel">
            <?php if (mysqli_num_rows($attendees) > 0): ?>
            <div class="row g-4">
                <?php while ($tkt = mysqli_fetch_assoc($attendees)):
                    $done  = ($tkt['status_checkin'] ?? '') == 'sudah';
                    $is_paid = $tkt['status'] == 'paid';
                    $hdr_g = !$is_paid ? 'var(--c-warning)' : ($done ? 'var(--g-success)' : 'var(--g-primary)');
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 ticket-card position-relative" style="border:none; border-radius:var(--r-lg); box-shadow:0 10px 30px rgba(0,0,0,0.08); overflow:hidden;">
                        <!-- Ticket header -->
                        <div style="background:<?= $hdr_g ?>;padding:1.5rem;color:#fff;position:relative;">
                            <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:rgba(255,255,255,.15);border-radius:50%;"></div>
                            <div style="position:absolute;bottom:-20px;left:-20px;width:80px;height:80px;background:rgba(255,255,255,.1);border-radius:50%;"></div>
                            <div class="d-flex justify-content-between align-items-start position-relative" style="z-index:2;">
                                <div>
                                    <div style="font-size:.7rem;opacity:.9;text-transform:uppercase;letter-spacing:1px;margin-bottom:.3rem; font-weight:700;">Tiket Akses</div>
                                    <h5 class="fw-bold mb-0 text-white" style="text-shadow:0 2px 4px rgba(0,0,0,0.2);"><?= htmlspecialchars($tkt['nama_tiket']) ?></h5>
                                </div>
                                <div style="background:rgba(255,255,255,0.2); padding:0.5rem; border-radius:12px; backdrop-filter:blur(5px);">
                                    <?php if($is_paid): ?>
                                        <i class="bi bi-<?= $done ? 'check-circle-fill' : 'qr-code-scan' ?>" style="font-size:1.8rem; line-height:1;"></i>
                                    <?php else: ?>
                                        <i class="bi bi-lock-fill" style="font-size:1.8rem; line-height:1;"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Divider / Cutout -->
                        <div style="position:relative; height:20px; background:#fff; overflow:hidden;">
                            <div style="position:absolute; top:10px; left:15px; right:15px; border-top:2px dashed #cbd5e1;"></div>
                            <div style="position:absolute; top:-10px; left:-10px; width:20px; height:20px; background:var(--bg); border-radius:50%; box-shadow:inset -2px 0 5px rgba(0,0,0,0.05);"></div>
                            <div style="position:absolute; top:-10px; right:-10px; width:20px; height:20px; background:var(--bg); border-radius:50%; box-shadow:inset 2px 0 5px rgba(0,0,0,0.05);"></div>
                        </div>

                        <!-- Ticket body -->
                        <div class="card-body p-4 bg-white" style="position:relative;">
                            <h6 class="fw-bold mb-2 lh-base" style="color:var(--txt);"><?= htmlspecialchars($tkt['nama_event']) ?></h6>
                            <div class="text-muted mb-4" style="font-size:.85rem; font-weight:500;">
                                <i class="bi bi-calendar3 me-2" style="color:var(--c-primary);"></i><?= date('d F Y', strtotime($tkt['tanggal'])) ?>
                            </div>
                            
                            <!-- QR Code area / Barcode visual -->
                            <div class="p-3 mb-4 text-center" style="background:#f8fafc;border-radius:var(--r-md); border:1px dashed #cbd5e1;">
                                <?php if ($tkt['status'] === 'paid'): ?>
                                    <div style="font-size:.68rem;color:var(--txt-muted);text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-bottom:.3rem;">KODE TIKET</div>
                                    <div class="ticket-code fw-bold fs-4 mb-3" style="color:var(--c-primary); letter-spacing:2px; font-family:monospace;"><?= $tkt['kode_tiket'] ?></div>
                                    
                                    <a href="?p=tiket_print&kode=<?= $tkt['kode_tiket'] ?>" target="_blank" class="btn btn-primary w-100" style="border-radius:50px; font-weight:600; box-shadow:0 4px 10px rgba(99,102,241,0.3);">
                                        <i class="bi bi-printer me-2"></i>Buka & Cetak PDF
                                    </a>
                                <?php else: ?>
                                    <div style="font-size:.68rem;color:var(--c-warning);text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-bottom:.3rem;">TIKET BELUM AKTIF</div>
                                    <div class="mb-3 px-2">
                                        <p class="text-muted mb-0" style="font-size: 0.8rem; line-height: 1.4;">Bayar terlebih dahulu untuk mendapatkan kode tiket & barcode</p>
                                    </div>
                                    
                                    <a href="?p=order_bayar&id=<?= $tkt['id_order'] ?>" class="btn btn-warning w-100" style="border-radius:50px; font-weight:600; color: #000;">
                                        <i class="bi bi-wallet2 me-2"></i>Bayar Sekarang
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Status -->
                            <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded-pill px-3">
                                <span class="badge <?= $done ? 'text-success' : 'text-warning' ?> p-0" style="font-size:0.8rem; background:transparent;">
                                    <i class="bi bi-<?= $done ? 'check-circle-fill' : 'clock-fill' ?> me-1"></i>
                                    <?= $done ? 'Sudah Check-in' : 'Menunggu Check-in' ?>
                                </span>
                                <?php if ($done && $tkt['waktu_checkin']): ?>
                                    <small class="text-muted fw-bold" style="font-size:.7rem;">
                                        <?= date('d M H:i', strtotime($tkt['waktu_checkin'])) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination Tiket -->
            <?php if($total_page_t > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page_t <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?p=riwayat&q=<?= urlencode($search) ?>&pt=<?= $page_t - 1 ?>&po=<?= $page_o ?>#tiket" style="border-radius:50px 0 0 50px;">Prev</a>
                    </li>
                    <?php for($i=1; $i<=$total_page_t; $i++): ?>
                        <li class="page-item <?= ($i == $page_t) ? 'active' : '' ?>">
                            <a class="page-link" href="?p=riwayat&q=<?= urlencode($search) ?>&pt=<?= $i ?>&po=<?= $page_o ?>#tiket"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page_t >= $total_page_t) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?p=riwayat&q=<?= urlencode($search) ?>&pt=<?= $page_t + 1 ?>&po=<?= $page_o ?>#tiket" style="border-radius:0 50px 50px 0;">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-5">
                <div style="width:80px;height:80px;background:var(--g-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-ticket-perforated text-white fs-3"></i>
                </div>
                <h5 class="fw-bold mb-1"><?= $search ? 'Tiket Tidak Ditemukan' : 'Belum Memiliki Tiket' ?></h5>
                <p class="text-muted mb-3" style="font-size:.87rem;"><?= $search ? 'Coba gunakan kata kunci lain.' : 'Pesan tiket untuk event favoritmu!' ?></p>
                <?php if(!$search): ?>
                <a href="?p=home" class="btn btn-primary" style="border-radius:50px;">
                    <i class="bi bi-calendar-event me-2"></i>Jelajahi Event
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Preserve tab on reload or navigation
    let hash = window.location.hash;
    if (hash) {
        let triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]');
        if (triggerEl) {
            let tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }
});
</script>
