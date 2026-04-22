<?php
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$date_filter = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : '';

$where = "WHERE (e.nama_event LIKE '%$search%' OR v.nama_venue LIKE '%$search%')";
if ($date_filter) {
    $where .= " AND e.tanggal = '$date_filter'";
}

$query = mysqli_query($conn, "SELECT e.*, v.nama_venue, v.alamat FROM event e JOIN venue v ON e.id_venue = v.id_venue $where ORDER BY e.tanggal DESC");
?>
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
                    <span style="opacity:.85;">Favorit Kamu</span>
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

                <!-- Search & Filter -->
                <div class="mt-4 p-2 bg-white rounded-pill shadow-lg d-inline-flex align-items-center w-100" style="max-width: 600px; border: 1px solid rgba(255,255,255,0.2);">
                    <form method="GET" class="d-flex w-100 gap-2 px-2">
                        <input type="hidden" name="p" value="home">
                        <div class="flex-grow-1 position-relative border-end pe-2">
                            <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%);"></i>
                            <input type="text" name="search" class="form-control border-0 bg-transparent shadow-none" placeholder="Cari event seru..." value="<?= htmlspecialchars($search) ?>" style="padding-left: 35px;">
                        </div>
                        <div class="position-relative border-end pe-2" style="width: 160px;">
                            <i class="bi bi-calendar-event position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%);"></i>
                            <input type="date" name="date" class="form-control border-0 bg-transparent shadow-none" value="<?= htmlspecialchars($date_filter) ?>" style="padding-left: 35px; font-size: 0.85rem;">
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Cari</button>
                    </form>
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
            ['bi-shield-check','var(--g-success)','100% Aman','Transaksi aman dengan sistem keamanan berlapis yang terpercaya.'],
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

    <!-- ════════════ VOUCHERS ════════════ -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1"><i class="bi bi-tags me-2" style="color:var(--c-primary)"></i>Promo & Voucher</h2>
                <p class="text-muted mb-0" style="font-size:.87rem;">Gunakan kode voucher di bawah ini saat checkout untuk mendapatkan potongan harga!</p>
            </div>
        </div>

        <div class="row g-4">
            <?php
            $user_id = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 0;
            $q_voucher = mysqli_query($conn, "SELECT v.*, 
                (SELECT COUNT(*) FROM orders o WHERE o.id_user = $user_id AND o.id_voucher = v.id_voucher AND o.status != 'cancel') as is_used
                FROM voucher v WHERE v.status = 'aktif' ORDER BY v.potongan DESC");
            
            if (mysqli_num_rows($q_voucher) > 0):
                while ($v = mysqli_fetch_assoc($q_voucher)):
                    $is_used = $v['is_used'] > 0;
                    $unlimited = $v['kuota'] == 0;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 position-relative" style="border-radius:18px; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; border: 1px dashed #dee2e6 !important;">
                    <div class="d-flex h-100">
                        <!-- Left Part (Discount) -->
                        <div class="p-3 text-white d-flex flex-column justify-content-center align-items-center text-center" 
                             style="width: 110px; background: <?= $is_used ? 'var(--bs-secondary)' : 'var(--g-primary)' ?>;">
                            <small style="font-size: 0.65rem; text-transform: uppercase; opacity: 0.8;">Diskon</small>
                            <div class="fw-bold" style="font-size: 1.1rem;">Rp</div>
                            <div class="fw-800" style="font-size: 1.4rem; font-weight: 800;"><?= number_format($v['potongan']/1000, 0) ?>k</div>
                        </div>
                        
                        <!-- Right Part (Details) -->
                        <div class="p-3 flex-grow-1 d-flex flex-column justify-content-center">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge <?= $is_used ? 'bg-secondary' : 'bg-primary' ?> bg-opacity-10 text-<?= $is_used ? 'secondary' : 'primary' ?> mb-2" style="font-size: 0.7rem; border-radius: 6px;">
                                    <?= $unlimited ? 'Unlimited' : 'Sisa ' . $v['kuota'] ?>
                                </span>
                                <?php if($is_used): ?>
                                    <span class="text-success" style="font-size: 0.75rem; font-weight: 600;"><i class="bi bi-check-circle-fill me-1"></i>Terpakai</span>
                                <?php endif; ?>
                            </div>
                            <h6 class="fw-bold mb-2 text-dark font-monospace" style="letter-spacing: 1px;"><?= $v['kode_voucher'] ?></h6>
                            <div class="d-flex align-items-center justify-content-between mt-auto">
                                <small class="text-muted" style="font-size: 0.7rem;">Berlaku Semua Tiket</small>
                                <?php if(!$is_used): ?>
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.7rem; border-radius: 50px;" onclick="navigator.clipboard.writeText('<?= $v['kode_voucher'] ?>'); alert('Kode <?= $v['kode_voucher'] ?> disalin!')">Copy</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Decorative Circles -->
                    <div style="position: absolute; width: 20px; height: 20px; background: #f8fafc; border-radius: 50%; left: 100px; top: -10px; border-bottom: 1px dashed #dee2e6;"></div>
                    <div style="position: absolute; width: 20px; height: 20px; background: #f8fafc; border-radius: 50%; left: 100px; bottom: -10px; border-top: 1px dashed #dee2e6;"></div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="col-12 text-center py-4 bg-light rounded-4">
                <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Belum ada promo tersedia saat ini.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ════════════ EVENT LIST ════════════ -->
    <div id="events">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1"><i class="bi bi-fire me-2" style="color:var(--c-warning)"></i>Event Tersedia</h2>
                <p class="text-muted mb-0" style="font-size:.87rem;">Pilih event favoritmu dan langsung pesan tiketnya!</p>
            </div>
            <span class="badge px-3 py-2" style="background:var(--g-primary);font-size:.8rem;border-radius:50px;">
                <?= $total_e ?> Event
            </span>
        </div>

        <div class="row g-4">
            <?php
            if (mysqli_num_rows($query) > 0):
                while ($d = mysqli_fetch_assoc($query)):
                    $tanggal   = date('d M Y', strtotime($d['tanggal']));
                    $tgl_short = date('d', strtotime($d['tanggal']));
                    $bln_short = date('M Y', strtotime($d['tanggal']));
                    $is_passed = strtotime($d['tanggal']) < strtotime('today');
            ?>
            <div class="col-md-4">
                <div class="card card-event h-100 <?= $is_passed ? 'opacity-75' : '' ?>">
                    <?php if($d['gambar']): ?>
                        <!-- TAMPILAN BARU (ADA GAMBAR) -->
                        <!-- Banner Image (16:9) -->
                        <div style="position:relative; width:100%; padding-top:56.25%; overflow:hidden; border-radius: var(--r-xl) var(--r-xl) 0 0;">
                            <img src="uploads/<?= $d['gambar'] ?>" alt="<?= htmlspecialchars($d['nama_event']) ?>" style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; <?= $is_passed ? 'filter: grayscale(100%);' : '' ?>">
                            <!-- Floating Date Badge -->
                            <div style="position:absolute; top:15px; right:15px; background:rgba(255,255,255,0.95); backdrop-filter:blur(4px); padding:0.5rem 0.8rem; border-radius:12px; text-align:center; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
                                <div style="color:var(--c-primary); font-weight:800; font-size:1.3rem; line-height:1;"><?= $tgl_short ?></div>
                                <div style="color:var(--txt-muted); font-size:0.65rem; font-weight:700; text-transform:uppercase; margin-top:2px;"><?= $bln_short ?></div>
                            </div>
                            <?php if($is_passed): ?>
                            <div style="position:absolute; top:15px; left:15px; background:var(--c-danger); color:white; padding:0.3rem 0.8rem; border-radius:50px; font-size:0.75rem; font-weight:bold; box-shadow:0 4px 15px rgba(0,0,0,0.2);">
                                Selesai
                            </div>
                            <?php endif; ?>
                        </div>
                        <!-- Body -->
                        <div class="card-body px-4 py-4">
                            <h5 class="fw-bold mb-3 lh-sm" style="color:var(--txt); font-size:1.1rem;"><?= htmlspecialchars($d['nama_event']) ?></h5>
                            <div class="d-flex align-items-start gap-2 mb-1">
                                <i class="bi bi-geo-alt-fill mt-1" style="color:var(--c-danger);font-size:.9rem;"></i>
                                <div>
                                    <div class="fw-bold" style="font-size:.87rem;color:var(--txt);"><?= htmlspecialchars($d['nama_venue']) ?></div>
                                    <div class="text-muted" style="font-size:.8rem;"><?= htmlspecialchars($d['alamat']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- TAMPILAN LAMA (TIDAK ADA GAMBAR) -->
                        <div class="card-header position-relative <?= $is_passed ? 'bg-secondary' : '' ?>" style="padding:1.4rem 1.4rem 1rem; <?= !$is_passed ? 'background: var(--g-primary);' : '' ?> background-size: cover; background-position: center; border-radius: var(--r-lg) var(--r-lg) 0 0;">
                            <div class="d-flex justify-content-between align-items-start position-relative" style="z-index:1; color:#fff;">
                                <div>
                                    <span class="badge mb-2 text-white"
                                          style="background:rgba(255,255,255,.2);backdrop-filter:blur(4px);border-radius:50px;font-size:.72rem;">
                                        <i class="bi bi-calendar3 me-1"></i><?= $tanggal ?>
                                    </span>
                                    <?php if($is_passed): ?>
                                    <span class="badge mb-2 text-white ms-1 bg-danger" style="border-radius:50px;font-size:.72rem;">Selesai</span>
                                    <?php endif; ?>
                                    <h5 class="mb-0 fw-bold lh-sm text-white" style="font-size:1rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                        <?= htmlspecialchars($d['nama_event']) ?>
                                    </h5>
                                </div>
                                <div class="text-center ms-2 flex-shrink-0 text-white"
                                     style="background:rgba(255,255,255,.2);backdrop-filter:blur(4px);border-radius:12px;padding:.4rem .7rem;min-width:48px;">
                                    <div style="font-size:1.4rem;font-weight:800;line-height:1;"><?= $tgl_short ?></div>
                                    <div style="font-size:.65rem;opacity:.9;text-transform:uppercase;"><?= $bln_short ?></div>
                                </div>
                            </div>
                        </div>
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
                    <?php endif; ?>
                    <!-- Footer -->
                    <div class="card-footer bg-white border-0 px-4 pb-4 pt-0">
                        <?php if($is_passed): ?>
                            <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-secondary w-100"
                               style="border-radius:50px;">
                                <i class="bi bi-calendar-x me-2"></i>Lihat Detail
                            </a>
                        <?php else: ?>
                            <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-primary w-100"
                               style="border-radius:50px;">
                                <i class="bi bi-ticket-perforated me-2"></i>Lihat &amp; Pesan Tiket
                            </a>
                        <?php endif; ?>
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
