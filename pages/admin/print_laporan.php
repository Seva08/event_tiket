<?php
include '../../config.php';
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$id_event_filter = isset($_GET['id_event']) ? (int)$_GET['id_event'] : 0;
$q_search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$f_month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
$f_year = isset($_GET['year']) ? (int)$_GET['year'] : 0;

$where_detail = "WHERE o.status = 'paid'";
if ($id_event_filter > 0) $where_detail .= " AND e.id_event = $id_event_filter";
if ($q_search) $where_detail .= " AND (u.nama LIKE '%$q_search%' OR a.kode_tiket LIKE '%$q_search%' OR o.id_order LIKE '%$q_search%')";
if ($f_month > 0) $where_detail .= " AND MONTH(o.tanggal_order) = $f_month";
if ($f_year > 0) $where_detail .= " AND YEAR(o.tanggal_order) = $f_year";

// Ambil Statistik Ringkasan (Rumus Konsisten)
$q_stats = mysqli_query($conn, "
    SELECT 
        (SELECT SUM(total) FROM orders o $where_detail) as total_omzet,
        COUNT(a.id_attendee) as total_tiket,
        SUM(CASE WHEN a.status_checkin = 'sudah' THEN 1 ELSE 0 END) as total_checkin
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN orders o ON od.id_order = o.id_order
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    $where_detail
");
$stats = mysqli_fetch_assoc($q_stats);

// Ambil Data List
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
";
$res = mysqli_query($conn, $query_detail);

$nama_event = "Semua Event";
if($id_event_filter > 0) {
    $ev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_event FROM event WHERE id_event = $id_event_filter"));
    $nama_event = $ev['nama_event'];
}
$months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan_YuiPass_<?= date('Ymd_His') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 11px; color: #333; background: #fff; }
        .table { border-color: #000; }
        .table thead th { 
            background-color: #f0f0f0 !important; 
            color: #000 !important; 
            border: 1px solid #000 !important;
            -webkit-print-color-adjust: exact;
        }
        .table td { border: 1px solid #000 !important; }
        .summary-box { 
            border: 2px solid #000; 
            padding: 15px; 
            margin-bottom: 20px; 
            background-color: #f9f9f9 !important; 
            -webkit-print-color-adjust: exact;
        }
        
        @page {
            size: A4;
            margin: 1.5cm;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .container-fluid { width: 100% !important; padding: 0 !important; }
            .summary-box { border: 1px solid #000 !important; }
            .badge { border: 1px solid #000 !important; color: #000 !important; }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-2">
        <div class="alert alert-primary d-flex justify-content-between align-items-center mb-4 no-print border-0 shadow-lg rounded-4">
            <div class="d-flex align-items-center">
                <div class="bg-white text-primary rounded-circle p-2 me-3 shadow-sm">
                    <i class="bi bi-file-earmark-pdf-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Siap Export ke PDF?</h6>
                    <small>PENTING: Pastikan pilih <b>"Save as PDF"</b> di kolom Destination agar nama file otomatis terisi.</small>
                </div>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-download me-2"></i>Export Sekarang
                </button>
                <button onclick="window.close()" class="btn btn-light rounded-pill px-3 border ms-2">Kembali</button>
            </div>
        </div>

        <div class="text-center mb-4 pb-3 border-bottom">
            <h2 class="fw-bold mb-1">YuiPass Ticketing System</h2>
            <h4 class="text-uppercase mb-1">Laporan Penjualan Tiket & Pengunjung</h4>
            <p class="mb-0 fs-5 fw-semibold text-primary"><?= $nama_event ?></p>
            <p class="mb-0">Periode: <?= $f_month > 0 ? $months[$f_month-1] : 'Semua Bulan' ?> <?= $f_year > 0 ? $f_year : 'Semua Tahun' ?></p>
            <p class="text-muted small mt-1">Dicetak pada: <?= date('d M Y, H:i') ?> oleh Admin</p>
        </div>

        <!-- Ringkasan Laporan -->
        <div class="summary-box rounded-3 shadow-sm">
            <div class="row text-center">
                <div class="col-4 border-end">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Total Tiket Terjual</small>
                    <h4 class="fw-bold mb-0"><?= number_format($stats['total_tiket'], 0, ',', '.') ?></h4>
                </div>
                <div class="col-4 border-end">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Total Check-in</small>
                    <h4 class="fw-bold mb-0 text-success"><?= number_format($stats['total_checkin'], 0, ',', '.') ?></h4>
                </div>
                <div class="col-4">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Total Pendapatan Bersih</small>
                    <h4 class="fw-bold mb-0 text-primary">Rp <?= number_format($stats['total_omzet'] ?? 0, 0, ',', '.') ?></h4>
                </div>
            </div>
        </div>

        <table class="table table-bordered table-sm align-middle">
            <thead>
                <tr>
                    <th width="40" class="text-center">NO</th>
                    <th width="120">KODE TIKET</th>
                    <th>PENGUNJUNG</th>
                    <th>EVENT & TIPE TIKET</th>
                    <th width="120" class="text-center">STATUS CHECK-IN</th>
                    <th width="130" class="text-end">WAKTU ORDER</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($res) > 0): ?>
                    <?php $no=1; while($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="fw-bold font-monospace text-primary"><?= $row['kode_tiket'] ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($row['nama']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($row['email']) ?></div>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($row['nama_event']) ?></div>
                            <span class="text-muted small"><?= htmlspecialchars($row['nama_tiket']) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold <?= $row['status_checkin'] == 'sudah' ? 'text-success' : 'text-muted' ?>">
                                <?= strtoupper($row['status_checkin']) ?>
                            </span>
                            <?php if($row['waktu_checkin']): ?>
                                <br><small class="text-muted"><?= date('H:i', strtotime($row['waktu_checkin'])) ?> WIB</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="fw-bold"><?= date('d/m/Y', strtotime($row['tanggal_order'])) ?></div>
                            <div class="text-muted small"><?= date('H:i', strtotime($row['tanggal_order'])) ?> WIB</div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4">Tidak ada data transaksi ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-5 pt-3">
            <div class="row">
                <div class="col-8">
                    <p class="small text-muted italic">* Laporan ini dibuat secara otomatis oleh sistem YuiPass Ticketing.</p>
                </div>
                <div class="col-4 text-center">
                    <p class="mb-5 small">Mengetahui,<br>Admin YuiPass</p>
                    <p class="fw-bold mb-0">__________________________</p>
                    <p class="small">Tanda Tangan & Nama Terang</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
