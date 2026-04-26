<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title"><i class="bi bi-speedometer2"></i> Dashboard Admin</h2>
                    <p class="text-muted mb-0">Kelola sistem dan pantau performa bisnis Anda</p>
                </div>
                <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-calendar3"></i> <?= date('d M Y') ?></span>
            </div>

            <!-- Welcome Banner -->
            <div class="alert alert-primary d-flex align-items-center mb-4" style="background: var(--g-primary); color: white; border: none; border-radius: 16px; box-shadow: var(--sh-sm);">
                <i class="bi bi-person-circle fs-1 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?>! 👋</h5>
                    <p class="mb-0">Kelola event, tiket, dan pantau transaksi dari dashboard ini.</p>
                </div>
            </div>

            <!-- Statistik Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-people me-2"></i>Total User</p>
                                    <h2 class="mb-0 fw-bold">
                                        <?php
                                        $total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'];
                                        echo $total_user;
                                        ?>
                                    </h2>
                                    <small class="opacity-75">Pengguna terdaftar</small>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-people fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-cart me-2"></i>Order Lunas</p>
                                    <h2 class="mb-0 fw-bold">
                                        <?php
                                        $total_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='paid'"))['total'];
                                        echo $total_order;
                                        ?>
                                    </h2>
                                    <small class="opacity-75">Pesanan sukses</small>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-cart fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-cash-stack me-2"></i>Pendapatan</p>
                                    <h4 class="mb-0 fw-bold">
                                        Rp <?= number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as total FROM orders WHERE status='paid'"))['total'] ?? 0, 0, ',', '.') ?>
                                    </h4>
                                    <small class="opacity-75">Total terkumpul</small>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-cash-stack fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card danger h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-text mb-1 opacity-75"><i class="bi bi-calendar-check me-2"></i>Event Aktif</p>
                                    <h2 class="mb-0 fw-bold">
                                        <?php
                                        $event_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM event WHERE tanggal >= CURDATE()"))['total'];
                                        echo $event_aktif;
                                        ?>
                                    </h2>
                                    <small class="opacity-75">Event berlangsung</small>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                    <i class="bi bi-calendar-check fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // Ambil data untuk grafik (5 Event Terbaru)
            $chart_query = mysqli_query($conn, "
                SELECT e.id_event, e.nama_event, e.gambar, SUM(od.qty) as total_tiket, SUM(od.subtotal) as total_pendapatan
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
                    <div class="card shadow-sm border-0 h-100" style="border-radius:16px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-bar-chart-line text-primary me-2"></i>Analisis Pendapatan</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius:16px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-lightning-charge text-warning me-2"></i>Aksi Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-3">
                                <a href="?p=admin_event_tambah" class="btn btn-primary text-start p-3 d-flex align-items-center justify-content-between" style="border-radius:12px; background:rgba(99,102,241,0.1); color:var(--c-primary); border:none;">
                                    <span><i class="bi bi-plus-circle-fill me-2"></i>Tambah Event</span>
                                    <i class="bi bi-chevron-right small"></i>
                                </a>
                                <a href="?p=admin_venue_tambah" class="btn btn-success text-start p-3 d-flex align-items-center justify-content-between" style="border-radius:12px; background:rgba(16,185,129,0.1); color:var(--c-success); border:none;">
                                    <span><i class="bi bi-building-add me-2"></i>Kelola Venue</span>
                                    <i class="bi bi-chevron-right small"></i>
                                </a>
                                <a href="?p=admin_voucher_tambah" class="btn btn-warning text-start p-3 d-flex align-items-center justify-content-between" style="border-radius:12px; background:rgba(245,158,11,0.1); color:var(--c-warning); border:none;">
                                    <span><i class="bi bi-tag-fill me-2"></i>Promo Voucher</span>
                                    <i class="bi bi-chevron-right small"></i>
                                </a>
                                <hr class="my-1">
                                <a href="?p=admin_laporan" class="btn btn-dark text-start p-3 d-flex align-items-center justify-content-between" style="border-radius:12px; border:none;">
                                    <span><i class="bi bi-file-earmark-pdf me-2"></i>Laporan Pesanan</span>
                                    <i class="bi bi-arrow-up-right small"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Section (Baris Baru) -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius:16px;">
                        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-trophy-fill text-warning me-2"></i>Top 5 Event Terlaris</h6>
                            <a href="?p=admin_laporan" class="text-decoration-none fw-bold" style="font-size:0.7rem;">Detail <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <?php if(count($leaderboard) > 0): ?>
                                    <?php foreach($leaderboard as $index => $e): 
                                        $rank = $index + 1;
                                        $badge_color = $rank == 1 ? 'gold' : ($rank == 2 ? 'silver' : ($rank == 3 ? '#cd7f32' : 'black'));
                                    ?>
                                    <div class="col-md">
                                        <div class="d-flex align-items-center p-2 rounded-3" style="background:#f1f5f9; border: 1px solid #e2e8f0;">
                                            <div class="position-relative me-2">
                                                <div style="width:36px; height:36px; border-radius:8px; overflow:hidden;">
                                                    <img src="<?= $e['gambar'] ? 'uploads/'.$e['gambar'] : 'https://ui-avatars.com/api/?name='.urlencode($e['nama_event']).'&background=random' ?>" style="width:100%; height:100%; object-fit:cover;">
                                                </div>
                                                <div class="position-absolute top-0 start-0 translate-middle badge rounded-circle p-1" style="background:<?= $badge_color ?>; width:18px; height:18px; font-size:0.6rem; border:2px solid white;">
                                                    <?= $rank ?>
                                                </div>
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-truncate" style="font-size:0.75rem; max-width: 100px;"><?= htmlspecialchars($e['nama_event']) ?></div>
                                                <small class="text-success fw-bold" style="font-size:0.7rem;"><?= $e['total_tiket'] ?> <span class="text-muted fw-normal">Tiket</span></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12 text-center py-3 text-muted" style="font-size:0.8rem;">Belum ada data penjualan.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laporan Transaksi Terbaru -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Transaksi Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID Order</th>
                                    <th>User</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Menampilkan hanya transaksi lunas terbaru
                                $res = mysqli_query($conn, "SELECT orders.*, users.nama FROM orders JOIN users ON orders.id_user = users.id_user WHERE status='paid' ORDER BY tanggal_order DESC LIMIT 5");
                                while ($row = mysqli_fetch_assoc($res)):
                                    $badge_class = 'success';
                                ?>
                                    <tr>
                                        <td>#<?= $row['id_order'] ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= date('d M Y H:i', strtotime($row['tanggal_order'])) ?></td>
                                        <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                        <td><span class="badge bg-<?= $badge_class ?>"><?= ucfirst($row['status']) ?></span></td>
                                        <td><a href="?p=admin_order_detail&id=<?= $row['id_order'] ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
                backgroundColor: 'rgba(99, 102, 241, 0.85)',
                borderColor: 'rgba(99, 102, 241, 1)',
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
