<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold"><i class="bi bi-speedometer2"></i> Dashboard Admin</h2>
                    <p class="text-muted mb-0">Kelola sistem dan pantau performa bisnis Anda</p>
                </div>
                <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-calendar3"></i> <?= date('d M Y') ?></span>
            </div>

            <!-- Welcome Banner -->
            <div class="alert bg-primary text-white d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm">
                <i class="bi bi-person-circle fs-1 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?>! 👋</h5>
                    <p class="mb-0">Kelola event, tiket, dan pantau transaksi dari dashboard ini.</p>
                </div>
            </div>

            <!-- Statistik Ringkasan (Gaya Laporan Premium) -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Total Customer</span>
                                <i class="bi bi-people fs-4 opacity-75"></i>
                            </div>
                            <h3 class="fw-bold mb-1">
                                <?php echo number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'], 0, ',', '.'); ?>
                            </h3>
                            <p class="mb-0 small opacity-75">Pengguna terdaftar</p>
                        </div>
                        <i class="bi bi-people position-absolute end-0 bottom-0 opacity-25 z-0 display-1" style="transform: translate(5%, 5%);"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-success text-white overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Order Lunas</span>
                                <i class="bi bi-cart-check fs-4 opacity-75"></i>
                            </div>
                            <h3 class="fw-bold mb-1">
                                <?php echo number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='paid'"))['total'], 0, ',', '.'); ?>
                            </h3>
                            <p class="mb-0 small opacity-75">Pesanan berhasil</p>
                        </div>
                        <i class="bi bi-cart-check position-absolute end-0 bottom-0 opacity-25 z-0 display-1" style="transform: translate(5%, 5%);"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-warning text-white overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Revenue</span>
                                <i class="bi bi-cash-stack fs-4 opacity-75"></i>
                            </div>
                            <h3 class="fw-bold mb-1" style="font-size: 1.5rem;">
                                Rp<?= number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as total FROM orders WHERE status='paid'"))['total'] ?? 0, 0, ',', '.') ?>
                            </h3>
                            <p class="mb-0 small opacity-75">Total pendapatan</p>
                        </div>
                        <i class="bi bi-cash-stack position-absolute end-0 bottom-0 opacity-25 z-0 display-1" style="transform: translate(5%, 5%);"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-danger text-white overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3">Event Aktif</span>
                                <i class="bi bi-calendar-event fs-4 opacity-75"></i>
                            </div>
                            <h3 class="fw-bold mb-1">
                                <?php echo number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM event WHERE tanggal >= CURDATE()"))['total'], 0, ',', '.'); ?>
                            </h3>
                            <p class="mb-0 small opacity-75">Event berlangsung</p>
                        </div>
                        <i class="bi bi-calendar-event position-absolute end-0 bottom-0 opacity-25 z-0 display-1" style="transform: translate(5%, 5%);"></i>
                    </div>
                </div>
            </div>

            <?php
            // Ambil data untuk grafik (5 Event Terbaru) - Query diperbaiki untuk ambil kuota juga
            $chart_query = mysqli_query($conn, "
                SELECT e.id_event, e.nama_event, e.gambar, 
                       (SELECT SUM(kuota) FROM tiket WHERE id_event = e.id_event) as total_kuota,
                       SUM(od.qty) as total_tiket, SUM(od.subtotal) as total_pendapatan
                FROM event e
                JOIN tiket t ON e.id_event = t.id_event
                JOIN order_detail od ON t.id_tiket = od.id_tiket
                JOIN orders o ON od.id_order = o.id_order
                WHERE o.status = 'paid'
                GROUP BY e.id_event
                ORDER BY total_tiket DESC LIMIT 5
            ");
            $leaderboard = [];
            $labels = []; $data_pendapatan = [];
            while ($row = mysqli_fetch_assoc($chart_query)) {
                $leaderboard[] = $row;
                $labels[] = strlen($row['nama_event']) > 15 ? substr($row['nama_event'],0,15).'...' : $row['nama_event'];
                $data_pendapatan[] = (int)$row['total_pendapatan'];
            }
            ?>

            <!-- Charts & Quick Actions -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-3 px-4">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Trend Pendapatan</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <canvas id="revenueChart" height="220"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 h-100 rounded-4">
                        <div class="card-header bg-white border-0 py-3 px-4">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Navigasi Cepat</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-grid gap-2">
                                <a href="?p=admin_event_tambah" class="btn btn-light text-start p-3 border-0 rounded-4 shadow-sm hover-translate-x transition-all">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-3 p-2 me-3"><i class="bi bi-calendar-plus"></i></div>
                                            <span class="fw-bold small">Tambah Event Baru</span>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted small"></i>
                                    </div>
                                </a>
                                <a href="?p=admin_order_list" class="btn btn-light text-start p-3 border-0 rounded-4 shadow-sm hover-translate-x transition-all">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success text-white rounded-3 p-2 me-3"><i class="bi bi-receipt"></i></div>
                                            <span class="fw-bold small">Kelola Transaksi</span>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted small"></i>
                                    </div>
                                </a>
                                <a href="?p=admin_laporan" class="btn btn-light text-start p-3 border-0 rounded-4 shadow-sm hover-translate-x transition-all">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-dark text-white rounded-3 p-2 me-3"><i class="bi bi-file-earmark-bar-graph"></i></div>
                                            <span class="fw-bold small">Laporan Penjualan</span>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted small"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 5 Events & Recent Transactions -->
            <div class="row g-4 mb-4">
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-award-fill text-warning me-2"></i>Top 5 Event</h5>
                            <span class="badge bg-light text-dark rounded-pill">Bulan Ini</span>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <div class="vstack gap-4 mt-3">
                                <?php if(count($leaderboard) > 0): ?>
                                    <?php foreach($leaderboard as $index => $e): 
                                        $percent = ($e['total_kuota'] > 0) ? min(100, round(($e['total_tiket'] / $e['total_kuota']) * 100)) : 0;
                                        $prog_color = $percent > 80 ? 'danger' : ($percent > 50 ? 'warning' : 'primary');
                                    ?>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold text-dark me-2">#<?= $index+1 ?></div>
                                                <div class="text-truncate" style="max-width: 180px;"><span class="small fw-semibold"><?= htmlspecialchars($e['nama_event']) ?></span></div>
                                            </div>
                                            <div class="small fw-bold text-<?= $prog_color ?>"><?= $e['total_tiket'] ?> <span class="text-muted fw-normal">Tiket</span></div>
                                        </div>
                                        <div class="progress rounded-pill" style="height: 8px;">
                                            <div class="progress-bar bg-<?= $prog_color ?> rounded-pill" role="progressbar" style="width: <?= $percent ?>%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted" style="font-size: 0.65rem;">Terjual: <?= $percent ?>%</small>
                                            <small class="text-muted" style="font-size: 0.65rem;">Kuota: <?= $e['total_kuota'] ?></small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox text-muted display-4"></i>
                                        <p class="text-muted small mt-2">Belum ada data penjualan.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                        <div class="card-header bg-primary text-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Transaksi Lunas Terbaru</h5>
                            <a href="?p=admin_order_list" class="btn btn-sm btn-light rounded-pill px-3 fw-bold small">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 px-4 py-3 small text-uppercase ls-1">Customer</th>
                                            <th class="border-0 px-4 py-3 small text-uppercase ls-1">Waktu</th>
                                            <th class="border-0 px-4 py-3 small text-uppercase ls-1">Total</th>
                                            <th class="border-0 px-4 py-3 small text-uppercase ls-1 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $res = mysqli_query($conn, "SELECT orders.*, users.nama FROM orders JOIN users ON orders.id_user = users.id_user WHERE status='paid' ORDER BY tanggal_order DESC LIMIT 6");
                                        if(mysqli_num_rows($res) > 0):
                                        while ($row = mysqli_fetch_assoc($res)):
                                        ?>
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <div class="fw-bold text-dark small"><?= htmlspecialchars($row['nama']) ?></div>
                                                    <div class="text-muted" style="font-size: 0.7rem;">ID: #<?= $row['id_order'] ?></div>
                                                </td>
                                                <td class="px-4 py-3 small text-muted">
                                                    <?= date('d M, H:i', strtotime($row['tanggal_order'])) ?>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="fw-bold text-success small">Rp<?= number_format($row['total'], 0, ',', '.') ?></div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <a href="?p=admin_order_detail&id=<?= $row['id_order'] ?>" class="btn btn-sm btn-primary rounded-circle" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; else: ?>
                                            <tr><td colspan="4" class="text-center py-5 text-muted small">Belum ada transaksi lunas.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
    .ls-1 { letter-spacing: 1px; }
    .hover-translate-x:hover { transform: translateX(5px); }
    .transition-all { transition: all 0.3s ease; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
</style>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = <?= json_encode($labels) ?>;
    const dataPendapatan = <?= json_encode($data_pendapatan) ?>;

    // Revenue Chart (Bar)
    const ctxRev = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRev, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: dataPendapatan,
                backgroundColor: 'rgba(13, 110, 253, 0.85)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });

    // Best Selling Events Chart (Removed as we use Leaderboard HTML now)
</script>
