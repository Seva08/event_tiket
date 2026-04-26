<?php
// Validasi login & role
include 'config.php';
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    echo "<script>alert('Akses ditolak!'); window.location='?p=home';</script>";
    exit;
}

$user_id = $_SESSION['id_user'];

// Handle Cancel Order
if (isset($_POST['action']) && $_POST['action'] == 'cancel_order') {
    $id_order = (int)$_POST['id_order'];
    $check = mysqli_query($conn, "SELECT status, id_voucher FROM orders WHERE id_order = $id_order AND id_user = $user_id");
    $order = mysqli_fetch_assoc($check);
    
    if ($order && $order['status'] == 'pending') {
        if ($order['id_voucher']) {
            mysqli_query($conn, "UPDATE voucher SET kuota = kuota + 1 WHERE id_voucher = " . $order['id_voucher']);
        }
        $details = mysqli_query($conn, "SELECT id_tiket, qty FROM order_detail WHERE id_order = $id_order");
        while ($d = mysqli_fetch_assoc($details)) {
            mysqli_query($conn, "UPDATE tiket SET kuota = kuota + " . $d['qty'] . " WHERE id_tiket = " . $d['id_tiket']);
        }
        mysqli_query($conn, "UPDATE orders SET status = 'cancel' WHERE id_order = $id_order");
        echo json_encode(['status' => 'success', 'message' => 'Pesanan berhasil dibatalkan']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak dapat dibatalkan']);
    }
    exit;
}

// Search & Filter Logic
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$where_order = "o.id_user = $user_id";
$where_tiket = "o.id_user = $user_id";

if ($search) {
    $where_order .= " AND (e.nama_event LIKE '%$search%' OR o.id_order LIKE '%$search%' OR t.nama_tiket LIKE '%$search%')";
    $where_tiket .= " AND (e.nama_event LIKE '%$search%' OR a.kode_tiket LIKE '%$search%' OR t.nama_tiket LIKE '%$search%')";
}

if ($status_filter) {
    $where_order .= " AND o.status = '$status_filter'";
    $where_tiket .= " AND o.status = '$status_filter'";
}

// Sorting logic
$order_sort = "o.tanggal_order DESC";
$ticket_sort = "e.tanggal DESC";

if ($sort_by === 'oldest') {
    $order_sort = "o.tanggal_order ASC";
    $ticket_sort = "e.tanggal ASC";
} elseif ($sort_by === 'price_high') {
    $order_sort = "o.total DESC";
} elseif ($sort_by === 'price_low') {
    $order_sort = "o.total ASC";
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
    "SELECT o.*, od.qty, t.nama_tiket, t.harga as harga_tiket, e.nama_event, e.tanggal, e.gambar, v.kode_voucher, v.potongan, vn.nama_venue, vn.alamat
     FROM orders o
     JOIN order_detail od ON o.id_order = od.id_order
     JOIN tiket t ON od.id_tiket = t.id_tiket
     JOIN event e ON t.id_event = e.id_event
     JOIN venue vn ON e.id_venue = vn.id_venue
     LEFT JOIN voucher v ON o.id_voucher = v.id_voucher
     WHERE $where_order ORDER BY $order_sort LIMIT $limit_o OFFSET $offset_o");

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
    "SELECT od.*, t.nama_tiket, e.nama_event, e.tanggal, e.gambar, vn.nama_venue, vn.alamat, o.status, o.id_order, a.kode_tiket, a.status_checkin, a.waktu_checkin
     FROM order_detail od
     JOIN orders o ON od.id_order = o.id_order
     JOIN tiket t ON od.id_tiket = t.id_tiket
     JOIN event e ON t.id_event = e.id_event
     JOIN venue vn ON e.id_venue = vn.id_venue
     LEFT JOIN attendee a ON od.id_detail = a.id_detail
     WHERE $where_tiket ORDER BY $ticket_sort LIMIT $limit_t OFFSET $offset_t");

// Overall Stats (Without search filter)
$total_order   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user=$user_id"))['c'];
$berhasil      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user=$user_id AND status='paid'"))['c'];
$pending       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user=$user_id AND status='pending'"))['c'];
$sudah_checkin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendee a JOIN order_detail od ON a.id_detail=od.id_detail JOIN orders o ON od.id_order=o.id_order WHERE o.id_user=$user_id AND a.status_checkin='sudah'"))['c'];
?>

<!-- Bootstrap-only styling -->

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="?p=home" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="?p=dashboard_user" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active">Riwayat</li>
        </ol>
    </nav>

    <!-- Search & Filter & Sort -->
    <div class="row align-items-center mb-4 g-3">
        <div class="col-md-5">
            <h2 class="fw-bold mb-1"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pembelian</h2>
            <p class="text-muted mb-0">Kelola semua transaksi dan tiket event kamu di sini.</p>
        </div>
        <div class="col-md-7">
            <div class="d-flex flex-column flex-md-row gap-2">
                <form method="GET" action="index.php" class="position-relative flex-grow-1">
                    <input type="hidden" name="p" value="riwayat">
                    <?php if($status_filter): ?><input type="hidden" name="status" value="<?= $status_filter ?>"><?php endif; ?>
                    <?php if($sort_by): ?><input type="hidden" name="sort" value="<?= $sort_by ?>"><?php endif; ?>
                    <input type="text" name="q" class="form-control border-0 shadow-sm rounded-pill ps-5 py-2" 
                           placeholder="Cari event atau nomor order..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <i class="bi bi-search position-absolute text-muted ms-3 top-50 translate-middle-y"></i>
                    <?php if($search): ?>
                        <a href="?p=riwayat<?= ($status_filter ? '&status='.$status_filter : '').($sort_by ? '&sort='.$sort_by : '') ?>" class="position-absolute text-muted me-3 top-50 translate-middle-y end-0">
                            <i class="bi bi-x-circle-fill"></i>
                        </a>
                    <?php endif; ?>
                </form>
                <div class="dropdown">
                    <button class="btn bg-white shadow-sm dropdown-toggle rounded-pill w-100 py-2 px-3" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sort-down me-2"></i>Urutkan
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2 mt-2">
                        <li><a class="dropdown-item rounded-3 <?= $sort_by == 'newest' ? 'active' : '' ?>" href="?p=riwayat&q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&sort=newest">Terbaru</a></li>
                        <li><a class="dropdown-item rounded-3 <?= $sort_by == 'oldest' ? 'active' : '' ?>" href="?p=riwayat&q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&sort=oldest">Terlama</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item rounded-3 <?= $sort_by == 'price_high' ? 'active' : '' ?>" href="?p=riwayat&q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&sort=price_high">Harga Tertinggi</a></li>
                        <li><a class="dropdown-item rounded-3 <?= $sort_by == 'price_low' ? 'active' : '' ?>" href="?p=riwayat&q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&sort=price_low">Harga Terendah</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Filters -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="?p=riwayat&q=<?= urlencode($search) ?>&sort=<?= $sort_by ?>" class="btn btn-sm rounded-pill <?= $status_filter == '' ? 'btn-primary' : 'btn-outline-secondary' ?>">Semua</a>
        <a href="?p=riwayat&status=pending&q=<?= urlencode($search) ?>&sort=<?= $sort_by ?>" class="btn btn-sm rounded-pill <?= $status_filter == 'pending' ? 'btn-warning' : 'btn-outline-secondary' ?>">Pending</a>
        <a href="?p=riwayat&status=paid&q=<?= urlencode($search) ?>&sort=<?= $sort_by ?>" class="btn btn-sm rounded-pill <?= $status_filter == 'paid' ? 'btn-success' : 'btn-outline-secondary' ?>">Paid</a>
        <a href="?p=riwayat&status=cancel&q=<?= urlencode($search) ?>&sort=<?= $sort_by ?>" class="btn btn-sm rounded-pill <?= $status_filter == 'cancel' ? 'btn-danger' : 'btn-outline-secondary' ?>">Cancelled</a>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-5">
        <?php 
        $stat_items = [
            ['primary', 'bi-cart-check', $total_order, 'Total Pesanan', ''],
            ['success', 'bi-check-circle', $berhasil, 'Terbayar', 'paid'],
            ['warning', 'bi-hourglass-split', $pending, 'Pending', 'pending'],
            ['info', 'bi-qr-code', $sudah_checkin, 'Check-in', '']
        ];
        foreach($stat_items as $item): ?>
        <div class="col-6 col-lg-3">
            <div class="card border-0 bg-<?= $item[0] ?> text-white shadow-sm p-3 p-md-4" role="button" onclick="window.location.href='?p=riwayat&status=<?= $item[4] ?>'">
                <i class="bi <?= $item[1] ?> fs-4 mb-2"></i>
                <div class="h3 fw-bold mb-0"><?= $item[2] ?></div>
                <div class="small opacity-75 fw-medium"><?= $item[3] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="d-flex justify-content-center mb-4">
        <ul class="nav nav-pills bg-light rounded-pill p-1 gap-1" id="riwayatTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill active" id="order-tab" data-bs-toggle="tab" data-bs-target="#order" type="button" onclick="window.location.hash='#order'">
                    <i class="bi bi-receipt me-2"></i>Pesanan
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-2"><?= $total_data_o ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill" id="tiket-tab" data-bs-toggle="tab" data-bs-target="#tiket" type="button" onclick="window.location.hash='#tiket'">
                    <i class="bi bi-ticket-perforated me-2"></i>Tiket Saya
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-2"><?= $total_data_t ?></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="riwayatTabContent">
        <!-- ── Tab Pesanan ── -->
        <div class="tab-pane fade show active" id="order" role="tabpanel">
            <?php if (mysqli_num_rows($query) > 0): ?>
            <div class="d-flex flex-column gap-4">
                <?php 
                $delay = 0;
                while ($row = mysqli_fetch_assoc($query)):
                    $status_class = "badge-" . $row['status'];
                ?>
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                        <?php $delay += 0.1; ?>
                        <div class="row g-0">
                            <div class="col-md-3">
                                <div class="position-relative h-100 min-vh-25">
                                    <img src="<?= $row['gambar'] ? 'uploads/'.$row['gambar'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=500&q=80' ?>" 
                                         class="w-100 h-100 object-fit-cover" alt="Event"
                                         onerror="this.src='https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=500&q=80'">
                                    <div class="position-absolute top-0 start-0 m-3">
                                        <span class="badge bg-<?= $row['status'] === 'paid' ? 'success' : ($row['status'] === 'pending' ? 'warning' : 'danger') ?> rounded-pill shadow-sm px-3 py-2 text-uppercase">
                                            <?= $row['status'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9 p-4">
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 h-100">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <div class="text-muted small fw-bold">
                                                    ORDER <span class="bg-light px-2 rounded" role="button" onclick="copyToClipboard('#<?= $row['id_order'] ?>', 'ID Order')">#<?= $row['id_order'] ?></span> 
                                                    • <?= date('d M Y, H:i', strtotime($row['tanggal_order'])) ?>
                                                </div>
                                                <?php 
                                                $is_new = (time() - strtotime($row['tanggal_order'])) < 300; // 5 minutes
                                                if($is_new): ?>
                                                    <span class="badge bg-info text-white small">Baru</span>
                                                <?php endif; ?>
                                            </div>
                                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($row['nama_event']) ?></h5>
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <i class="bi bi-ticket-perforated text-primary"></i>
                                                <span class="text-muted small"><?= htmlspecialchars($row['nama_tiket']) ?></span>
                                                <span class="badge bg-light text-dark border"><?= $row['qty'] ?>x</span>
                                            </div>
                                            
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php if ($row['kode_voucher']): ?>
                                                <div class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                                    <i class="bi bi-tag-fill me-1"></i> Promo: <?= $row['kode_voucher'] ?>
                                                </div>
                                                <?php endif; ?>
                                                <div class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-1">
                                                    <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($row['nama_venue']) ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-md-end d-flex flex-column justify-content-between align-items-md-end min-vw-15">
                                            <div>
                                                <div class="text-muted small fw-medium mb-1">Total Pembayaran</div>
                                                <div class="h4 fw-bold text-primary mb-3">Rp <?= number_format($row['total'], 0, ',', '.') ?></div>
                                            </div>
                                            
                                            <div class="d-flex gap-2 w-100 justify-content-md-end">
                                                <button class="btn btn-outline-success rounded-pill px-3" 
                                                        onclick="shareOrderWhatsApp(<?= htmlspecialchars(json_encode([
                                                            'id' => $row['id_order'],
                                                            'event' => $row['nama_event'],
                                                            'total' => $row['total'],
                                                            'status' => $row['status']
                                                        ])) ?>)">
                                                    <i class="bi bi-whatsapp"></i>
                                                </button>
                                                <?php if ($row['status'] === 'pending'): ?>
                                                    <button class="btn btn-outline-danger rounded-pill px-3" onclick="cancelOrder(<?= $row['id_order'] ?>)">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                    <a href="?p=order_bayar&id=<?= $row['id_order'] ?>" class="btn btn-primary rounded-pill px-4 flex-grow-1 flex-md-grow-0">
                                                        <i class="bi bi-wallet2 me-2"></i>Bayar
                                                    </a>
                                                <?php endif; ?>
                                                <button class="btn btn-outline-secondary rounded-pill px-3 btn-order-detail" 
                                                        data-order='<?= htmlspecialchars(json_encode([
                                                            'id' => $row['id_order'],
                                                            'event' => $row['nama_event'],
                                                            'tiket' => $row['nama_tiket'],
                                                            'qty' => $row['qty'],
                                                            'harga' => $row['harga_tiket'],
                                                            'potongan' => $row['potongan'] ?? 0,
                                                            'total' => $row['total'],
                                                            'status' => $row['status'],
                                                            'tanggal' => date('d M Y, H:i', strtotime($row['tanggal_order'])),
                                                            'venue' => $row['nama_venue'],
                                                            'alamat' => $row['alamat']
                                                        ]), ENT_QUOTES, 'UTF-8') ?>'>
                                                    <i class="bi bi-info-circle"></i> Rincian
                                                </button>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php if($total_page_o > 1): ?>
            <div class="d-flex justify-content-center mt-5">
                <nav>
                    <ul class="pagination pagination-rounded gap-2">
                        <li class="page-item <?= ($page_o <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link border-0 shadow-sm" href="?p=riwayat&q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&po=<?= $page_o - 1 ?>&pt=<?= $page_t ?>#order"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        <?php for($i=1; $i<=$total_page_o; $i++): ?>
                            <li class="page-item <?= ($i == $page_o) ? 'active' : '' ?>">
                                <a class="page-link border-0 shadow-sm" href="?p=riwayat&q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&po=<?= $i ?>&pt=<?= $page_t ?>#order"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page_o >= $total_page_o) ? 'disabled' : '' ?>">
                            <a class="page-link border-0 shadow-sm" href="?p=riwayat&q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&po=<?= $page_o + 1 ?>&pt=<?= $page_t ?>#order"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-5">
                <img src="https://illustrations.popsy.co/gray/shopping-cart.svg" alt="Empty" class="mb-4 opacity-50 w-25">
                <h5 class="fw-bold">Tidak ada pesanan ditemukan</h5>
                <p class="text-muted">Coba sesuaikan filter atau kata kunci pencarian kamu.</p>
                <a href="?p=home" class="btn btn-primary rounded-pill px-4 mt-2">Mulai Cari Event</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Tab Tiket ── -->
        <div class="tab-pane fade" id="tiket" role="tabpanel">
            <?php if (mysqli_num_rows($attendees) > 0): ?>
            <div class="row g-4">
                <?php 
                $delay_t = 0;
                while ($tkt = mysqli_fetch_assoc($attendees)):
                    $is_done = ($tkt['status_checkin'] ?? '') == 'sudah';
                    $is_paid = $tkt['status'] == 'paid';
                    
                    // Countdown Logic
                    $event_date = strtotime($tkt['tanggal']);
                    $now = time();
                    $diff = $event_date - $now;
                    $days_left = ceil($diff / (60 * 60 * 24));
                ?>
                <div class="col-md-6 col-xl-4">
                    <?php $delay_t += 0.1; ?>
                    <div class="card bg-white shadow-sm h-100 overflow-hidden d-flex flex-column rounded-3">
                        <!-- Top Image Part -->
                        <div class="ratio ratio-21x9 position-relative">
                            <img src="<?= $tkt['gambar'] ? 'uploads/'.$tkt['gambar'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=500&q=80' ?>" 
                                 class="w-100 h-100 object-fit-cover" alt="Event"
                                 onerror="this.src='https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=500&q=80'">
                            
                            <div class="position-absolute top-0 start-0 m-3">
                                <?php if($days_left > 0): ?>
                                    <div class="badge bg-dark bg-opacity-50 text-white rounded-2 shadow-sm px-2 py-1 small fw-bold">
                                        <i class="bi bi-hourglass-split me-1"></i><?= $days_left ?> Hari Lagi
                                    </div>
                                <?php elseif($days_left == 0): ?>
                                    <div class="badge bg-danger text-white rounded-2 shadow-sm px-2 py-1 small fw-bold">
                                        <i class="bi bi-fire me-1"></i>Hari Ini!
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="position-absolute top-0 end-0 m-3">
                                <?php if($is_paid): ?>
                                    <span class="badge bg-white text-primary rounded-pill shadow-sm px-3 py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger text-white rounded-pill shadow-sm px-3 py-2">
                                        <i class="bi bi-lock-fill me-1"></i>Belum Bayar
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Ticket Body -->
                        <div class="p-4 pt-3 flex-grow-1 position-relative">
                            <hr class="my-0">

                            <div class="mb-4">
                                <div class="text-primary fw-bold small text-uppercase mb-1"><?= htmlspecialchars($tkt['nama_tiket']) ?></div>
                                <h5 class="fw-bold mb-3 text-truncate" title="<?= htmlspecialchars($tkt['nama_event']) ?>"><?= htmlspecialchars($tkt['nama_event']) ?></h5>
                                
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="bg-light rounded p-2 text-center px-3">
                                        <div class="fw-bold lh-1"><?= date('d', strtotime($tkt['tanggal'])) ?></div>
                                        <div class="small text-muted small"><?= date('M', strtotime($tkt['tanggal'])) ?></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small fw-bold text-dark"><i class="bi bi-geo-alt text-danger me-1"></i> <?= htmlspecialchars($tkt['nama_venue']) ?></div>
                                        <div class="small text-muted text-truncate col-8"><?= htmlspecialchars($tkt['alamat']) ?></div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-success rounded-pill px-3" 
                                            onclick="shareTicketWhatsApp('<?= $tkt['kode_tiket'] ?>', '<?= addslashes($tkt['nama_event']) ?>', '<?= date('d M Y', strtotime($tkt['tanggal'])) ?>')">
                                        <i class="bi bi-whatsapp"></i>
                                    </button>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($tkt['nama_venue'] . ' ' . $tkt['alamat']) ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-primary rounded-pill flex-grow-1">
                                        <i class="bi bi-map me-1"></i> Lokasi
                                    </a>
                                    <a href="https://www.google.com/calendar/render?action=TEMPLATE&text=<?= urlencode($tkt['nama_event']) ?>&dates=<?= date('Ymd\THis\Z', strtotime($tkt['tanggal'])) ?>/<?= date('Ymd\THis\Z', strtotime($tkt['tanggal'] . ' +3 hours')) ?>&details=Tiket+Anda:+<?= $tkt['kode_tiket'] ?>&location=<?= urlencode($tkt['nama_venue']) ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">
                                        <i class="bi bi-calendar-plus"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="bg-light rounded-4 p-3 text-center border border-dashed">
                                <?php if ($is_paid): ?>
                                    <div class="small text-muted text-uppercase fw-bold mb-2 small">Kode Tiket</div>
                                    <div class="h4 fw-bold font-monospace text-primary mb-3 rounded" role="button" onclick="copyToClipboard('<?= $tkt['kode_tiket'] ?>', 'Kode Tiket')"><?= $tkt['kode_tiket'] ?></div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-primary flex-grow-1 rounded-pill" onclick="showQuickBarcode('<?= $tkt['kode_tiket'] ?>', '<?= addslashes($tkt['nama_event']) ?>')">
                                            <i class="bi bi-qr-code me-2"></i>Quick Scan
                                        </button>
                                        <a href="?p=tiket_print&kode=<?= $tkt['kode_tiket'] ?>" target="_blank" class="btn btn-primary rounded-pill px-3 shadow-sm">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="py-3">
                                        <p class="small text-muted mb-3">Selesaikan pembayaran untuk mengaktifkan tiket ini.</p>
                                        <a href="?p=order_bayar&id=<?= $tkt['id_order'] ?>" class="btn btn-warning w-100 rounded-pill py-2">
                                            <i class="bi bi-wallet2 me-2"></i>Bayar Sekarang
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Ticket Footer -->
                        <div class="px-4 pb-4 mt-n2">
                            <div class="d-flex justify-content-between align-items-center bg-white border rounded-pill px-3 py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-<?= $is_done ? 'success' : 'warning' ?> p-1"></div>
                                    <span class="small fw-medium text-muted"><?= $is_done ? 'Sudah Digunakan' : 'Siap Digunakan' ?></span>
                                </div>
                                <?php if($is_done): ?>
                                    <div class="small text-muted small"><?= date('d M, H:i', strtotime($tkt['waktu_checkin'])) ?></div>
                                <?php else: ?>
                                    <i class="bi bi-qr-code-scan text-muted"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination Tiket -->
            <?php if($total_page_t > 1): ?>
            <div class="d-flex justify-content-center mt-5">
                <nav>
                    <ul class="pagination pagination-rounded gap-2">
                        <li class="page-item <?= ($page_t <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link border-0 shadow-sm" href="?p=riwayat&q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&pt=<?= $page_t - 1 ?>&po=<?= $page_o ?>#tiket"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        <?php for($i=1; $i<=$total_page_t; $i++): ?>
                            <li class="page-item <?= ($i == $page_t) ? 'active' : '' ?>">
                                <a class="page-link border-0 shadow-sm" href="?p=riwayat&q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&pt=<?= $i ?>&po=<?= $page_o ?>#tiket"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page_t >= $total_page_t) ? 'disabled' : '' ?>">
                            <a class="page-link border-0 shadow-sm" href="?p=riwayat&q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&pt=<?= $page_t + 1 ?>&po=<?= $page_o ?>#tiket"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-5">
                <img src="https://illustrations.popsy.co/gray/ticket.svg" alt="Empty" class="mb-4 opacity-50 w-25">
                <h5 class="fw-bold">Tidak ada tiket ditemukan</h5>
                <p class="text-muted">Coba sesuaikan filter atau kata kunci pencarian kamu.</p>
                <a href="?p=home" class="btn btn-primary rounded-pill px-4 mt-2">Cari Event Seru</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Load JsBarcode Library -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<script>
