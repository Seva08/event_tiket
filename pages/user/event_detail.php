<?php
if (!isset($_GET['id'])) { header("Location: ?p=home"); exit; }
$id_event = (int)$_GET['id'];
$event = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT e.*, v.nama_venue, v.alamat, v.kapasitas
     FROM event e JOIN venue v ON e.id_venue = v.id_venue
     WHERE e.id_event = $id_event"));
if (!$event) {
    echo "<script>alert('Event tidak ditemukan!'); window.location='?p=home';</script>"; exit;
}
$tiket   = mysqli_query($conn,
    "SELECT t.*, (SELECT COALESCE(SUM(od.qty), 0) FROM order_detail od
       JOIN orders o ON od.id_order = o.id_order
       WHERE od.id_tiket = t.id_tiket AND o.status != 'cancel') as terjual
     FROM tiket t WHERE t.id_event = $id_event");
$tanggal   = date('d M Y', strtotime($event['tanggal']));
$hari      = date('d', strtotime($event['tanggal']));
$bulan     = date('M Y', strtotime($event['tanggal']));
$is_passed = strtotime($event['tanggal']) < strtotime('today');

// Get min price
$q_min = mysqli_query($conn, "SELECT MIN(harga) as min_harga FROM tiket WHERE id_event = $id_event");
$min_price = mysqli_fetch_assoc($q_min)['min_harga'] ?? 0;
?>

<div class="container pb-5">
    <!-- Banner Section -->
    <div class="position-relative rounded-4 overflow-hidden mt-4 shadow-sm" style="min-height: 300px;">
        <div class="ratio ratio-21x9 ratio-sm-16x9">
            <img src="<?= $event['gambar'] ? "uploads/".$event['gambar'] : "https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&w=1200&q=80" ?>"
                 class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($event['nama_event']) ?>">
        </div>
        <!-- Gradient Overlay for better readability -->
        <div class="position-absolute top-0 start-0 end-0 bottom-0" style="background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 60%, rgba(0,0,0,0) 100%);"></div>
        
        <!-- Content Overlay -->
        <div class="position-absolute bottom-0 start-0 end-0 p-4 p-md-5 z-1 text-white">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-star-fill me-1"></i> Rekomendasi
                </span>
                <?php if($is_passed): ?>
                <span class="badge bg-danger px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-calendar-x me-1"></i> Event Selesai
                </span>
                <?php endif; ?>
            </div>
            <h1 class="display-5 fw-bold mb-3 lh-sm text-shadow"><?= htmlspecialchars($event['nama_event']) ?></h1>
            <div class="d-flex flex-wrap gap-x-4 gap-y-2 opacity-100 small">
                <span class="d-flex align-items-center gap-2"><i class="bi bi-calendar3 text-warning"></i><?= $tanggal ?></span>
                <span class="d-flex align-items-center gap-2"><i class="bi bi-geo-alt text-danger"></i><?= htmlspecialchars($event['nama_venue']) ?></span>
                <span class="d-flex align-items-center gap-2"><i class="bi bi-people text-info"></i><?= number_format($event['kapasitas']) ?> Kapasitas</span>
            </div>
        </div>
    </div>

    <div class="row mt-4 mt-md-5 g-4">
        <!-- Sidebar Info -->
        <div class="col-lg-4 order-lg-2">
            <div class="sticky-lg-top" style="top: 2rem;">
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Lokasi & Waktu</h5>
                        <div class="d-flex gap-3 mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                <i class="bi bi-calendar-check text-primary fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.7rem;">Waktu Pelaksanaan</small>
                                <span class="fw-bold text-dark"><?= $tanggal ?></span>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-4">
                            <div class="bg-danger bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.7rem;">Lokasi Venue</small>
                                <span class="fw-bold text-dark d-block"><?= htmlspecialchars($event['nama_venue']) ?></span>
                                <small class="text-muted small"><?= htmlspecialchars($event['alamat']) ?></small>
                            </div>
                        </div>
                        <hr class="opacity-10">
                        <div class="mt-4 p-3 bg-light rounded-3">
                            <small class="text-muted d-block mb-1 fw-bold text-uppercase" style="font-size: 0.7rem;">Harga Mulai Dari</small>
                            <h3 class="fw-bold text-primary mb-0">Rp <?= number_format($min_price, 0, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>

                <div class="card border-0 bg-dark text-white rounded-4 shadow-sm overflow-hidden mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-success fs-5"></i>
                            Beli dengan Aman
                        </h6>
                        <p class="small opacity-75 mb-0 lh-base">Tiket resmi 100% aman dan terjamin. Dapatkan e-tiket langsung setelah konfirmasi pembayaran.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Section -->
        <div class="col-lg-8 order-lg-1">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0"><i class="bi bi-ticket-perforated me-2 text-primary"></i>Tiket Tersedia</h4>
                <span class="text-muted small d-none d-sm-inline">Pilih kategori tiket kamu</span>
            </div>

            <?php if (mysqli_num_rows($tiket) > 0): ?>
                <div class="d-flex flex-column gap-3">
                    <?php while ($t = mysqli_fetch_assoc($tiket)): 
                        $habis = $t['kuota'] <= 0;
                    ?>
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden <?= $habis || $is_passed ? 'opacity-75' : '' ?>">
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md-7">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($t['nama_tiket']) ?></h5>
                                        <?php if($habis): ?>
                                            <span class="badge bg-danger rounded-pill">Sold Out</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted small mb-3 lh-base">Akses masuk sesuai kategori, fasilitas standar sesuai venue dengan pelayanan terbaik kami.</p>
                                    <div class="d-flex flex-wrap gap-3 small">
                                        <span class="text-success fw-bold d-flex align-items-center gap-1"><i class="bi bi-patch-check-fill"></i> Konfirmasi Instan</span>
                                        <span class="text-muted d-flex align-items-center gap-1"><i class="bi bi-ticket-detailed"></i> Sisa: <?= $t['kuota'] ?></span>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="bg-light p-3 rounded-4 text-md-end">
                                        <div class="mb-3">
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Harga Satuan</small>
                                            <h4 class="fw-bold text-primary mb-0">Rp <?= number_format($t['harga'], 0, ',', '.') ?></h4>
                                        </div>
                                        
                                        <?php if ($is_passed): ?>
                                            <button class="btn btn-secondary w-100 disabled rounded-pill py-2">Event Selesai</button>
                                        <?php elseif ($habis): ?>
                                            <button class="btn btn-danger w-100 disabled rounded-pill py-2">Tiket Habis</button>
                                        <?php elseif (isset($_SESSION['id_user']) && $_SESSION['role'] === 'user'): ?>
                                            <a href="?p=tiket_pesan&id=<?= $t['id_tiket'] ?>" class="btn btn-primary w-100 fw-bold rounded-pill py-2 shadow-sm">Pesan Tiket</a>
                                        <?php elseif (!isset($_SESSION['id_user'])): ?>
                                            <a href="?p=login" class="btn btn-outline-primary w-100 rounded-pill py-2 fw-bold">Login untuk Pesan</a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100 disabled rounded-pill py-2">Hanya untuk User</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-white shadow-sm rounded-4">
                    <i class="bi bi-ticket-detailed text-muted display-4 mb-3 d-block"></i>
                    <h5 class="fw-bold">Tiket Belum Tersedia</h5>
                    <p class="text-muted">Pantau terus halaman ini untuk update tiket terbaru.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
