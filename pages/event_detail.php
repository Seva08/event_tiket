<?php
if (!isset($_GET['id'])) {
    header("Location: ?p=home");
    exit;
}

$id_event = (int)$_GET['id'];

// Query detail event
$event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT e.*, v.nama_venue, v.alamat, v.kapasitas 
    FROM event e 
    JOIN venue v ON e.id_venue = v.id_venue 
    WHERE e.id_event = $id_event"));

if (!$event) {
    echo "<script>alert('Event tidak ditemukan!'); window.location='?p=home';</script>";
    exit;
}

// Query tiket untuk event ini
$tiket = mysqli_query($conn, "SELECT t.*, 
    (SELECT COUNT(*) FROM order_detail od 
     JOIN orders o ON od.id_order = o.id_order 
     WHERE od.id_tiket = t.id_tiket AND o.status = 'paid') as terjual
    FROM tiket t 
    WHERE t.id_event = $id_event");

$tanggal = date('d M Y', strtotime($event['tanggal']));
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=home">Home</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($event['nama_event']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Event Info -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header text-white py-3" style="background: var(--primary-gradient);">
                    <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Info Event</h5>
                </div>
                <div class="card-body">
                    <h4 class="fw-bold mb-3"><?= htmlspecialchars($event['nama_event']) ?></h4>
                    
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-calendar3 text-primary"></i>
                            <strong>Tanggal:</strong><br>
                            <span class="ms-4"><?= $tanggal ?></span>
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-geo-alt text-danger"></i>
                            <strong>Lokasi:</strong><br>
                            <span class="ms-4"><?= htmlspecialchars($event['nama_venue']) ?></span><br>
                            <small class="ms-4 text-muted"><?= htmlspecialchars($event['alamat']) ?></small>
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-people text-success"></i>
                            <strong>Kapasitas:</strong><br>
                            <span class="ms-4"><?= number_format($event['kapasitas']) ?> orang</span>
                        </li>
                    </ul>
                </div>
            </div>

            <?php if (isset($_SESSION['id_user'])): ?>
                <a href="?p=riwayat" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-clock-history"></i> Riwayat Pembelian
                </a>
            <?php else: ?>
                <a href="?p=login" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Login untuk Pesan
                </a>
            <?php endif; ?>
        </div>

        <!-- Tiket List -->
        <div class="col-md-8">
            <h4 class="mb-4"><i class="bi bi-ticket"></i> Pilih Tiket</h4>

            <?php if (mysqli_num_rows($tiket) > 0): ?>
                <div class="row">
                    <?php while ($t = mysqli_fetch_assoc($tiket)): 
                        $sisa = $t['kuota'] - $t['terjual'];
                        $habis = $sisa <= 0;
                    ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 <?= $habis ? 'opacity-50' : '' ?>">
                                <div class="card-header d-flex justify-content-between align-items-center" style="background: var(--primary-gradient); color: white;">
                                    <span class="fw-bold"><?= htmlspecialchars($t['nama_tiket']) ?></span>
                                    <?php if ($habis): ?>
                                        <span class="badge bg-danger">HABIS</span>
                                    <?php elseif ($sisa <= 10): ?>
                                        <span class="badge bg-warning text-dark">SISA <?= $sisa ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success">TERSEDIA</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h3 class="text-primary fw-bold mb-3">
                                        Rp <?= number_format($t['harga'], 0, ',', '.') ?>
                                    </h3>
                                    
                                    <div class="d-flex justify-content-between text-muted mb-3">
                                        <small><i class="bi bi-ticket"></i> Kuota: <?= $t['kuota'] ?></small>
                                        <small><i class="bi bi-cart"></i> Terjual: <?= $t['terjual'] ?></small>
                                    </div>

                                    <div class="progress mb-3" style="height: 6px;">
                                        <div class="progress-bar <?= $habis ? 'bg-danger' : 'bg-success' ?>" 
                                             style="width: <?= min(100, ($t['terjual'] / $t['kuota']) * 100) ?>%"></div>
                                    </div>

                                    <?php if (!$habis && isset($_SESSION['id_user'])): ?>
                                        <a href="?p=tiket_pesan&id=<?= $t['id_tiket'] ?>" class="btn btn-primary w-100">
                                            <i class="bi bi-cart"></i> Pesan Tiket
                                        </a>
                                    <?php elseif ($habis): ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="bi bi-x-circle"></i> Tiket Habis
                                        </button>
                                    <?php else: ?>
                                        <a href="?p=login" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-box-arrow-in-right"></i> Login untuk Pesan
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-ticket fs-1 text-muted"></i>
                    <p class="text-muted mt-3">Belum ada tiket tersedia untuk event ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
