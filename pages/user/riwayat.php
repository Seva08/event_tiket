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

<div class="container py-4">
    <!-- Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Riwayat Saya</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item small"><a href="?p=home" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item small active" aria-current="page">Riwayat</li>
                </ol>
            </nav>
        </div>
        <div class="bg-white px-3 py-2 rounded-pill shadow-sm border small fw-bold text-primary">
            <i class="bi bi-person-check me-2"></i><?= htmlspecialchars($_SESSION['nama']) ?>
        </div>
    </div>

    <!-- Stats Premium Row -->
    <div class="row g-3 mb-5">
        <?php 
        $stat_items = [
            ['primary', 'bi-cart-check', $total_order, 'Total Pesanan', ''],
            ['success', 'bi-check-circle', $berhasil, 'Lunas', 'paid'],
            ['warning', 'bi-hourglass-split', $pending, 'Pending', 'pending'],
            ['info', 'bi-qr-code', $sudah_checkin, 'Check-in', '']
        ];
        foreach($stat_items as $item): ?>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-<?= $item[0] ?> text-white overflow-hidden position-relative transition-transform hover-scale" role="button" onclick="window.location.href='?p=riwayat&status=<?= $item[4] ?>'">
                <div class="card-body p-3 p-md-4 position-relative z-1">
                    <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 mb-2 small fw-bold text-uppercase ls-1" style="font-size: 0.6rem;"><?= $item[3] ?></span>
                    <h2 class="fw-bold mb-0 display-6"><?= $item[2] ?></h2>
                </div>
                <i class="bi <?= $item[1] ?> position-absolute end-0 bottom-0 opacity-25 z-0 display-3 m-1" style="transform: translate(5%, 5%);"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ul class="nav nav-pills bg-white border shadow-sm rounded-pill p-1 gap-1" id="riwayatTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill active py-2 px-4 fw-bold small" id="order-tab" data-bs-toggle="tab" data-bs-target="#order" type="button" onclick="location.hash='order'">
                        <i class="bi bi-receipt me-2"></i>Daftar Pesanan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill py-2 px-4 fw-bold small" id="tiket-tab" data-bs-toggle="tab" data-bs-target="#tiket" type="button" onclick="location.hash='tiket'">
                        <i class="bi bi-ticket-perforated me-2"></i>Tiket Saya
                    </button>
                </li>
            </ul>
            <div class="dropdown">
                <button class="btn btn-white bg-white border shadow-sm rounded-pill px-3 py-2 small fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-funnel me-2"></i>Urutkan: <?= ucfirst($sort_by) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2">
                    <li><a class="dropdown-item rounded-3 small fw-bold" href="?p=riwayat&sort=newest">Terbaru</a></li>
                    <li><a class="dropdown-item rounded-3 small fw-bold" href="?p=riwayat&sort=price_high">Harga Tertinggi</a></li>
                    <li><a class="dropdown-item rounded-3 small fw-bold" href="?p=riwayat&sort=price_low">Harga Terendah</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="tab-content" id="riwayatTabContent">
        <!-- ── Tab Pesanan ── -->
        <div class="tab-pane fade show active" id="order" role="tabpanel">
            <?php if (mysqli_num_rows($query) > 0): ?>
                <div class="row g-4">
                    <?php while ($row = mysqli_fetch_assoc($query)): 
                        $s_color = ($row['status'] == 'paid' ? 'success' : ($row['status'] == 'pending' ? 'warning' : 'danger'));
                    ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white hover-translate-x transition-all">
                            <div class="row g-0">
                                <div class="col-md-3 col-lg-2">
                                    <div class="ratio ratio-1x1 h-100">
                                        <img src="<?= $row['gambar'] ? 'uploads/'.$row['gambar'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=500&q=80' ?>" 
                                             class="object-fit-cover" alt="Event">
                                    </div>
                                </div>
                                <div class="col-md-9 col-lg-10 p-4">
                                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-<?= $s_color ?>-subtle text-<?= $s_color ?> rounded-pill px-3 py-1 small fw-bold text-uppercase ls-1">
                                                    <?= $row['status'] ?>
                                                </span>
                                                <span class="text-muted small fw-bold font-monospace bg-light px-2 rounded">#<?= $row['id_order'] ?></span>
                                                <span class="text-muted small">• <?= date('d M Y, H:i', strtotime($row['tanggal_order'])) ?></span>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($row['nama_event']) ?></h5>
                                            <div class="d-flex align-items-center gap-3 text-muted small mb-3">
                                                <span><i class="bi bi-ticket-perforated me-1 text-primary"></i> <?= htmlspecialchars($row['nama_tiket']) ?></span>
                                                <span><i class="bi bi-people me-1 text-primary"></i> <?= $row['qty'] ?> Tiket</span>
                                                <span><i class="bi bi-geo-alt me-1 text-danger"></i> <?= htmlspecialchars($row['nama_venue']) ?></span>
                                            </div>
                                        </div>
                                        <div class="text-md-end">
                                            <div class="text-muted small fw-bold text-uppercase ls-1 mb-1" style="font-size: 0.65rem;">Total Pembayaran</div>
                                            <div class="h4 fw-bold text-primary mb-3">Rp <?= number_format($row['total'], 0, ',', '.') ?></div>
                                            <div class="d-flex gap-2 justify-content-md-end">
                                                <?php if($row['status'] == 'pending'): ?>
                                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" onclick="cancelOrder(<?= $row['id_order'] ?>)">Batal</button>
                                                    <a href="?p=order_bayar&id=<?= $row['id_order'] ?>" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm">Bayar Sekarang</a>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-light border rounded-pill px-3 fw-bold shadow-sm btn-order-detail" 
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
                                                    <i class="bi bi-info-circle me-1"></i> Rincian
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination Pesanan -->
                <?php if($total_page_o > 1): 
                    $params = "&q=".urlencode($search)."&status=".urlencode($status_filter)."&sort=".urlencode($sort_by)."&pt=".$page_t;
                ?>
                <div class="d-flex justify-content-center mt-5">
                    <nav>
                        <ul class="pagination pagination-rounded gap-2">
                            <li class="page-item <?= ($page_o <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link border-0 shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" href="?p=riwayat&po=<?= $page_o - 1 . $params ?>#order"><i class="bi bi-chevron-left"></i></a>
                            </li>
                            <?php for($i=1; $i<=$total_page_o; $i++): ?>
                                <li class="page-item <?= ($i == $page_o) ? 'active' : '' ?>">
                                    <a class="page-link border-0 shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" href="?p=riwayat&po=<?= $i . $params ?>#order"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page_o >= $total_page_o) ? 'disabled' : '' ?>">
                                <a class="page-link border-0 shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" href="?p=riwayat&po=<?= $page_o + 1 . $params ?>#order"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-inbox text-muted display-1 mb-3 opacity-25"></i>
                    <h5 class="fw-bold">Belum ada pesanan</h5>
                    <p class="text-muted small mb-4">Yuk cari event favoritmu dan mulai pesan tiket!</p>
                    <a href="?p=home" class="btn btn-primary rounded-pill px-4 fw-bold">Jelajahi Event</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Tab Tiket Saya ── -->
        <div class="tab-pane fade" id="tiket" role="tabpanel">
            <?php if (mysqli_num_rows($attendees) > 0): ?>
                <div class="row g-4">
                    <?php while ($tkt = mysqli_fetch_assoc($attendees)): 
                        $is_done = ($tkt['status_checkin'] ?? '') == 'sudah';
                        $is_paid = $tkt['status'] == 'paid';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                            <div class="ratio ratio-21x9">
                                <img src="<?= $tkt['gambar'] ? 'uploads/'.$tkt['gambar'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=500&q=80' ?>" 
                                     class="object-fit-cover" alt="Event">
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-<?= $is_paid ? 'success' : 'warning' ?> rounded-pill shadow-sm px-3 py-2 small fw-bold">
                                        <?= $is_paid ? 'Aktif' : 'Menunggu' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <div class="text-primary fw-bold small text-uppercase ls-1 mb-1" style="font-size: 0.65rem;"><?= htmlspecialchars($tkt['nama_tiket']) ?></div>
                                        <h6 class="fw-bold text-dark text-truncate mb-0"><?= htmlspecialchars($tkt['nama_event']) ?></h6>
                                    </div>
                                    <div class="bg-light rounded p-2 text-center min-vw-10">
                                        <div class="fw-bold lh-1 small"><?= date('d', strtotime($tkt['tanggal'])) ?></div>
                                        <div class="text-muted small" style="font-size: 0.6rem;"><?= date('M', strtotime($tkt['tanggal'])) ?></div>
                                    </div>
                                </div>
                                <p class="text-muted small mb-4 text-truncate-2">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($tkt['nama_venue']) ?>
                                </p>

                                <div class="bg-light rounded-4 p-3 border border-dashed text-center mb-3">
                                    <?php if($is_paid): ?>
                                        <div class="small text-muted text-uppercase fw-bold mb-1" style="font-size: 0.6rem;">Kode Tiket</div>
                                        <div class="h5 fw-bold font-monospace text-primary mb-3 tracking-widest"><?= $tkt['kode_tiket'] ?></div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary flex-grow-1 rounded-pill fw-bold" onclick="showQuickBarcode('<?= $tkt['kode_tiket'] ?>', '<?= addslashes($tkt['nama_event']) ?>')">
                                                <i class="bi bi-qr-code me-2"></i>Quick Scan
                                            </button>
                                            <a href="?p=tiket_print&kode=<?= $tkt['kode_tiket'] ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="py-2">
                                            <p class="small text-muted mb-3">Tiket belum aktif. Selesaikan pembayaran.</p>
                                            <a href="?p=order_bayar&id=<?= $tkt['id_order'] ?>" class="btn btn-sm btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">Bayar Sekarang</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center bg-light rounded-pill px-3 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-<?= $is_done ? 'success' : 'secondary opacity-25' ?> p-1"></div>
                                        <span class="small fw-bold text-muted"><?= $is_done ? 'Digunakan' : 'Belum Digunakan' ?></span>
                                    </div>
                                    <?php if($is_done): ?>
                                        <span class="text-muted small" style="font-size: 0.7rem;"><?= date('d/m H:i', strtotime($tkt['waktu_checkin'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination Tiket -->
                <?php if($total_page_t > 1): 
                    $params = "&q=".urlencode($search)."&status=".urlencode($status_filter)."&sort=".urlencode($sort_by)."&po=".$page_o;
                ?>
                <div class="d-flex justify-content-center mt-5">
                    <nav>
                        <ul class="pagination pagination-rounded gap-2">
                            <li class="page-item <?= ($page_t <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link border-0 shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" href="?p=riwayat&pt=<?= $page_t - 1 . $params ?>#tiket"><i class="bi bi-chevron-left"></i></a>
                            </li>
                            <?php for($i=1; $i<=$total_page_t; $i++): ?>
                                <li class="page-item <?= ($i == $page_t) ? 'active' : '' ?>">
                                    <a class="page-link border-0 shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" href="?p=riwayat&pt=<?= $i . $params ?>#tiket"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page_t >= $total_page_t) ? 'disabled' : '' ?>">
                                <a class="page-link border-0 shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" href="?p=riwayat&pt=<?= $page_t + 1 . $params ?>#tiket"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-ticket-perforated text-muted display-1 mb-3 opacity-25"></i>
                    <h5 class="fw-bold">Belum ada tiket aktif</h5>
                    <p class="text-muted small">Selesaikan pesanan kamu untuk melihat tiket di sini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.ls-1 { letter-spacing: 1px; }
.hover-scale:hover { transform: translateY(-5px) scale(1.02); z-index: 10; }
.hover-translate-x:hover { transform: translateX(5px); }
.transition-all { transition: all 0.3s ease; }
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.border-dashed { border-style: dashed !important; border-width: 2px !important; }
.tracking-widest { letter-spacing: 0.2rem; }
.pagination-rounded .page-link { border-radius: 50% !important; margin: 0 3px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
// Keep existing logic but improve SweetAlert modals
function cancelOrder(id) {
    Swal.fire({
        title: 'Batalkan Pesanan?',
        text: "Kuota tiket akan dilepas kembali ke publik.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tutup',
        reverseButtons: true,
        customClass: { confirmButton: 'rounded-pill px-4', cancelButton: 'rounded-pill px-4' }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'cancel_order');
            formData.append('id_order', id);
            fetch(window.location.href, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({ title: 'Berhasil', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => window.location.reload());
                } else { Swal.fire('Error', data.message, 'error'); }
            });
        }
    });
}

