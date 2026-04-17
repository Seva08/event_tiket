<?php
$tiket_terjual = mysqli_query($conn,
    "SELECT e.nama_event, e.tanggal, v.nama_venue,
        COUNT(DISTINCT o.id_order) as total_order,
        SUM(od.qty) as total_tiket_terjual,
        SUM(od.subtotal) as total_pendapatan
     FROM event e
     JOIN venue v ON e.id_venue = v.id_venue
     LEFT JOIN tiket t ON e.id_event = t.id_event
     LEFT JOIN order_detail od ON t.id_tiket = od.id_tiket
     LEFT JOIN orders o ON od.id_order = o.id_order AND o.status = 'paid'
     GROUP BY e.id_event
     ORDER BY e.tanggal DESC");

$transaksi = mysqli_query($conn,
    "SELECT o.*, u.nama, u.email, od.qty, od.subtotal,
        t.nama_tiket, e.nama_event, v.kode_voucher, v.potongan
     FROM orders o
     JOIN users u ON o.id_user = u.id_user
     LEFT JOIN order_detail od ON o.id_order = od.id_order
     LEFT JOIN tiket t ON od.id_tiket = t.id_tiket
     LEFT JOIN event e ON t.id_event = e.id_event
     LEFT JOIN voucher v ON o.id_voucher = v.id_voucher
     ORDER BY o.tanggal_order DESC");
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2><i class="bi bi-file-earmark-text"></i> Laporan</h2>
        <ul class="nav nav-tabs mb-3" id="laporanTab" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" id="tiket-tab" data-bs-toggle="tab" data-bs-target="#tiket" type="button"><i class="bi bi-ticket"></i> Tiket Terjual per Event</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="transaksi-tab" data-bs-toggle="tab" data-bs-target="#transaksi" type="button"><i class="bi bi-cart"></i> Data Transaksi</button></li>
        </ul>
        <div class="tab-content" id="laporanTabContent">
            <div class="tab-pane fade show active" id="tiket" role="tabpanel">
                <div class="card"><div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-graph-up"></i> Tiket Terjual per Event</h5></div>
                <div class="card-body"><div class="table-responsive"><table class="table table-striped table-hover">
                    <thead class="table-dark"><tr><th>No</th><th>Event</th><th>Tanggal</th><th>Venue</th><th>Total Order</th><th>Tiket Terjual</th><th>Pendapatan</th></tr></thead>
                    <tbody><?php $no = 1; while ($row = mysqli_fetch_assoc($tiket_terjual)): ?>
                    <tr><td><?= $no++ ?></td><td><?= htmlspecialchars($row['nama_event']) ?></td><td><?= date('d M Y', strtotime($row['tanggal'])) ?></td><td><?= htmlspecialchars($row['nama_venue']) ?></td><td><?= $row['total_order'] ?? 0 ?></td><td><?= $row['total_tiket_terjual'] ?? 0 ?></td><td>Rp <?= number_format($row['total_pendapatan'] ?? 0, 0, ',', '.') ?></td></tr>
                    <?php endwhile; ?></tbody>
                </table></div></div></div>
            </div>
            <div class="tab-pane fade" id="transaksi" role="tabpanel">
                <div class="card"><div class="card-header bg-success text-white"><h5 class="mb-0"><i class="bi bi-cart-check"></i> Data Transaksi Lengkap</h5></div>
                <div class="card-body"><div class="table-responsive"><table class="table table-striped table-hover">
                    <thead class="table-dark"><tr><th>ID Order</th><th>User</th><th>Event</th><th>Tiket</th><th>Qty</th><th>Subtotal</th><th>Voucher</th><th>Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                    <tbody><?php while ($row = mysqli_fetch_assoc($transaksi)):
                        $badge_class = $row['status']=='paid' ? 'success' : ($row['status']=='pending' ? 'warning' : 'danger');
                    ?>
                    <tr>
                        <td>#<?= $row['id_order'] ?></td><td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['nama_event'] ?? '-') ?></td><td><?= htmlspecialchars($row['nama_tiket'] ?? '-') ?></td>
                        <td><?= $row['qty'] ?? 0 ?></td><td>Rp <?= number_format($row['subtotal'] ?? 0, 0, ',', '.') ?></td>
                        <td><?= $row['kode_voucher'] ? $row['kode_voucher'] : '-' ?></td>
                        <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                        <td><span class="badge bg-<?= $badge_class ?>"><?= ucfirst($row['status']) ?></span></td>
                        <td><?= date('d M Y H:i', strtotime($row['tanggal_order'])) ?></td>
                        <td><a href="?p=admin_order_detail&id=<?= $row['id_order'] ?>" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    <?php endwhile; ?></tbody>
                </table></div></div></div>
            </div>
        </div>
    </main>
</div></div>