function copyToClipboard(text, label) {
    navigator.clipboard.writeText(text.replace('#', '')).then(() => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: `${label} berhasil disalin!`
        });
    });
}

function cancelOrder(id) {
    Swal.fire({
        title: 'Batalkan Pesanan?',
        text: "Pesanan yang dibatalkan tidak dapat dikembalikan dan kuota tiket akan dilepas.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Batalkan!',
        cancelButtonText: 'Tutup',
        reverseButtons: true,
        customClass: {
            confirmButton: 'rounded-pill px-4',
            cancelButton: 'rounded-pill px-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'cancel_order');
            formData.append('id_order', id);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Dibatalkan!',
                        text: data.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

function shareOrderWhatsApp(data) {
    const text = `Halo! Berikut detail pesanan saya di YuiPass:%0A%0A` +
                 `*Order ID:* #${data.id}%0A` +
                 `*Event:* ${data.event}%0A` +
                 `*Total:* Rp ${new Intl.NumberFormat('id-ID').format(data.total)}%0A` +
                 `*Status:* ${data.status.toUpperCase()}%0A%0A` +
                 `Cek detailnya di platform YuiPass ya!`;
    window.open(`https://wa.me/?text=${text}`, '_blank');
}

function shareTicketWhatsApp(kode, event, tanggal) {
    const text = `Halo! Ini kode tiket saya untuk event *${event}* (${tanggal}):%0A%0A` +
                 `*KODE TIKET:* ${kode}%0A%0A` +
                 `Sampai jumpa di lokasi!`;
    window.open(`https://wa.me/?text=${text}`, '_blank');
}

function showQuickBarcode(kode, event) {
    Swal.fire({
        title: 'Quick Scan Tiket',
        html: `
            <div class="text-center p-3">
                <div class="fw-bold mb-3">${event}</div>
                <div class="bg-white p-4 rounded-4 border border-dashed mb-3 d-inline-block">
                    <svg id="quick-barcode"></svg>
                </div>
                <div class="h4 fw-bold font-monospace text-primary tracking-widest">${kode}</div>
                <p class="text-muted small mt-3 mb-0">Tunjukkan barcode ini kepada petugas di pintu masuk.</p>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: () => {
            JsBarcode("#quick-barcode", kode, {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 80,
                displayValue: false,
                margin: 0
            });
        }
    });
}

function showOrderDetail(data) {
    const formatRp = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    
    let html = `
        <div class="text-start small">
            <div class="mb-4 p-3 rounded bg-light border">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">No. Pesanan</span>
                    <span class="fw-bold">#${data.id}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Waktu Transaksi</span>
                    <span>${data.tanggal}</span>
                </div>
            </div>
            
            <h6 class="fw-bold mb-2">Item Pesanan</h6>
            <div class="mb-4">
                <div class="fw-bold text-dark">${data.event}</div>
                <div class="d-flex justify-content-between text-muted">
                    <span>${data.tiket} (x${data.qty})</span>
                    <span>${formatRp(data.harga * data.qty)}</span>
                </div>
            </div>
            
            <h6 class="fw-bold mb-2">Rincian Pembayaran</h6>
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Harga Tiket (x${data.qty})</span>
                    <span>${formatRp(data.harga * data.qty)}</span>
                </div>
                ${data.potongan > 0 ? `
                <div class="d-flex justify-content-between mb-1 text-success">
                    <span>Diskon Voucher</span>
                    <span>- ${formatRp(data.potongan)}</span>
                </div>
                ` : ''}
                <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                    <span class="fw-bold">Total Bayar</span>
                    <span class="fw-bold text-primary fs-5">${formatRp(data.total)}</span>
                </div>
            </div>
            
            <h6 class="fw-bold mb-2">Informasi Venue</h6>
            <div class="mb-0">
                <div class="fw-bold text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${data.venue}</div>
                <div class="text-muted small">${data.alamat}</div>
            </div>
        </div>
    `;

    Swal.fire({
        title: 'Detail Transaksi',
        html: html,
        showCloseButton: true,
        showConfirmButton: data.status === 'pending',
        confirmButtonText: '<i class="bi bi-wallet2 me-2"></i>Bayar Sekarang',
        confirmButtonColor: '#6366f1',
        cancelButtonText: 'Tutup',
        showCancelButton: true,
        buttonsStyling: true,
        customClass: {
            confirmButton: 'rounded-pill px-4',
            cancelButton: 'rounded-pill px-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?p=order_bayar&id=' + data.id;
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    // Detail button listener
    document.querySelectorAll('.btn-order-detail').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.getAttribute('data-order'));
            showOrderDetail(data);
        });
    });

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
