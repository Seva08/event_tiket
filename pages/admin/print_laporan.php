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
    <title>Laporan Full - <?= $nama_event ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
            table { width: 100% !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h4 class="mb-0">Pratinjau Cetak Laporan</h4>
            <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
        </div>

        <div class="text-center mb-4">
            <h2 class="fw-bold">LAPORAN PENJUALAN TIKET</h2>
            <h4 class="text-uppercase"><?= $nama_event ?></h4>
            <p class="mb-0">Periode: <?= $f_month > 0 ? $months[$f_month-1] : 'Semua Bulan' ?> <?= $f_year > 0 ? $f_year : 'Semua Tahun' ?></p>
            <p class="text-muted small">Dicetak pada: <?= date('d M Y H:i') ?></p>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode Tiket</th>
                    <th>Nama Pengunjung</th>
                    <th>Event & Tipe</th>
                    <th>Status Check-in</th>
                    <th>Waktu Order</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td class="font-monospace fw-bold"><?= $row['kode_tiket'] ?></td>
                    <td>
                        <?= htmlspecialchars($row['nama']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['nama_event']) ?><br>
                        <small class="badge bg-light text-dark border"><?= htmlspecialchars($row['nama_tiket']) ?></small>
                    </td>
                    <td>
                        <?= strtoupper($row['status_checkin']) ?>
                        <?= $row['waktu_checkin'] ? ' ('.date('H:i', strtotime($row['waktu_checkin'])).')' : '' ?>
                    </td>
                    <td><?= date('d/m/y H:i', strtotime($row['tanggal_order'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
