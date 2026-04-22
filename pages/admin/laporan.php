<?php
// Configuration
$limit = 10;

// Tab 1: Tiket Terjual per Event
$p1 = isset($_GET['p1']) ? (int)$_GET['p1'] : 1;
if ($p1 < 1) $p1 = 1;
$offset1 = ($p1 - 1) * $limit;
$q1 = isset($_GET['q1']) ? mysqli_real_escape_string($conn, $_GET['q1']) : '';

$where1 = "WHERE (e.nama_event LIKE '%$q1%' OR v.nama_venue LIKE '%$q1%')";

// Total data for pagination Tab 1
$count1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT e.id_event) as c FROM event e JOIN venue v ON e.id_venue = v.id_venue $where1"))['c'];
$total_page1 = ceil($count1 / $limit);

$tiket_terjual = mysqli_query($conn,
    "SELECT e.nama_event, e.tanggal, v.nama_venue,
        COUNT(DISTINCT CASE WHEN o.status = 'paid' THEN o.id_order END) as total_order,
        SUM(CASE WHEN o.status = 'paid' THEN od.qty ELSE 0 END) as total_tiket_terjual,
        SUM(CASE WHEN o.status = 'paid' THEN od.subtotal ELSE 0 END) as total_pendapatan
     FROM event e
     JOIN venue v ON e.id_venue = v.id_venue
     LEFT JOIN tiket t ON e.id_event = t.id_event
     LEFT JOIN order_detail od ON t.id_tiket = od.id_tiket
     LEFT JOIN orders o ON od.id_order = o.id_order
     $where1
     GROUP BY e.id_event
     ORDER BY e.tanggal DESC
     LIMIT $limit OFFSET $offset1");

// Tab 2: Data Transaksi
$p2 = isset($_GET['p2']) ? (int)$_GET['p2'] : 1;
if ($p2 < 1) $p2 = 1;
$offset2 = ($p2 - 1) * $limit;
$q2 = isset($_GET['q2']) ? mysqli_real_escape_string($conn, $_GET['q2']) : '';

$where2 = "WHERE (u.nama LIKE '%$q2%' OR u.email LIKE '%$q2%' OR o.id_order LIKE '%$q2%' OR e.nama_event LIKE '%$q2%')";

// Total data for pagination Tab 2
$count2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders o JOIN users u ON o.id_user = u.id_user LEFT JOIN order_detail od ON o.id_order = od.id_order LEFT JOIN tiket t ON od.id_tiket = t.id_tiket LEFT JOIN event e ON t.id_event = e.id_event $where2"))['c'];
$total_page2 = ceil($count2 / $limit);

$transaksi = mysqli_query($conn,
    "SELECT o.*, u.nama, u.email, od.qty, od.subtotal,
        t.nama_tiket, e.nama_event, v.kode_voucher, v.potongan
     FROM orders o
     JOIN users u ON o.id_user = u.id_user
     LEFT JOIN order_detail od ON o.id_order = od.id_order
     LEFT JOIN tiket t ON od.id_tiket = t.id_tiket
     LEFT JOIN event e ON t.id_event = e.id_event
     LEFT JOIN voucher v ON o.id_voucher = v.id_voucher
     $where2
     ORDER BY o.tanggal_order DESC
     LIMIT $limit OFFSET $offset2");

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tiket';
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
        <ul class="nav nav-tabs mb-3 d-print-none" id="laporanTab" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link <?= $active_tab == 'tiket' ? 'active' : '' ?>" id="tiket-tab" data-bs-toggle="tab" data-bs-target="#tiket" type="button" onclick="history.pushState(null, null, '?p=admin_laporan&tab=tiket')"><i class="bi bi-ticket"></i> Tiket Terjual per Event</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link <?= $active_tab == 'transaksi' ? 'active' : '' ?>" id="transaksi-tab" data-bs-toggle="tab" data-bs-target="#transaksi" type="button" onclick="history.pushState(null, null, '?p=admin_laporan&tab=transaksi')"><i class="bi bi-cart"></i> Data Transaksi</button></li>
        </ul>
        <div class="tab-content" id="laporanTabContent">
            <div class="tab-pane fade <?= $active_tab == 'tiket' ? 'show active' : '' ?>" id="tiket" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i> Tiket Terjual per Event</h5>
                        <form method="GET" action="index.php" class="d-flex d-print-none" style="max-width: 300px;">
                            <input type="hidden" name="p" value="admin_laporan">
                            <input type="hidden" name="tab" value="tiket">
                            <div class="input-group input-group-sm">
                                <input type="text" name="q1" class="form-control" placeholder="Cari event/venue..." value="<?= htmlspecialchars($q1) ?>">
                                <button class="btn btn-light border" type="submit"><i class="bi bi-search"></i></button>
                                <?php if($q1): ?>
                                    <a href="?p=admin_laporan&tab=tiket" class="btn btn-light border"><i class="bi bi-x"></i></a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                <div class="card-body"><div class="table-responsive"><table class="table table-striped table-hover align-middle" id="table-tiket">
                    <thead class="table-dark"><tr><th>No</th><th>Event</th><th>Tanggal</th><th>Venue</th><th class="text-center">Order</th><th class="text-center">Terjual</th><th>Pendapatan</th><th class="text-center d-print-none">Detail</th></tr></thead>
                    <tbody><?php $no = $offset1 + 1; while ($row = mysqli_fetch_assoc($tiket_terjual)): ?>
                    <tr><td><?= $no++ ?></td><td><span class="fw-bold"><?= htmlspecialchars($row['nama_event']) ?></span></td><td><?= date('d M Y', strtotime($row['tanggal'])) ?></td><td><small><?= htmlspecialchars($row['nama_venue']) ?></small></td><td class="text-center"><?= $row['total_order'] ?? 0 ?></td><td class="text-center"><span class="badge bg-info text-dark"><?= $row['total_tiket_terjual'] ?? 0 ?></span></td><td class="fw-bold text-success">Rp <?= number_format($row['total_pendapatan'] ?? 0, 0, ',', '.') ?></td><td class="text-center d-print-none"><a href="?p=admin_laporan&tab=transaksi&q2=<?= urlencode($row['nama_event']) ?>" class="btn btn-outline-primary btn-sm" title="Lihat semua transaksi event ini"><i class="bi bi-list-check"></i> Transaksi</a></td></tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($tiket_terjual) == 0): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Data tidak ditemukan.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table></div>
                
                <!-- Pagination Tab 1 -->
                <?php if($total_page1 > 1): ?>
                <nav class="mt-3 d-print-none">
                    <ul class="pagination pagination-sm justify-content-center">
                        <li class="page-item <?= ($p1 <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?p=admin_laporan&tab=tiket&q1=<?= urlencode($q1) ?>&p1=<?= $p1-1 ?>">Prev</a>
                        </li>
                        <?php for($i=1; $i<=$total_page1; $i++): ?>
                            <li class="page-item <?= ($i == $p1) ? 'active' : '' ?>">
                                <a class="page-link" href="?p=admin_laporan&tab=tiket&q1=<?= urlencode($q1) ?>&p1=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($p1 >= $total_page1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?p=admin_laporan&tab=tiket&q1=<?= urlencode($q1) ?>&p1=<?= $p1+1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                </div></div>
            </div>
            <div class="tab-pane fade <?= $active_tab == 'transaksi' ? 'show active' : '' ?>" id="transaksi" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-cart-check me-2"></i> Data Transaksi Lengkap</h5>
                        <form method="GET" action="index.php" class="d-flex d-print-none" style="max-width: 300px;">
                            <input type="hidden" name="p" value="admin_laporan">
                            <input type="hidden" name="tab" value="transaksi">
                            <div class="input-group input-group-sm">
                                <input type="text" name="q2" class="form-control" placeholder="ID/Nama/Email..." value="<?= htmlspecialchars($q2) ?>">
                                <button class="btn btn-light border" type="submit"><i class="bi bi-search"></i></button>
                                <?php if($q2): ?>
                                    <a href="?p=admin_laporan&tab=transaksi" class="btn btn-light border"><i class="bi bi-x"></i></a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                <div class="card-body"><div class="table-responsive"><table class="table table-striped table-hover align-middle" id="table-transaksi">
                    <thead class="table-dark"><tr><th>ID Order</th><th>User</th><th>Event</th><th>Tiket</th><th class="text-center">Qty</th><th>Subtotal</th><th>Voucher</th><th>Total</th><th>Status</th><th>Tanggal</th><th class="d-print-none">Aksi</th></tr></thead>
                    <tbody><?php while ($row = mysqli_fetch_assoc($transaksi)):
                        $badge_class = $row['status']=='paid' ? 'success' : ($row['status']=='pending' ? 'warning' : 'danger');
                    ?>
                    <tr>
                        <td><span class="badge bg-secondary">#<?= $row['id_order'] ?></span></td>
                        <td><div class="fw-bold"><?= htmlspecialchars($row['nama']) ?></div><small class="text-muted"><?= htmlspecialchars($row['email']) ?></small></td>
                        <td><small><?= htmlspecialchars($row['nama_event'] ?? '-') ?></small></td><td><span class="text-primary"><?= htmlspecialchars($row['nama_tiket'] ?? '-') ?></span></td>
                        <td class="text-center"><?= $row['qty'] ?? 0 ?></td><td><small>Rp <?= number_format($row['subtotal'] ?? 0, 0, ',', '.') ?></small></td>
                        <td><small class="text-muted"><?= $row['kode_voucher'] ? $row['kode_voucher'] : '-' ?></small></td>
                        <td class="fw-bold">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                        <td><span class="badge bg-<?= $badge_class ?>"><?= ucfirst($row['status']) ?></span></td>
                        <td><small><?= date('d/m/y H:i', strtotime($row['tanggal_order'])) ?></small></td>
                        <td class="d-print-none">
                            <button type="button" class="btn btn-info btn-sm text-white btn-view-order" data-id="<?= $row['id_order'] ?>" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($transaksi) == 0): ?>
                        <tr><td colspan="11" class="text-center py-4 text-muted">Data tidak ditemukan.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table></div>
                
                <!-- Pagination Tab 2 -->
                <?php if($total_page2 > 1): ?>
                <nav class="mt-3 d-print-none">
                    <ul class="pagination pagination-sm justify-content-center">
                        <li class="page-item <?= ($p2 <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?p=admin_laporan&tab=transaksi&q2=<?= urlencode($q2) ?>&p2=<?= $p2-1 ?>">Prev</a>
                        </li>
                        <?php for($i=1; $i<=$total_page2; $i++): ?>
                            <li class="page-item <?= ($i == $p2) ? 'active' : '' ?>">
                                <a class="page-link" href="?p=admin_laporan&tab=transaksi&q2=<?= urlencode($q2) ?>&p2=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($p2 >= $total_page2) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?p=admin_laporan&tab=transaksi&q2=<?= urlencode($q2) ?>&p2=<?= $p2+1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                </div></div>
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

// Handler untuk Modal Detail Order
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-view-order')) {
        const btn = e.target.closest('.btn-view-order');
        const id = btn.getAttribute('data-id');
        
        Swal.fire({
            title: 'Memuat Detail...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch(`pages/admin/ajax_order_detail.php?id=${id}`)
            .then(res => res.text())
            .then(html => {
                Swal.fire({
                    width: '600px',
                    html: html,
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rounded-4'
                    }
                });
            })
            .catch(err => {
                Swal.fire('Error', 'Gagal memuat data detail pesanan.', 'error');
            });
    }
});
</script>
