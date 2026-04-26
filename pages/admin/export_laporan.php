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

// Ambil Statistik Ringkasan (Konsisten)
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
    SELECT a.*, u.nama, u.email, t.nama_tiket, e.nama_event, o.id_order, o.status as order_status, o.tanggal_order, o.total as order_total
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
header("Content-Disposition: attachment; filename=Laporan_Tiket_".date('Ymd_His').".xls");
?>
<table border="1">
    <tr>
        <th colspan="11" style="font-size: 16px; font-weight: bold;">LAPORAN PENJUALAN TIKET YUIPASS</th>
    </tr>
    <tr>
        <th colspan="11">Tanggal Export: <?= date('d/m/Y H:i') ?></th>
    </tr>
    <tr></tr>
    <!-- Ringkasan Statistik -->
    <tr style="background-color: #f2f2f2; font-weight: bold;">
        <th colspan="3">RINGKASAN LAPORAN</th>
        <th colspan="8"></th>
    </tr>
    <tr>
        <td colspan="2">Total Tiket Terjual</td>
        <td style="font-weight: bold;"><?= $stats['total_tiket'] ?></td>
        <td colspan="8"></td>
    </tr>
    <tr>
        <td colspan="2">Total Check-in</td>
        <td style="font-weight: bold; color: green;"><?= $stats['total_checkin'] ?></td>
        <td colspan="8"></td>
    </tr>
    <tr>
        <td colspan="2">Total Pendapatan Bersih</td>
        <td style="font-weight: bold; color: blue;">Rp <?= number_format($stats['total_omzet'] ?? 0, 0, ',', '.') ?></td>
        <td colspan="8"></td>
    </tr>
    <tr></tr>
    <thead>
        <tr style="background-color: #333; color: white; font-weight: bold;">
            <th>No</th>
            <th>Kode Tiket</th>
            <th>ID Order</th>
            <th>Nama Pengunjung</th>
            <th>Email</th>
            <th>Event</th>
            <th>Tipe Tiket</th>
            <th>Status Bayar</th>
            <th>Bayar Akhir (Incl. Diskon)</th>
            <th>Status Check-in</th>
            <th>Waktu Order</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; while($row = mysqli_fetch_assoc($res)): ?>
        <tr>
            <td align="center"><?= $no++ ?></td>
            <td style="mso-number-format:'\@';"><?= $row['kode_tiket'] ?></td>
            <td>#<?= $row['id_order'] ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['nama_event']) ?></td>
            <td><?= htmlspecialchars($row['nama_tiket']) ?></td>
            <td align="center"><?= strtoupper($row['order_status']) ?></td>
            <td align="right">Rp<?= number_format($row['order_total'], 0, ',', '.') ?></td>
            <td align="center"><?= strtoupper($row['status_checkin']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_order'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
