<?php
include '../../config.php';

// Validasi login admin
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

// Header untuk Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Full_Tiket_".date('Ymd').".xls");
?>
<table border="1">
    <thead>
        <tr style="background-color: #eee; font-weight: bold;">
            <th>No</th>
            <th>Kode Tiket</th>
            <th>ID Order</th>
            <th>Nama Pengunjung</th>
            <th>Email</th>
            <th>Event</th>
            <th>Tipe Tiket</th>
            <th>Status Bayar</th>
            <th>Status Check-in</th>
            <th>Waktu Check-in</th>
            <th>Waktu Order</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; while($row = mysqli_fetch_assoc($res)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td>'<?= $row['kode_tiket'] ?></td>
            <td>#<?= $row['id_order'] ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['nama_event']) ?></td>
            <td><?= htmlspecialchars($row['nama_tiket']) ?></td>
            <td><?= strtoupper($row['order_status']) ?></td>
            <td><?= strtoupper($row['status_checkin']) ?></td>
            <td><?= $row['waktu_checkin'] ? date('d/m/Y H:i', strtotime($row['waktu_checkin'])) : '-' ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_order'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
