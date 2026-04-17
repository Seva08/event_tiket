<!-- ════════════ HERO ════════════ -->
<div class="hero-section text-white" style="padding: 5.5rem 0 4.5rem;">
    <div class="container position-relative" style="z-index:1;">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge mb-3 px-3 py-2"
                      style="background:rgba(255,255,255,.18);backdrop-filter:blur(6px);font-size:.8rem;letter-spacing:.5px;border-radius:50px;">
                    <i class="bi bi-lightning-fill me-1"></i> Platform Tiket #1 Indonesia
                </span>
                <h1 class="display-4 fw-800 mb-3 lh-sm" style="font-weight:800;letter-spacing:-.5px;">
                    Pesan Tiket Event<br>
                    <span style="opacity:.85;">Favorit Kamu 🎟️</span>
                </h1>
                <p class="mb-4 lh-lg" style="font-size:1.05rem;opacity:.88;max-width:480px;">
                    Temukan konser, festival, workshop, dan ratusan event seru lainnya — semua dalam satu platform yang mudah dan aman.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if (!isset($_SESSION['id_user'])): ?>
                        <a href="?p=login" class="btn btn-light px-4 py-2 fw-bold shadow-sm"
                           style="border-radius:50px;color:var(--c-primary);font-size:.95rem;">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk & Pesan
                        </a>
                        <a href="#events" class="btn px-4 py-2 fw-600"
                           style="border-radius:50px;background:rgba(255,255,255,.15);backdrop-filter:blur(6px);color:#fff;border:1.5px solid rgba(255,255,255,.4);font-size:.95rem;">
                            <i class="bi bi-calendar-event me-2"></i>Lihat Event
                        </a>
                    <?php else: ?>
                        <?php $dash = $_SESSION['role']=='admin' ? 'dashboard_admin' : ($_SESSION['role']=='petugas' ? 'dashboard_petugas' : 'dashboard_user'); ?>
                        <a href="?p=<?= $dash ?>" class="btn btn-light px-4 py-2 fw-bold shadow-sm"
                           style="border-radius:50px;color:var(--c-primary);">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard Saya
                        </a>
                        <a href="#events" class="btn px-4 py-2"
                           style="border-radius:50px;background:rgba(255,255,255,.15);backdrop-filter:blur(6px);color:#fff;border:1.5px solid rgba(255,255,255,.4);">
                            <i class="bi bi-calendar-event me-2"></i>Lihat Event
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Quick stats -->
                <div class="d-flex gap-4 mt-5 flex-wrap">
                    <?php
                    $total_e = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM event"))['c'];
                    $total_u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='user'"))['c'];
                    $total_o = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='paid'"))['c'];
                    ?>
                    <div style="opacity:.9;">
                        <div class="fw-bold fs-4" style="font-weight:800;"><?= $total_e ?></div>
                        <div style="font-size:.8rem;opacity:.75;">Event Tersedia</div>
                    </div>
                    <div style="width:1px;background:rgba(255,255,255,.25);"></div>
                    <div style="opacity:.9;">
                        <div class="fw-bold fs-4" style="font-weight:800;"><?= $total_u ?>+</div>
                        <div style="font-size:.8rem;opacity:.75;">Member Aktif</div>
                    </div>
                    <div style="width:1px;background:rgba(255,255,255,.25);"></div>
                    <div style="opacity:.9;">
                        <div class="fw-bold fs-4" style="font-weight:800;"><?= $total_o ?>+</div>
                        <div style="font-size:.8rem;opacity:.75;">Tiket Terjual</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="bi bi-calendar2-event floating-icon"
                   style="font-size:11rem;opacity:.25;display:block;"></i>
                <!-- Floating badge -->
                <div class="d-inline-flex align-items-center gap-2 mt-n4"
                     style="background:rgba(255,255,255,.18);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.3);border-radius:50px;padding:.55rem 1.2rem;font-size:.85rem;">
                    <i class="bi bi-shield-check-fill text-white"></i>
                    <span class="text-white fw-600">100% Aman &amp; Terpercaya</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ════════════ FEATURES ════════════ -->
