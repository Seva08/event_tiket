<?php
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    echo "<script>alert('Akses ditolak!'); window.location='?p=login';</script>";
    exit;
}

$user_id       = $_SESSION['id_user'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $user_id"))['c'];
$pending       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $user_id AND status = 'pending'"))['c'];
$sudah_bayar   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $user_id AND status = 'paid'"))['c'];
$total_tiket   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN orders o ON od.id_order = o.id_order WHERE o.id_user = $user_id AND o.status = 'paid'"))['c'];
$events        = mysqli_query($conn, "SELECT e.*, v.nama_venue, v.alamat FROM event e JOIN venue v ON e.id_venue = v.id_venue ORDER BY e.tanggal DESC LIMIT 6");
?>

<div class="container py-4">
    <!-- Header Greeting -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item small"><a href="?p=home" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item small active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
        </div>
        <div class="bg-white px-3 py-2 rounded-pill shadow-sm border small fw-bold text-primary">
            <i class="bi bi-clock-history me-2"></i>Sesi Aktif: <?= date('H:i') ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Profile -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body p-4 text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-person-fill display-6"></i>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-success border border-white border-3 rounded-circle p-2"></span>
                    </div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($_SESSION['email'] ?? 'Member YuiPass') ?></p>
                    <div class="d-grid gap-2">
                        <a href="?p=profile" class="btn btn-light btn-sm rounded-pill fw-bold py-2 shadow-sm border-0">
                            <i class="bi bi-gear me-2"></i>Edit Profil
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 p-0">
                    <div class="list-group list-group-flush small">
                        <a href="?p=riwayat" class="list-group-item list-group-item-action py-3 border-0 bg-transparent">
                            <i class="bi bi-receipt me-3 text-primary"></i>Riwayat Pesanan
                        </a>
                        <a href="?p=logout" class="list-group-item list-group-item-action py-3 border-0 bg-transparent text-danger">
                            <i class="bi bi-box-arrow-right me-3"></i>Keluar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card border-0 bg-dark text-white rounded-4 shadow-sm overflow-hidden">
                <div class="card-body p-4 position-relative z-1">
                    <h6 class="fw-bold mb-2">Butuh Bantuan?</h6>
                    <p class="small opacity-75 mb-3">Hubungi tim support kami jika Anda mengalami kendala transaksi.</p>
                    <a href="#" class="btn btn-primary btn-sm rounded-pill px-3">WhatsApp Kami</a>
                </div>
                <i class="bi bi-headset position-absolute end-0 bottom-0 opacity-25 z-0 display-4 m-2"></i>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Welcome Banner -->
            <div class="card border-0 bg-primary text-white rounded-4 shadow-sm mb-4 position-relative overflow-hidden">
                <div class="card-body p-4 p-md-5 position-relative z-1">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-2 text-white">Selamat Datang, <?= htmlspecialchars(explode(' ', $_SESSION['nama'])[0]) ?>! ✨</h2>
                        <p class="mb-4 opacity-75">Senang melihatmu kembali. Semua tiket dan riwayat pesananmu tersimpan rapi di sini.</p>
                        <a href="?p=home" class="btn btn-light rounded-pill px-4 fw-bold">Cari Event Seru</a>
                    </div>
                </div>
                <i class="bi bi-stars position-absolute end-0 top-0 display-1 opacity-25 m-4"></i>
            </div>

            <!-- Statistics Premium -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white overflow-hidden position-relative transition-transform hover-scale">
                        <div class="card-body p-3 position-relative z-1 text-center">
                            <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 mb-2 small fw-bold">Total Order</span>
                            <h3 class="fw-bold mb-0 display-6"><?= $total_pesanan ?></h3>
                        </div>
                        <i class="bi bi-receipt position-absolute end-0 bottom-0 opacity-25 z-0 display-4 m-1" style="transform: translate(10%, 10%);"></i>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-warning text-white overflow-hidden position-relative transition-transform hover-scale">
                        <div class="card-body p-3 position-relative z-1 text-center">
                            <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 mb-2 small fw-bold">Pending</span>
                            <h3 class="fw-bold mb-0 display-6"><?= $pending ?></h3>
                        </div>
                        <i class="bi bi-clock position-absolute end-0 bottom-0 opacity-25 z-0 display-4 m-1" style="transform: translate(10%, 10%);"></i>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-success text-white overflow-hidden position-relative transition-transform hover-scale">
                        <div class="card-body p-3 position-relative z-1 text-center">
                            <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 mb-2 small fw-bold">Lunas</span>
                            <h3 class="fw-bold mb-0 display-6"><?= $sudah_bayar ?></h3>
                        </div>
                        <i class="bi bi-check-circle position-absolute end-0 bottom-0 opacity-25 z-0 display-4 m-1" style="transform: translate(10%, 10%);"></i>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-info text-white overflow-hidden position-relative transition-transform hover-scale">
                        <div class="card-body p-3 position-relative z-1 text-center">
                            <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 mb-2 small fw-bold">Tiket Aktif</span>
                            <h3 class="fw-bold mb-0 display-6"><?= $total_tiket ?></h3>
                        </div>
                        <i class="bi bi-ticket-perforated position-absolute end-0 bottom-0 opacity-25 z-0 display-4 m-1" style="transform: translate(10%, 10%);"></i>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Transaksi Terbaru</h5>
                    <a href="?p=riwayat" class="btn btn-sm btn-link text-decoration-none fw-bold small">Lihat Riwayat</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="small text-muted text-uppercase ls-1">
                                    <th class="px-4 py-3 border-0">ID Order</th>
                                    <th class="px-4 py-3 border-0">Tgl Pesan</th>
                                    <th class="px-4 py-3 border-0 text-center">Status</th>
                                    <th class="px-4 py-3 border-0 text-end">Total Bayar</th>
                                    <th class="px-4 py-3 border-0 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recent_orders = mysqli_query($conn, "SELECT * FROM orders WHERE id_user = $user_id ORDER BY tanggal_order DESC LIMIT 3");
                                if (mysqli_num_rows($recent_orders) > 0):
                                    while($ro = mysqli_fetch_assoc($recent_orders)):
                                        $s_color = ($ro['status'] == 'paid' ? 'success' : ($ro['status'] == 'pending' ? 'warning' : 'danger'));
                                ?>
                                <tr>
                                    <td class="px-4 py-3 fw-bold small">#<?= $ro['id_order'] ?></td>
                                    <td class="px-4 py-3 small"><?= date('d M Y', strtotime($ro['tanggal_order'])) ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge bg-<?= $s_color ?>-subtle text-<?= $s_color ?> rounded-pill px-3 py-1 small fw-bold">
                                            <?= strtoupper($ro['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-end fw-bold small text-primary">Rp<?= number_format($ro['total'], 0, ',', '.') ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($ro['status'] == 'pending'): ?>
                                            <a href="?p=order_bayar&id=<?= $ro['id_order'] ?>" class="btn btn-sm btn-primary rounded-pill px-3 small fw-bold">Bayar</a>
                                        <?php else: ?>
                                            <a href="?p=riwayat&detail=<?= $ro['id_order'] ?>" class="btn btn-sm btn-light rounded-pill px-3 small fw-bold shadow-sm border">Detail</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">Belum ada transaksi terbaru.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Suggested Events -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-fire text-warning me-2"></i>Event Populer</h5>
                    <a href="?p=home" class="btn btn-sm btn-light rounded-pill px-3 fw-bold border shadow-sm small">Jelajahi Semua</a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <?php 
                        mysqli_data_seek($events, 0); // Reset result pointer
                        while ($d = mysqli_fetch_assoc($events)):
                            $is_passed = strtotime($d['tanggal']) < strtotime('today');
                            if($is_passed) continue; // Only show active ones in suggestions
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-translate-y transition-all">
                                <div class="ratio ratio-16x9">
                                    <img src="uploads/<?= $d['gambar'] ?>" class="object-fit-cover" alt="<?= $d['nama_event'] ?>">
                                </div>
                                <div class="card-body p-3">
                                    <div class="badge bg-primary-subtle text-primary mb-2 rounded-pill small fw-bold">
                                        <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($d['tanggal'])) ?>
                                    </div>
                                    <h6 class="fw-bold text-dark text-truncate mb-2"><?= htmlspecialchars($d['nama_event']) ?></h6>
                                    <p class="text-muted small text-truncate-2 mb-3" style="font-size: 0.75rem;">
                                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($d['nama_venue']) ?>
                                    </p>
                                    <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-primary w-100 rounded-pill small fw-bold py-2 shadow-sm">Lihat Tiket</a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ls-1 { letter-spacing: 1px; }
.hover-scale:hover { transform: translateY(-5px) scale(1.02); z-index: 10; }
.hover-translate-y:hover { transform: translateY(-5px); }
.transition-all { transition: all 0.3s ease; }
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
