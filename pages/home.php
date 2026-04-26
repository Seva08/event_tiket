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
<div class="bg-primary text-white py-5">
    <div class="container position-relative py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-2 mb-3">
                    <i class="bi bi-lightning-fill me-1"></i> Platform Tiket #1 Indonesia
                </span>
                <h1 class="display-4 fw-bold mb-3 lh-sm">
                    Pesan Tiket Event<br>
                    <span class="opacity-75">Favorit Kamu</span>
                </h1>
                <p class="mb-4 lh-lg fs-6 opacity-75 col-md-10 col-lg-8 mx-auto mx-lg-0">
                    Temukan konser, festival, workshop, dan ratusan event seru lainnya — semua dalam satu platform yang mudah dan aman.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if (!isset($_SESSION['id_user'])): ?>
                        <a href="?p=login" class="btn btn-light px-4 py-2 fw-bold rounded-pill shadow-sm text-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk & Pesan
                        </a>
                        <a href="#events" class="btn btn-outline-light px-4 py-2 rounded-pill">
                            <i class="bi bi-calendar-event me-2"></i>Lihat Event
                        </a>
                    <?php else: ?>
                        <?php $dash = $_SESSION['role']=='admin' ? 'dashboard_admin' : ($_SESSION['role']=='petugas' ? 'dashboard_petugas' : 'dashboard_user'); ?>
                        <a href="?p=<?= $dash ?>" class="btn btn-light px-4 py-2 fw-bold rounded-pill shadow-sm text-primary">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard Saya
                        </a>
                        <a href="#events" class="btn btn-outline-light px-4 py-2 rounded-pill">
                            <i class="bi bi-calendar-event me-2"></i>Lihat Event
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Search & Filter -->
                <div class="mt-4 p-2 bg-white rounded-pill shadow-lg d-inline-flex align-items-center w-100 col-md-10 col-lg-8">
                    <form method="GET" class="d-flex w-100 gap-2 px-2">
                        <input type="hidden" name="p" value="home">
                        <div class="flex-grow-1 position-relative border-end pe-2">
                            <i class="bi bi-search position-absolute text-muted ms-3 top-50 translate-middle-y"></i>
                            <input type="text" name="search" class="form-control border-0 bg-transparent shadow-none ps-5" placeholder="Cari event seru..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="position-relative border-end pe-2 col-3">
                            <input type="date" name="date" class="form-control border-0 bg-transparent shadow-none small" value="<?= htmlspecialchars($date_filter) ?>">
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
                    <div class="opacity-75">
                        <div class="fw-bold fs-4"><?= $total_e ?></div>
                        <div class="small opacity-75">Event Tersedia</div>
                    </div>
                    <div class="vr bg-white opacity-25"></div>
                    <div class="opacity-75">
                        <div class="fw-bold fs-4"><?= $total_u ?>+</div>
                        <div class="small opacity-75">Member Aktif</div>
                    </div>
                    <div class="vr bg-white opacity-25"></div>
                    <div class="opacity-75">
                        <div class="fw-bold fs-4"><?= $total_o ?>+</div>
                        <div class="small opacity-75">Tiket Terjual</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="bi bi-calendar2-event display-1 opacity-25 d-block"></i>
                <!-- Floating badge -->
                <div class="d-inline-flex align-items-center gap-2 mt-3 bg-white bg-opacity-25 rounded-pill px-3 py-2 small">
                    <i class="bi bi-shield-check-fill text-white"></i>
                    <span class="text-white fw-semibold">100% Aman &amp; Terpercaya</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ════════════ FEATURES ════════════ -->
