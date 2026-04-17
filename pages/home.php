<!-- Hero Section -->
<div class="hero-section text-white py-5">
    <div class="container position-relative" style="z-index: 1;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 fw-bold mb-3"><i class="bi bi-ticket-perforated"></i> EventTiket</h1>
                <p class="lead fs-5 mb-4">Platform pemesanan tiket event terpercaya. Temukan dan pesan tiket untuk konser, festival, workshop, dan berbagai event menarik lainnya.</p>
                <?php if (!isset($_SESSION['id_user'])): ?>
                    <a href="?p=login" class="btn btn-light btn-lg px-4 py-3 fw-bold shadow-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Mulai Explorasi
                    </a>
                    <a href="#events" class="btn btn-outline-light btn-lg px-4 py-3 ms-2">
                        <i class="bi bi-calendar-event me-2"></i> Lihat Event
                    </a>
                <?php else: ?>
                    <a href="?p=dashboard_user" class="btn btn-light btn-lg px-4 py-3 fw-bold shadow-lg">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard Saya
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-calendar2-event floating-icon" style="font-size: 10rem; opacity: 0.4;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Event List -->
<div class="container py-5" id="events">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-event"></i> Daftar Event</h2>
        <span class="text-muted">Temukan event favoritmu</span>
    </div>

    <div class="row">
        <?php
        $query = mysqli_query($conn, "SELECT event.*, venue.nama_venue, venue.alamat FROM event JOIN venue ON event.id_venue = venue.id_venue ORDER BY event.tanggal DESC");
        if (mysqli_num_rows($query) > 0):
            while ($d = mysqli_fetch_array($query)) {
                $tanggal = date('d M Y', strtotime($d['tanggal']));
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card card-event h-100">
                        <div class="card-header bg-primary text-white">
                            <small><i class="bi bi-calendar3"></i> <?= $tanggal ?></small>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($d['nama_event']) ?></h5>
                            <p class="text-muted">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($d['nama_venue']) ?><br>
                                <small><?= htmlspecialchars($d['alamat']) ?></small>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-primary w-100">
                                <i class="bi bi-ticket"></i> Lihat Tiket
                            </a>
                        </div>
                    </div>
                </div>
            <?php
            }
        else:
            ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                <p class="text-muted mt-3">Tidak ada event yang tersedia saat ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
