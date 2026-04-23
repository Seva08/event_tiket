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

<style>
    .event-banner {
        height: 450px;
        position: relative;
        background-size: cover;
        background-position: center;
        border-radius: 30px;
        margin-top: 20px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .event-banner::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0; top: 0;
        background: linear-gradient(to top, rgba(15, 23, 42, 0.95) 0%, rgba(15, 23, 42, 0.4) 50%, rgba(15, 23, 42, 0) 100%);
    }
    .event-banner:hover {
        box-shadow: 0 30px 60px rgba(0,0,0,0.3);
    }
    .banner-content {
        position: absolute;
        bottom: 40px;
        left: 40px;
        right: 40px;
        z-index: 2;
        color: white;
    }
    .info-box {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #f1f5f9;
        height: 100%;
    }
    .ticket-option {
        border: 1.5px solid #f1f5f9;
        border-radius: 20px;
        padding: 25px;
        transition: all 0.3s;
        background: white;
        position: relative;
    }
    .ticket-option:hover {
        border-color: #6366f1;
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(99,102,241,0.1);
    }
    .sticky-info {
        position: sticky;
        top: 100px;
    }
</style>

<div class="container pb-5">
    <!-- Banner Section -->
    <div class="event-banner img-hd-container" style="background-image: url('<?= $event['gambar'] ? "uploads/".$event['gambar'] : "https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&w=1200&q=80" ?>'); transition: all 0.5s ease; filter: contrast(1.02) brightness(1.05);">
        <div class="banner-content" style="border-radius: 20px; padding: 20px;">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-warning text-dark px-3 py-2" style="border-radius: 50px;">
                    <i class="bi bi-star-fill me-1"></i> Rekomendasi
                </span>
                <?php if($is_passed): ?>
                <span class="badge bg-danger px-3 py-2" style="border-radius: 50px;">
                    <i class="bi bi-calendar-x me-1"></i> Event Selesai
                </span>
                <?php endif; ?>
            </div>
            <h1 class="display-4 fw-bold mb-2"><?= htmlspecialchars($event['nama_event']) ?></h1>
            <div class="d-flex flex-wrap gap-4 opacity-75">
                <span><i class="bi bi-calendar3 me-2"></i><?= $tanggal ?></span>
                <span><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($event['nama_venue']) ?></span>
                <span><i class="bi bi-people me-2"></i><?= number_format($event['kapasitas']) ?> Kapasitas</span>
            </div>
        </div>
    </div>

    <div class="row mt-5 g-4">
        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="sticky-info">
                <div class="info-box mb-4">
                    <h5 class="fw-bold mb-4">Lokasi & Waktu</h5>
                    <div class="d-flex gap-3 mb-4">
                        <div class="flex-shrink-0" style="width: 45px; height: 45px; background: #eef2ff; border-radius: 12px; display:flex; align-items:center; justify-content:center;">
                            <i class="bi bi-calendar-check text-primary fs-5"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold">WAKTU</small>
                            <span class="fw-bold text-dark"><?= $tanggal ?></span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-4">
                        <div class="flex-shrink-0" style="width: 45px; height: 45px; background: #fff1f2; border-radius: 12px; display:flex; align-items:center; justify-content:center;">
                            <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fw-bold">TEMPAT</small>
                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($event['nama_venue']) ?></span>
                            <small class="text-muted"><?= htmlspecialchars($event['alamat']) ?></small>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-4">
                        <small class="text-muted d-block mb-1">HARGA MULAI DARI</small>
                        <h3 class="fw-bold text-primary mb-0">Rp <?= number_format($min_price, 0, ',', '.') ?></h3>
                    </div>
                </div>

                <div class="info-box bg-dark text-white p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-shield-check me-2 text-success"></i>Beli dengan Aman</h6>
                    <p class="small opacity-75 mb-0">Tiket resmi 100% aman dan terjamin. Dapatkan e-tiket langsung setelah konfirmasi pembayaran.</p>
                </div>
            </div>
        </div>

        <!-- Ticket Section -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Tiket Tersedia</h4>
                <span class="text-muted small">Silakan pilih kategori tiket kamu</span>
            </div>

            <?php if (mysqli_num_rows($tiket) > 0): ?>
                <div class="row g-3">
                    <?php while ($t = mysqli_fetch_assoc($tiket)): 
                        $habis = $t['kuota'] <= 0;
                    ?>
                    <div class="col-12">
                        <div class="ticket-option <?= $habis || $is_passed ? 'opacity-75' : '' ?>">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($t['nama_tiket']) ?></h5>
                                        <?php if($habis): ?>
                                            <span class="badge bg-danger">Sold Out</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small mb-3">Akses masuk sesuai kategori, fasilitas standar sesuai venue.</div>
                                    <div class="d-flex gap-3 small">
                                        <span class="text-success fw-bold"><i class="bi bi-check2-all me-1"></i>Konfirmasi Instan</span>
                                        <span class="text-muted"><i class="bi bi-ticket-perforated me-1"></i>Sisa: <?= $t['kuota'] ?></span>
                                    </div>
                                </div>
                                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Harga per tiket</small>
                                        <h4 class="fw-bold text-primary mb-0">Rp <?= number_format($t['harga'], 0, ',', '.') ?></h4>
                                    </div>
                                    
                                    <?php if ($is_passed): ?>
                                        <button class="btn btn-secondary w-100 disabled" style="border-radius: 50px;">Event Selesai</button>
                                    <?php elseif ($habis): ?>
                                        <button class="btn btn-danger w-100 disabled" style="border-radius: 50px;">Tiket Habis</button>
                                    <?php elseif (isset($_SESSION['id_user']) && $_SESSION['role'] === 'user'): ?>
                                        <a href="?p=tiket_pesan&id=<?= $t['id_tiket'] ?>" class="btn btn-primary w-100 fw-bold" style="border-radius: 50px; padding: 12px;">Pesan Tiket</a>
                                    <?php elseif (!isset($_SESSION['id_user'])): ?>
                                        <a href="?p=login" class="btn btn-outline-primary w-100" style="border-radius: 50px;">Login untuk Pesan</a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100 disabled" style="border-radius: 50px;">Hanya untuk User</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 info-box">
                    <i class="bi bi-ticket-detailed text-muted display-4 mb-3"></i>
                    <h5>Tiket Belum Tersedia</h5>
                    <p class="text-muted">Pantau terus halaman ini untuk update tiket terbaru.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
