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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0"><i class="bi bi-file-earmark-text"></i> Laporan</h2>
            <div class="d-print-none">
                <button onclick="window.print()" class="btn btn-outline-danger me-2"><i class="bi bi-file-earmark-pdf"></i> Cetak PDF</button>
                <button onclick="exportCurrentTabExcel()" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Export Excel</button>
            </div>
        </div>
        <ul class="nav nav-tabs mb-3" id="laporanTab" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" id="tiket-tab" data-bs-toggle="tab" data-bs-target="#tiket" type="button"><i class="bi bi-ticket"></i> Tiket Terjual per Event</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="transaksi-tab" data-bs-toggle="tab" data-bs-target="#transaksi" type="button"><i class="bi bi-cart"></i> Data Transaksi</button></li>
        </ul>
        <div class="tab-content" id="laporanTabContent">
            <div class="tab-pane fade show active" id="tiket" role="tabpanel">
                <div class="card"><div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-graph-up"></i> Tiket Terjual per Event</h5></div>
                <div class="card-body"><div class="table-responsive"><table class="table table-striped table-hover" id="table-tiket">
                    <thead class="table-dark"><tr><th>No</th><th>Event</th><th>Tanggal</th><th>Venue</th><th>Total Order</th><th>Tiket Terjual</th><th>Pendapatan</th></tr></thead>
                    <tbody><?php $no = 1; while ($row = mysqli_fetch_assoc($tiket_terjual)): ?>
                    <tr><td><?= $no++ ?></td><td><?= htmlspecialchars($row['nama_event']) ?></td><td><?= date('d M Y', strtotime($row['tanggal'])) ?></td><td><?= htmlspecialchars($row['nama_venue']) ?></td><td><?= $row['total_order'] ?? 0 ?></td><td><?= $row['total_tiket_terjual'] ?? 0 ?></td><td>Rp <?= number_format($row['total_pendapatan'] ?? 0, 0, ',', '.') ?></td></tr>
                    <?php endwhile; ?></tbody>
                </table></div></div></div>
            </div>
            <div class="tab-pane fade" id="transaksi" role="tabpanel">
                <div class="card"><div class="card-header bg-success text-white"><h5 class="mb-0"><i class="bi bi-cart-check"></i> Data Transaksi Lengkap</h5></div>
                <div class="card-body"><div class="table-responsive"><table class="table table-striped table-hover" id="table-transaksi">
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

<style>
@media print {
    /* Sembunyikan elemen yang tidak perlu dicetak */
    .sidebar, .navbar, .nav-tabs, .btn, .d-print-none { display: none !important; }
    .col-md-10 { width: 100% !important; margin: 0 !important; padding: 0 !important; }
    body { background-color: white !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-header { background-color: transparent !important; color: black !important; border-bottom: 2px solid #000 !important; padding-left: 0; }
    .table-dark th { background-color: #f8f9fa !important; color: black !important; border-bottom: 2px solid #000 !important; }
    /* Hanya tampilkan tab yang aktif saat di-print */
    .tab-pane { display: none !important; }
    .tab-pane.active { display: block !important; }
    a { text-decoration: none !important; color: black !important; }
}
</style>

<script>
function exportCurrentTabExcel() {
    // Cek tab mana yang aktif
    let activeTab = document.querySelector('.tab-pane.active').id;
    let tableID = activeTab === 'tiket' ? 'table-tiket' : 'table-transaksi';
    let filename = activeTab === 'tiket' ? 'Laporan_Tiket_Terjual' : 'Laporan_Data_Transaksi';
    
    var table = document.getElementById(tableID);
    var clone = table.cloneNode(true);
    
    // Hapus kolom 'Aksi' jika berada di tab transaksi
    if (activeTab === 'transaksi') {
        var rows = clone.rows;
        for (var i = 0; i < rows.length; i++) {
            rows[i].deleteCell(-1); // Hapus sel terakhir
        }
    }
    
    var uri = 'data:application/vnd.ms-excel;base64,';
    var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta charset="utf-8"></head><body><table border="1">{table}</table></body></html>';
    var base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) };
    var format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) };
    
    var ctx = {worksheet: filename, table: clone.innerHTML};
    
    var a = document.createElement('a');
    a.href = uri + base64(format(template, ctx));
    a.download = filename + '_' + new Date().toISOString().slice(0,10) + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>