<div class="container py-5">
    <div class="row g-4 mb-5">
        <?php $features = [
            ['bi-lightning-charge-fill','var(--g-primary)','Cepat & Mudah','Proses pemesanan tiket dalam hitungan menit, kapan saja dan di mana saja.'],
            ['bi-shield-check-fill','var(--g-success)','100% Aman','Transaksi aman dengan sistem keamanan berlapis yang terpercaya.'],
            ['bi-qr-code','var(--g-warning)','E-Tiket Digital','Tiket digital dengan QR Code — tanpa perlu cetak, langsung tunjukkan saat masuk.'],
        ]; foreach ($features as $f): ?>
        <div class="col-md-4">
            <div class="card h-100 p-1">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div style="width:46px;height:46px;background:<?= $f[1] ?>;border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi <?= $f[0] ?> text-white fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1"><?= $f[2] ?></h6>
                        <p class="text-muted mb-0" style="font-size:.85rem;line-height:1.55;"><?= $f[3] ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ════════════ EVENT LIST ════════════ -->
    <div id="events">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1"><i class="bi bi-fire me-2" style="color:var(--c-warning)"></i>Event Tersedia</h2>
                <p class="text-muted mb-0" style="font-size:.87rem;">Pilih event favoritmu dan langsung pesan tiketnya!</p>
            </div>
            <span class="badge px-3 py-2" style="background:var(--g-primary);font-size:.8rem;border-radius:50px;">
                <?= $total_e ?> event aktif
            </span>
        </div>

        <div class="row g-4">
            <?php
            $query = mysqli_query($conn, "SELECT e.*, v.nama_venue, v.alamat FROM event e JOIN venue v ON e.id_venue = v.id_venue ORDER BY e.tanggal DESC");
            if (mysqli_num_rows($query) > 0):
                while ($d = mysqli_fetch_assoc($query)):
                    $tanggal   = date('d M Y', strtotime($d['tanggal']));
                    $tgl_short = date('d', strtotime($d['tanggal']));
                    $bln_short = date('M Y', strtotime($d['tanggal']));
            ?>
            <div class="col-md-4">
                <div class="card card-event h-100">
                    <!-- Banner -->
                    <div class="card-header" style="padding:1.4rem 1.4rem 1rem;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge mb-2"
                                      style="background:rgba(255,255,255,.2);border-radius:50px;font-size:.72rem;">
                                    <i class="bi bi-calendar3 me-1"></i><?= $tanggal ?>
                                </span>
                                <h5 class="mb-0 fw-bold lh-sm" style="font-size:1rem;">
                                    <?= htmlspecialchars($d['nama_event']) ?>
                                </h5>
                            </div>
                            <div class="text-center ms-2 flex-shrink-0"
                                 style="background:rgba(255,255,255,.18);border-radius:12px;padding:.4rem .7rem;min-width:48px;">
                                <div style="font-size:1.4rem;font-weight:800;line-height:1;"><?= $tgl_short ?></div>
                                <div style="font-size:.65rem;opacity:.85;text-transform:uppercase;"><?= $bln_short ?></div>
                            </div>
                        </div>
                    </div>
                    <!-- Body -->
                    <div class="card-body px-4 py-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-geo-alt-fill" style="color:var(--c-danger);font-size:.85rem;"></i>
                            <span class="fw-600" style="font-size:.87rem;color:var(--txt);font-weight:600;">
                                <?= htmlspecialchars($d['nama_venue']) ?>
                            </span>
                        </div>
                        <p class="text-muted mb-0" style="font-size:.8rem;padding-left:1.3rem;">
                            <?= htmlspecialchars($d['alamat']) ?>
                        </p>
                    </div>
                    <!-- Footer -->
                    <div class="card-footer bg-white border-0 px-4 pb-4 pt-0">
                        <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-primary w-100"
                           style="border-radius:50px;">
                            <i class="bi bi-ticket-perforated me-2"></i>Lihat &amp; Pesan Tiket
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="col-12 text-center py-5">
                <div style="width:80px;height:80px;background:var(--g-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-calendar-x text-white fs-3"></i>
                </div>
                <h5 class="text-muted">Belum Ada Event Tersedia</h5>
                <p class="text-muted" style="font-size:.87rem;">Pantau terus halaman ini untuk event terbaru!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ════════════ FOOTER STRIP ════════════ -->
<footer class="mt-5 py-4">
    <div class="container text-center">
        <p class="mb-0">
            &copy; <?= date('Y') ?> <strong style="color:var(--c-primary)">EventTiket</strong>.
            Dibuat dengan <i class="bi bi-heart-fill text-danger"></i> untuk semua pecinta event.
        </p>
    </div>
</footer>
