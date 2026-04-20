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
    "SELECT t.*, (SELECT COUNT(*) FROM order_detail od
       JOIN orders o ON od.id_order = o.id_order
       WHERE od.id_tiket = t.id_tiket AND o.status='paid') as terjual
     FROM tiket t WHERE t.id_event = $id_event");
$tanggal = date('d M Y', strtotime($event['tanggal']));
$hari    = date('d', strtotime($event['tanggal']));
$bulan   = date('M Y', strtotime($event['tanggal']));
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=home">Home</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($event['nama_event']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- ── Event Info sidebar ── -->
        <div class="col-lg-4">
            <!-- Date banner card -->
            <div class="card mb-3" style="overflow:hidden;border:none!important; border-radius: var(--r-lg);">
                <?php $bg_image_detail = $event['gambar'] ? "url('uploads/{$event['gambar']}')" : "var(--g-primary)"; ?>
                <div style="background:<?= $bg_image_detail ?>;background-size:cover;background-position:center;padding:2rem 1.5rem;color:#fff;position:relative;overflow:hidden;">
                    <?php if($event['gambar']): ?>
                        <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);"></div>
                    <?php else: ?>
                        <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:rgba(255,255,255,.1);border-radius:50%;"></div>
                    <?php endif; ?>
                    <div class="d-flex align-items-start gap-3 position-relative" style="z-index: 1;">
                        <div class="text-center flex-shrink-0 text-white"
                             style="background:rgba(255,255,255,.2);backdrop-filter:blur(6px);border-radius:14px;padding:.6rem 1rem;min-width:60px;">
                            <div style="font-size:2rem;font-weight:800;line-height:1;"><?= $hari ?></div>
                            <div style="font-size:.7rem;text-transform:uppercase;opacity:.9;"><?= $bulan ?></div>
                        </div>
                        <div class="text-white">
                            <h5 class="fw-bold mb-1 lh-sm" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?= htmlspecialchars($event['nama_event']) ?></h5>
                            <div style="font-size:.82rem;opacity:.9;text-shadow: 0 1px 2px rgba(0,0,0,0.5);"><i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($event['nama_venue']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" style="border-radius:0 0 var(--r-lg) var(--r-lg);">
                        <li class="list-group-item d-flex align-items-center gap-3 py-3">
                            <div style="width:36px;height:36px;background:rgba(99,102,241,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-calendar3" style="color:var(--c-primary)"></i>
                            </div>
                            <div>
                                <div style="font-size:.72rem;color:var(--txt-muted);font-weight:600;text-transform:uppercase;">Tanggal</div>
                                <div class="fw-600" style="font-weight:600;"><?= $tanggal ?></div>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-3 py-3">
                            <div style="width:36px;height:36px;background:rgba(239,68,68,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-geo-alt-fill" style="color:var(--c-danger)"></i>
                            </div>
                            <div>
                                <div style="font-size:.72rem;color:var(--txt-muted);font-weight:600;text-transform:uppercase;">Venue</div>
                                <div class="fw-600" style="font-weight:600;"><?= htmlspecialchars($event['nama_venue']) ?></div>
                                <div style="font-size:.78rem;color:var(--txt-muted);"><?= htmlspecialchars($event['alamat']) ?></div>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-3 py-3">
                            <div style="width:36px;height:36px;background:rgba(16,185,129,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-people-fill" style="color:var(--c-success)"></i>
                            </div>
                            <div>
                                <div style="font-size:.72rem;color:var(--txt-muted);font-weight:600;text-transform:uppercase;">Kapasitas</div>
                                <div class="fw-600" style="font-weight:600;"><?= number_format($event['kapasitas']) ?> orang</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <?php if (isset($_SESSION['id_user'])): ?>
                <a href="?p=riwayat" class="btn btn-outline-primary w-100" style="border-radius:50px;">
                    <i class="bi bi-clock-history me-2"></i>Riwayat Pembelian Saya
                </a>
            <?php else: ?>
                <a href="?p=login" class="btn btn-primary w-100" style="border-radius:50px;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login untuk Memesan
                </a>
            <?php endif; ?>
        </div>

        <!-- ── Tiket List ── -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="page-title mb-0"><i class="bi bi-ticket-perforated me-2" style="color:var(--c-primary)"></i>Pilih Tiket</h4>
            </div>

            <?php if (mysqli_num_rows($tiket) > 0): ?>
                <div class="row g-3">
                    <?php while ($t = mysqli_fetch_assoc($tiket)):
                        $sisa  = $t['kuota'] - $t['terjual'];
                        $habis = $sisa <= 0;
                        $pct   = min(100, ($t['kuota'] > 0 ? ($t['terjual'] / $t['kuota']) * 100 : 0));
                    ?>
                    <div class="col-md-6">
                        <div class="card h-100 <?= $habis ? '' : '' ?>" style="<?= $habis ? 'opacity:.6;' : '' ?>">
                            <!-- Header -->
                            <div class="card-header" style="background:var(--g-primary);color:#fff;position:relative;overflow:hidden;">
                                <div style="position:absolute;top:-15px;right:-15px;width:60px;height:60px;background:rgba(255,255,255,.1);border-radius:50%;"></div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold"><?= htmlspecialchars($t['nama_tiket']) ?></span>
                                    <?php if ($habis): ?>
                                        <span class="badge bg-danger" style="border-radius:50px;">Habis</span>
                                    <?php elseif ($sisa <= 10): ?>
                                        <span class="badge bg-warning text-dark" style="border-radius:50px;">Sisa <?= $sisa ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success" style="border-radius:50px;">Tersedia</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Body -->
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <div style="font-size:.75rem;color:var(--txt-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Harga</div>
                                    <div class="fw-bold" style="font-size:1.6rem;color:var(--c-primary);font-weight:800;">
                                        Rp <?= number_format($t['harga'], 0, ',', '.') ?>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mb-2" style="font-size:.8rem;color:var(--txt-muted);">
                                    <span><i class="bi bi-ticket me-1"></i>Kuota: <?= $t['kuota'] ?></span>
                                    <span><i class="bi bi-cart-check me-1"></i>Terjual: <?= $t['terjual'] ?></span>
                                </div>
                                <div class="progress mb-4" style="height:5px;border-radius:10px;background:#f1f5f9;">
                                    <div class="progress-bar <?= $habis ? 'bg-danger' : 'bg-success' ?>"
                                         style="width:<?= $pct ?>%;border-radius:10px;"></div>
                                </div>

                                <?php if (!$habis && isset($_SESSION['id_user'])): ?>
                                    <a href="?p=tiket_pesan&id=<?= $t['id_tiket'] ?>" class="btn btn-primary w-100" style="border-radius:50px;">
                                        <i class="bi bi-cart-plus me-2"></i>Pesan Sekarang
                                    </a>
                                <?php elseif ($habis): ?>
                                    <button class="btn btn-secondary w-100" disabled style="border-radius:50px;opacity:.6;">
                                        <i class="bi bi-x-circle me-2"></i>Tiket Habis
                                    </button>
                                <?php else: ?>
                                    <a href="?p=login" class="btn btn-outline-primary w-100" style="border-radius:50px;">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Login untuk Pesan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div style="width:80px;height:80px;background:var(--g-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                        <i class="bi bi-ticket text-white fs-3"></i>
                    </div>
                    <h5 class="text-muted">Tiket Belum Tersedia</h5>
                    <p class="text-muted" style="font-size:.87rem;">Belum ada tiket yang diterbitkan untuk event ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