function showQuickBarcode(kode, event) {
    Swal.fire({
        title: 'Quick Scan',
        html: `
            <div class="text-center p-3">
                <div class="fw-bold mb-3 small">${event}</div>
                <div class="bg-white p-4 rounded-4 border border-dashed mb-3 d-inline-block shadow-sm">
                    <svg id="quick-barcode"></svg>
                </div>
                <div class="h4 fw-bold font-monospace text-primary tracking-widest">${kode}</div>
                <p class="text-muted small mt-3 mb-0">Scan barcode ini di pintu masuk event.</p>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: () => {
            JsBarcode("#quick-barcode", kode, { format: "CODE128", width: 2, height: 80, displayValue: false });
        }
    });
}

function showOrderDetail(data) {
    const formatRp = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    let html = `
        <div class="text-start small p-2">
            <div class="mb-4 p-3 rounded-4 bg-light border border-dashed">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">ID Order</span>
                    <span class="fw-bold">#${data.id}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Waktu</span>
                    <span class="fw-bold">${data.tanggal}</span>
                </div>
            </div>
            
            <div class="mb-4">
                <h6 class="fw-bold mb-2">Item Detail</h6>
                <div class="p-3 bg-white border rounded-4">
                    <div class="fw-bold text-dark mb-1">${data.event}</div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">${data.tiket} (x${data.qty})</span>
                        <span class="fw-bold">${formatRp(data.harga * data.qty)}</span>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <h6 class="fw-bold mb-2">Rincian Pembayaran</h6>
                <div class="p-3 bg-white border rounded-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span>${formatRp(data.harga * data.qty)}</span>
                    </div>
                    ${data.potongan > 0 ? `
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Potongan Voucher</span>
                        <span>- ${formatRp(data.potongan)}</span>
                    </div>
                    ` : ''}
                    <div class="d-flex justify-content-between pt-2 border-top">
                        <span class="fw-bold">Total Akhir</span>
                        <span class="fw-bold text-primary h5 mb-0">${formatRp(data.total)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    Swal.fire({
        title: 'Rincian Pesanan',
        html: html,
        showCloseButton: true,
        showConfirmButton: data.status === 'pending',
        confirmButtonText: '<i class="bi bi-wallet2 me-2"></i>Bayar Sekarang',
        confirmButtonColor: '#6366f1',
        cancelButtonText: 'Tutup',
        showCancelButton: true,
        customClass: { confirmButton: 'rounded-pill px-4', cancelButton: 'rounded-pill px-4' }
    }).then((result) => {
        if (result.isConfirmed) { window.location.href = '?p=order_bayar&id=' + data.id; }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.btn-order-detail').forEach(btn => {
        btn.addEventListener('click', function() {
            showOrderDetail(JSON.parse(this.getAttribute('data-order')));
        });
    });

    // Auto-select tab based on hash
    let hash = window.location.hash;
    if (hash) {
        let tabEl = document.querySelector(`button[data-bs-target="${hash}"]`);
        if (tabEl) {
            let tab = new bootstrap.Tab(tabEl);
            tab.show();
        }
    }
});
</script>
