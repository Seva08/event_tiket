<?php
// Configuration
$id_event_filter = isset($_GET['id_event']) ? (int)$_GET['id_event'] : 0;
$q_search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$f_month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
$f_year = isset($_GET['year']) ? (int)$_GET['year'] : 0;

// Base condition for status 'paid'
// Gunakan alias o.status dan t.id_event agar tidak ambigu
$base_where = "WHERE o.status = 'paid'";
if ($id_event_filter > 0) $base_where .= " AND t.id_event = $id_event_filter";
if ($f_month > 0) $base_where .= " AND MONTH(o.tanggal_order) = $f_month";
if ($f_year > 0) $base_where .= " AND YEAR(o.tanggal_order) = $f_year";

// 1. Statistik Ringkasan (Cards)
$q_stats_main = mysqli_query($conn, "
    SELECT 
        (SELECT SUM(total) FROM orders o $base_where) as total_omzet,
        SUM(od.qty) as total_terjual,
        COUNT(DISTINCT o.id_order) as total_pesanan
    FROM orders o
    JOIN order_detail od ON o.id_order = od.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    $base_where
");
if (!$q_stats_main) die("Error Stats: " . mysqli_error($conn));
$stats_main = mysqli_fetch_assoc($q_stats_main);

// Hitung Total Check-in secara terpisah
// Samakan aliasnya agar konsisten
$where_checkin = "WHERE o.status = 'paid' AND a.status_checkin = 'sudah'";
if ($id_event_filter > 0) $where_checkin .= " AND t.id_event = $id_event_filter";
if ($f_month > 0) $where_checkin .= " AND MONTH(o.tanggal_order) = $f_month";
if ($f_year > 0) $where_checkin .= " AND YEAR(o.tanggal_order) = $f_year";

$q_checkin = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    $where_checkin
");
if (!$q_checkin) die("Error Checkin: " . mysqli_error($conn));
$total_checkin = mysqli_fetch_assoc($q_checkin)['total'];

// Gabungkan stats
$stats = [
    'total_omzet' => $stats_main['total_omzet'],
    'total_terjual' => $stats_main['total_terjual'],
    'total_pesanan' => $stats_main['total_pesanan'],
    'total_checkin' => $total_checkin
];

// 2. Ambil List Event untuk Dropdown Filter
$list_event = mysqli_query($conn, "SELECT id_event, nama_event FROM event ORDER BY tanggal DESC");

// 3. Ambil Data Detail Pengunjung & Transaksi
$limit = 10;
$p = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($p < 1) $p = 1;
$offset = ($p - 1) * $limit;

$where_detail = $base_where;
if ($q_search) {
    $where_detail .= " AND (u.nama LIKE '%$q_search%' OR a.kode_tiket LIKE '%$q_search%' OR o.id_order LIKE '%$q_search%')";
}

$count_query = "
    SELECT COUNT(*) as c 
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN users u ON o.id_user = u.id_user
    JOIN tiket t ON od.id_tiket = t.id_tiket
    $where_detail
";
$q_count = mysqli_query($conn, $count_query);
if (!$q_count) die("Error Count: " . mysqli_error($conn));
$total_rows = mysqli_fetch_assoc($q_count)['c'];
$total_pages = ceil($total_rows / $limit);

$query_detail = "
    SELECT a.*, u.nama, u.email, t.nama_tiket, e.nama_event, o.id_order, o.status as order_status, o.tanggal_order
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN users u ON o.id_user = u.id_user
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    $where_detail
    ORDER BY o.tanggal_order DESC
    LIMIT $limit OFFSET $offset
";
$data_laporan = mysqli_query($conn, $query_detail);
if (!$data_laporan) die("Error Detail: " . mysqli_error($conn));
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i> Laporan Lanjutan</h2>
                    <p class="text-muted mb-0">Analisis performa penjualan dan kedatangan pengunjung</p>
                </div>
                <div class="d-print-none">
                    <a href="pages/admin/print_laporan.php?id_event=<?= $id_event_filter ?>&q=<?= urlencode($q_search) ?>&month=<?= $f_month ?>&year=<?= $f_year ?>" target="_blank" class="btn btn-white shadow-sm border px-3 rounded-pill fw-semibold"><i class="bi bi-printer me-2"></i> Cetak Full (PDF)</a>
                    <a href="pages/admin/export_laporan.php?id_event=<?= $id_event_filter ?>&q=<?= urlencode($q_search) ?>&month=<?= $f_month ?>&year=<?= $f_year ?>" target="_blank" class="btn btn-success shadow-sm px-3 rounded-pill fw-semibold ms-2"><i class="bi bi-file-earmark-excel me-2"></i> Export Full (Excel)</a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Revenue</span>
                                <i class="bi bi-cash-stack fs-4 opacity-75"></i>
                            </div>
                            <h3 class="fw-bold mb-1">Rp <?= number_format($stats['total_omzet'] ?? 0, 0, ',', '.') ?></h3>
                            <p class="mb-0 small opacity-75">Total pendapatan (Lunas)</p>
                        </div>
                        <i class="bi bi-cash-stack position-absolute end-0 bottom-0 opacity-25 z-0 display-1"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-info text-white overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Sold</span>
                                <i class="bi bi-ticket-perforated fs-4 opacity-75"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?= number_format($stats['total_terjual'] ?? 0, 0, ',', '.') ?></h3>
                            <p class="mb-0 small opacity-75">Tiket telah terjual</p>
                        </div>
                        <i class="bi bi-ticket-perforated position-absolute end-0 bottom-0 opacity-25 z-0 display-1"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-success text-white overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Check-in</span>
                                <i class="bi bi-person-check fs-4 opacity-75"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?= number_format($stats['total_checkin'] ?? 0, 0, ',', '.') ?></h3>
                            <p class="mb-0 small opacity-75">Pengunjung telah hadir</p>
                        </div>
                        <i class="bi bi-person-check position-absolute end-0 bottom-0 opacity-25 z-0 display-1"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-dark text-white overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Transactions</span>
                                <i class="bi bi-bag-check fs-4 opacity-75"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?= number_format($stats['total_pesanan'] ?? 0, 0, ',', '.') ?></h3>
                            <p class="mb-0 small opacity-75">Total pesanan sukses</p>
                        </div>
                        <i class="bi bi-bag-check position-absolute end-0 bottom-0 opacity-25 z-0 display-1"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none">
                <div class="card-body p-4">
                    <form method="GET" action="index.php" class="row g-3">
                        <input type="hidden" name="p" value="admin_laporan">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-uppercase opacity-75">Filter Per Event</label>
                            <select name="id_event" class="form-select border-0 bg-light py-2 px-3 rounded-3 shadow-none" onchange="this.form.submit()">
                                <option value="0">-- Semua Event --</option>
                                <?php mysqli_data_seek($list_event, 0); while($ev = mysqli_fetch_assoc($list_event)): ?>
                                    <option value="<?= $ev['id_event'] ?>" <?= $id_event_filter == $ev['id_event'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ev['nama_event']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-uppercase opacity-75">Bulan</label>
                            <select name="month" class="form-select border-0 bg-light py-2 px-3 rounded-3 shadow-none" onchange="this.form.submit()">
                                <option value="0">Semua</option>
                                <?php
                                $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                                foreach ($months as $idx => $mname):
                                    $mval = $idx + 1;
                                    echo "<option value='$mval' ".($f_month == $mval ? 'selected' : '').">$mname</option>";
                                endforeach;
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-uppercase opacity-75">Tahun</label>
                            <select name="year" class="form-select border-0 bg-light py-2 px-3 rounded-3 shadow-none" onchange="this.form.submit()">
                                <option value="0">Semua</option>
                                <?php
                                $current_y = date('Y');
                                for ($y = $current_y; $y >= 2024; $y--):
                                    echo "<option value='$y' ".($f_year == $y ? 'selected' : '').">$y</option>";
                                endfor;
                                ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-uppercase opacity-75">Pencarian Cepat</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light rounded-start-3"><i class="bi bi-search"></i></span>
                                <input type="text" name="q" class="form-control border-0 bg-light py-2 shadow-none" placeholder="Cari Nama, Email, atau Kode Tiket..." value="<?= htmlspecialchars($q_search) ?>">
                                <button type="submit" class="btn btn-primary px-4 rounded-end-3">Cari</button>
                                <?php if($id_event_filter || $q_search || $f_month || $f_year): ?>
                                    <a href="?p=admin_laporan" class="btn btn-outline-secondary px-3 ms-2 rounded-3"><i class="bi bi-arrow-counterclockwise"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Detailed Table Section -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tableLaporan">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase fw-bold text-muted">NO</th>
                                    <th class="py-3 border-0 small text-uppercase fw-bold text-muted">Ticket ID</th>
                                    <th class="py-3 border-0 small text-uppercase fw-bold text-muted">Attendee</th>
                                    <th class="py-3 border-0 small text-uppercase fw-bold text-muted">Event Details</th>
                                    <th class="py-3 border-0 small text-uppercase fw-bold text-muted">Payment</th>
                                    <th class="py-3 border-0 small text-uppercase fw-bold text-muted text-center">Check-in Status</th>
                                    <th class="pe-4 py-3 border-0 small text-uppercase fw-bold text-muted text-end">Order Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($data_laporan)): 
                                    $is_paid = in_array(strtolower($row['order_status']), ['success', 'paid']);
                                    $pay_badge = $is_paid ? 'success' : 'warning';
                                    $checkin_badge = ($row['status_checkin'] == 'sudah') ? 'success' : 'secondary';
                                    $checkin_icon = ($row['status_checkin'] == 'sudah') ? 'check-circle-fill' : 'dash-circle';
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-bold"><?= $no++ ?></td>
                                    <td>
                                        <div class="font-monospace text-primary fw-bold small">
                                            <?= $row['kode_tiket'] ?>
                                        </div>
                                        <div class="text-muted small">#ORDER-<?= $row['id_order'] ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark mb-1"><?= htmlspecialchars($row['nama_event']) ?></div>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 small"><?= htmlspecialchars($row['nama_tiket']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="rounded-circle bg-<?= $pay_badge ?> me-2 d-inline-block p-1"></span>
                                            <span class="fw-bold text-<?= $pay_badge ?> small">
                                                <?= strtoupper($row['order_status']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if($row['status_checkin'] == 'sudah'): ?>
                                            <div class="badge bg-success bg-opacity-10 text-success p-2 rounded-3 d-inline-flex align-items-center">
                                                <i class="bi bi-check-circle-fill me-2"></i>
                                                <span><?= date('H:i', strtotime($row['waktu_checkin'])) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="badge bg-light text-muted p-2 rounded-3 d-inline-flex align-items-center">
                                                <i class="bi bi-dash-circle me-2"></i>
                                                <span>BELUM</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="small fw-bold text-dark"><?= date('d M Y', strtotime($row['tanggal_order'])) ?></div>
                                        <div class="text-muted small"><?= date('H:i', strtotime($row['tanggal_order'])) ?> WIB</div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if(mysqli_num_rows($data_laporan) == 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox display-1 d-block mb-3 opacity-25"></i>
                                            <p class="fs-5 mb-0">Tidak ada data ditemukan</p>
                                            <small>Coba sesuaikan filter atau pencarian Anda.</small>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Section -->
                <?php if($total_pages > 1): ?>
                <div class="card-footer bg-white border-top-0 p-4">
                    <nav>
                        <ul class="pagination pagination-sm justify-content-center mb-0">
                            <li class="page-item <?= ($p <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link border-0 shadow-none rounded-pill mx-1" href="?p=admin_laporan&id_event=<?= $id_event_filter ?>&q=<?= urlencode($q_search) ?>&month=<?= $f_month ?>&year=<?= $f_year ?>&page=<?= $p-1 ?>"><i class="bi bi-chevron-left"></i></a>
                            </li>
                            <?php for($i=1; $i<=$total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $p) ? 'active' : '' ?>">
                                    <a class="page-link border-0 shadow-none rounded-pill mx-1" href="?p=admin_laporan&id_event=<?= $id_event_filter ?>&q=<?= urlencode($q_search) ?>&month=<?= $f_month ?>&year=<?= $f_year ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($p >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link border-0 shadow-none rounded-pill mx-1" href="?p=admin_laporan&id_event=<?= $id_event_filter ?>&q=<?= urlencode($q_search) ?>&month=<?= $f_month ?>&year=<?= $f_year ?>&page=<?= $p+1 ?>"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>



<script>
// Pencarian otomatis saat dropdown berubah
function autoSubmit() {
    document.querySelector('form').submit();
}
</script>