<div class="container py-5">
    <div class="row g-4 mb-5">
        <?php $features = [
            ['bi-lightning-charge-fill','bg-primary','Cepat & Mudah','Proses pemesanan tiket dalam hitungan menit, kapan saja dan di mana saja.'],
            ['bi-shield-check','bg-success','100% Aman','Transaksi aman dengan sistem keamanan berlapis yang terpercaya.'],
            ['bi-qr-code','bg-warning','E-Tiket Digital','Tiket digital dengan QR Code — tanpa perlu cetak, langsung tunjukkan saat masuk.'],
        ]; foreach ($features as $f): ?>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="<?= $f[1] ?> text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 p-3">
                        <i class="bi <?= $f[0] ?> fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1"><?= $f[2] ?></h6>
                        <p class="text-muted mb-0 small"><?= $f[3] ?></p>
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
                <h2 class="fw-bold mb-1"><i class="bi bi-tags-fill me-2 text-primary"></i>Promo & Voucher</h2>
                <p class="text-muted mb-0 small">Klaim voucher menarik untuk pengalaman event lebih hemat!</p>
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
                <div class="card border-0 shadow-sm rounded-4 position-relative overflow-hidden transition-all h-100 voucher-card">
                    <div class="d-flex h-100">
                        <!-- Sisi Kiri: Nilai Diskon -->
                        <div class="p-3 <?= $is_used ? 'bg-secondary' : 'bg-primary' ?> text-white d-flex flex-column justify-content-center align-items-center text-center position-relative" style="width: 100px; flex-shrink: 0;">
                            <!-- Variasi Punch Holes (Atas & Bawah) -->
                            <div class="position-absolute bg-white rounded-circle" style="width: 20px; height: 20px; top: -10px; right: -10px; z-index: 2;"></div>
                            <div class="position-absolute bg-white rounded-circle" style="width: 20px; height: 20px; bottom: -10px; right: -10px; z-index: 2;"></div>
                            
                            <i class="bi bi-ticket-perforated mb-2 opacity-50 fs-4"></i>
                            <div class="fw-bold small opacity-75 text-uppercase">DISKON</div>
                            <div class="fw-bold fs-4"><?= number_format($v['potongan']/1000, 0) ?>k</div>
                        </div>
                        
                        <!-- Garis Sobekan (Dashed Line) -->
                        <div class="border-start border-2 border-dashed h-100 opacity-25" style="border-style: dashed !important; border-color: #64748b !important;"></div>
                        
                        <!-- Sisi Kanan: Info & Kode -->
                        <div class="p-4 bg-white flex-grow-1 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge <?= $is_used ? 'bg-secondary' : 'bg-primary' ?> bg-opacity-10 text-<?= $is_used ? 'secondary' : 'primary' ?> px-2 py-1 rounded-pill small" style="font-size: 0.65rem;">
                                    <i class="bi bi-layers-fill me-1"></i><?= $unlimited ? 'Unlimited' : 'Sisa ' . $v['kuota'] ?>
                                </span>
                                <?php if($is_used): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill small" style="font-size: 0.65rem;"><i class="bi bi-check-circle-fill"></i></span>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="fw-bold mb-1 text-dark font-monospace tracking-tighter"><?= $v['kode_voucher'] ?></h5>
                            <p class="text-muted mb-3" style="font-size: 0.75rem;">Berlaku untuk semua jenis tiket event aktif.</p>
                            
                            <div class="mt-auto pt-2 border-top border-light">
                                <?php if($is_used): ?>
                                    <button class="btn btn-sm btn-light w-100 text-muted disabled rounded-pill" style="font-size: 0.75rem;">Sudah Digunakan</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-bold transition-all" 
                                            onclick="navigator.clipboard.writeText('<?= $v['kode_voucher'] ?>'); Swal.fire({icon: 'success', title: 'Kode Disalin!', text: 'Gunakan kode <?= $v['kode_voucher'] ?> saat membeli tiket.', timer: 1500, showConfirmButton: false});" 
                                            style="font-size: 0.75rem;">
                                        <i class="bi bi-copy me-1"></i> Salin Kode
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="col-12 text-center py-5 bg-light rounded-4 border border-dashed">
                <i class="bi bi-ticket-detailed display-4 text-muted opacity-25 d-block mb-3"></i>
                <p class="text-muted fw-bold mb-0">Belum ada promo tersedia saat ini.</p>
                <p class="text-muted small">Cek kembali nanti untuk diskon menarik lainnya!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tambahkan CSS inline sedikit untuk hover effect -->
    <style>
        .voucher-card { transition: all 0.3s ease; }
        .voucher-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        .border-dashed { border-style: dashed !important; }
    </style>

    <!-- ════════════ EVENT LIST ════════════ -->
    <div id="events">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-fire me-2 text-warning"></i>Event Tersedia</h2>
                <p class="text-muted mb-0 small">Pilih event favoritmu dan langsung pesan tiketnya!</p>
            </div>
            <span class="badge bg-primary rounded-pill px-3 py-2">
                <?= $total_e ?> Event
            </span>
        </div>

        <div class="row g-3 g-md-4">
            <?php
            if (mysqli_num_rows($query) > 0):
                while ($d = mysqli_fetch_assoc($query)):
                    $tanggal   = date('d M Y', strtotime($d['tanggal']));
                    $tgl_short = date('d', strtotime($d['tanggal']));
                    $bln_short = date('M Y', strtotime($d['tanggal']));
                    $is_passed = strtotime($d['tanggal']) < strtotime('today');
            ?>
            <div class="col-sm-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden <?= $is_passed ? 'opacity-75' : '' ?>">
                    <?php if($d['gambar']): ?>
                        <div class="position-relative">
                            <div class="ratio ratio-16x9">
                                <img src="uploads/<?= $d['gambar'] ?>" alt="<?= htmlspecialchars($d['nama_event']) ?>" class="w-100 h-100 object-fit-cover">
                            </div>
                            <!-- Date Badge Overlay -->
                            <div class="position-absolute top-0 end-0 m-3 bg-white bg-opacity-90 rounded-3 shadow-sm p-2 text-center" style="min-width: 60px; z-index: 2;">
                                <div class="text-primary fw-bold fs-5 lh-1"><?= $tgl_short ?></div>
                                <div class="text-muted fw-bold text-uppercase" style="font-size: 0.6rem;"><?= date('M Y', strtotime($d['tanggal'])) ?></div>
                            </div>
                            <?php if($is_passed): ?>
                            <div class="position-absolute top-0 start-0 m-3" style="z-index: 2;">
                                <span class="badge bg-danger rounded-pill shadow-sm px-3 py-2 fw-bold">Selesai</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="card-header border-0 p-4 <?= $is_passed ? 'bg-secondary text-white' : 'bg-primary text-white' ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="col-8">
                                    <span class="badge bg-white bg-opacity-25 rounded-pill mb-2 small">
                                        <i class="bi bi-calendar3 me-1"></i><?= $tanggal ?>
                                    </span>
                                    <?php if($is_passed): ?>
                                    <span class="badge mb-2 ms-1 bg-danger rounded-pill small">Selesai</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center bg-white bg-opacity-25 rounded-3 p-2 px-3">
                                    <div class="fs-4 fw-bold lh-1"><?= $tgl_short ?></div>
                                    <div class="small opacity-75 text-uppercase" style="font-size: 0.6rem;"><?= date('M', strtotime($d['tanggal'])) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-dark lh-base"><?= htmlspecialchars($d['nama_event']) ?></h5>
                        <div class="d-flex align-items-start gap-2 mb-0">
                            <i class="bi bi-geo-alt-fill text-danger flex-shrink-0 mt-1"></i>
                            <div class="small text-muted">
                                <div class="fw-bold text-dark text-truncate d-block" style="max-width: 200px;"><?= htmlspecialchars($d['nama_venue']) ?></div>
                                <div class="text-truncate d-block" style="max-width: 200px;"><?= htmlspecialchars($d['alamat']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 p-4 pt-0 mt-auto">
                        <?php if($is_passed): ?>
                            <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-outline-secondary w-100 rounded-pill py-2">
                                <i class="bi bi-info-circle me-2"></i>Lihat Detail
                            </a>
                        <?php else: ?>
                            <a href="?p=event_detail&id=<?= $d['id_event'] ?>" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm fw-bold">
                                <i class="bi bi-ticket-perforated me-2"></i>Lihat &amp; Pesan Tiket
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="col-12 text-center py-5">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 w-25 ratio ratio-1x1 min-vw-10">
                    <i class="bi bi-calendar-x text-white fs-3"></i>
                </div>
                <h5 class="text-muted">Belum Ada Event Tersedia</h5>
                <p class="text-muted small">Pantau terus halaman ini untuk event terbaru!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
