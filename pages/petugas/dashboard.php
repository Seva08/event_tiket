<?php


// Proses check-in manual
if (isset($_POST['checkin_manual'])) {
    $kode_tiket = mysqli_real_escape_string($conn, $_POST['kode_tiket']);
    $attendee = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM attendee WHERE kode_tiket='$kode_tiket' AND status_checkin='belum'"));

    if ($attendee) {
        mysqli_query($conn, "UPDATE attendee SET status_checkin='sudah', waktu_checkin=NOW() WHERE kode_tiket='$kode_tiket'");
        echo "<script>alert('Check-in berhasil!'); window.location='?p=dashboard_petugas';</script>";
    } else {
        $error_checkin = "Kode tiket tidak ditemukan atau sudah check-in!";
    }
}



// Statistik
$total_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='pending'"))['total'];
$paid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='paid'"))['total'];
$cancelled = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='cancelled'"))['total'];
$total_checkin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM attendee WHERE status_checkin='sudah' AND DATE(waktu_checkin) = CURDATE()"))['total'];
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Petugas -->
        <?php include 'pages/admin/_sidebar.php'; ?>
        
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h2 class="fw-bold mb-1 text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Dashboard Petugas</h2>
                    <p class="text-muted mb-0">Panel operasional untuk validasi tiket & kehadiran.</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white px-3 py-2 rounded-pill shadow-sm border small fw-bold">
                        <i class="bi bi-calendar3 text-primary me-2"></i><?= date('d M Y') ?>
                    </div>
                    <div id="liveTime" class="bg-primary text-white px-3 py-2 rounded-pill shadow-sm small fw-bold" style="min-width: 100px; text-align: center;">
                        00:00:00
                    </div>
                </div>
            </div>

            <!-- Welcome Banner Premium -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-4" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                <div class="card-body p-4 p-md-5 position-relative">
                    <div class="position-relative z-1">
                        <h3 class="fw-bold text-white mb-2">Selamat Bertugas, <?= explode(' ', $_SESSION['nama'])[0] ?>! 👋</h3>
                        <p class="text-white text-opacity-75 mb-4 col-lg-8">Sistem siap digunakan. Pastikan setiap pengunjung melakukan check-in untuk validasi kehadiran dan keamanan event.</p>
                        <a href="?p=petugas_checkin" class="btn btn-light btn-lg rounded-pill px-4 fw-bold shadow-sm border-0 transition-transform">
                            <i class="bi bi-qr-code-scan me-2"></i>Mulai Scan Tiket
                        </a>
                    </div>
                    <!-- Decorative Icon -->
                    <i class="bi bi-shield-check position-absolute end-0 bottom-0 mb-n4 me-n2 text-white opacity-10" style="font-size: 15rem;"></i>
                </div>
            </div>

            <!-- Main Stats Grid -->
            <div class="row g-4 mb-4">
                <!-- Check-in Today (Highlighted) -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="bg-success bg-opacity-10 text-success rounded-3 icon-box p-3">
                                    <i class="bi bi-person-check-fill fs-3"></i>
                                </div>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">LIVE</span>
                            </div>
                            <h6 class="text-muted fw-bold text-uppercase small mb-1">Check-in Hari Ini</h6>
                            <div class="d-flex align-items-baseline">
                                <h1 class="display-4 fw-bold text-dark mb-0"><?= $total_checkin ?></h1>
                                <span class="ms-2 text-muted fw-semibold">Pengunjung</span>
                            </div>
                            <div class="mt-3 progress rounded-pill" style="height: 6px;">
                                <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Check-in Area -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h6 class="fw-bold text-dark text-uppercase small mb-0"><i class="bi bi-keyboard me-2"></i>Check-in Manual</h6>
                        </div>
                        <div class="card-body p-4">
                            <?php if (isset($error_checkin)): ?>
                                <div class="alert alert-danger rounded-3 small py-2"><i class="bi bi-exclamation-circle me-2"></i><?= $error_checkin ?></div>
                            <?php endif; ?>
                            <form method="POST" id="checkinFormDashboard">
                                <input type="hidden" name="checkin_manual" value="1">
                                <div class="input-group input-group-lg mb-3">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-hash text-muted"></i></span>
                                    <input type="text" name="kode_tiket" id="kodeTiketDashboard" class="form-control border-0 bg-light fs-6 shadow-none" placeholder="Masukkan Kode Tiket..." required autofocus>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                                        Check-in
                                    </button>
                                </div>
                                <p class="text-muted small mb-0 mt-3"><i class="bi bi-info-circle me-1"></i>Gunakan fitur ini jika kamera scanner bermasalah atau tiket fisik rusak.</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Ringkasan Premium (Gaya Admin) -->
            <div class="row g-4 mb-5">
                <!-- Check-in Hari Ini -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-success text-white overflow-hidden position-relative transition-transform hover-scale">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 small fw-bold">Live Check-in</span>
                                <i class="bi bi-person-check fs-4 opacity-75"></i>
                            </div>
                            <h2 class="fw-bold mb-1 display-6"><?= number_format($total_checkin, 0, ',', '.') ?></h2>
                            <p class="mb-0 small opacity-75 fw-medium">Pengunjung hari ini</p>
                        </div>
                        <i class="bi bi-person-check position-absolute end-0 bottom-0 opacity-25 z-0 display-1" style="transform: translate(5%, 5%);"></i>
                    </div>
                </div>

                <!-- Total Paid -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white overflow-hidden position-relative transition-transform hover-scale">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 small fw-bold">Order Lunas</span>
                                <i class="bi bi-cart-check fs-4 opacity-75"></i>
                            </div>
                            <h2 class="fw-bold mb-1 display-6"><?= number_format($paid, 0, ',', '.') ?></h2>
                            <p class="mb-0 small opacity-75 fw-medium">Transaksi berhasil</p>
                        </div>
                        <i class="bi bi-cart-check position-absolute end-0 bottom-0 opacity-25 z-0 display-1" style="transform: translate(5%, 5%);"></i>
                    </div>
                </div>

                <!-- Order Pending -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-warning text-white overflow-hidden position-relative transition-transform hover-scale">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 small fw-bold">Pending</span>
                                <i class="bi bi-hourglass-split fs-4 opacity-75"></i>
                            </div>
                            <h2 class="fw-bold mb-1 display-6"><?= number_format($pending, 0, ',', '.') ?></h2>
                            <p class="mb-0 small opacity-75 fw-medium">Menunggu bayar</p>
                        </div>
                        <i class="bi bi-hourglass-split position-absolute end-0 bottom-0 opacity-25 z-0 display-1" style="transform: translate(5%, 5%);"></i>
                    </div>
                </div>

                <!-- Order Batal -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-danger text-white overflow-hidden position-relative transition-transform hover-scale">
                        <div class="card-body p-4 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1 small fw-bold">Dibatalkan</span>
                                <i class="bi bi-x-circle fs-4 opacity-75"></i>
                            </div>
                            <h2 class="fw-bold mb-1 display-6"><?= number_format($cancelled, 0, ',', '.') ?></h2>
                            <p class="mb-0 small opacity-75 fw-medium">Transaksi batal</p>
                        </div>
                        <i class="bi bi-x-circle position-absolute end-0 bottom-0 opacity-25 z-0 display-1" style="transform: translate(5%, 5%);"></i>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.letter-spacing-1 { letter-spacing: 1px; }
.x-small { font-size: 0.7rem; }
.transition-transform { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.hover-scale:hover { transform: translateY(-5px) scale(1.02); z-index: 10; }
</style>

<script>
function updateClock() {
    const now = new Date();
    const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                       now.getMinutes().toString().padStart(2, '0') + ':' + 
                       now.getSeconds().toString().padStart(2, '0');
    document.getElementById('liveTime').textContent = timeString;
}
setInterval(updateClock, 1000);
updateClock();

// Auto-submit logic for dashboard scanner (hardware scanner focus)
const scanInput = document.getElementById('kodeTiketDashboard');
const scanForm = document.getElementById('checkinFormDashboard');
if (scanInput) {
    let timer;
    scanInput.addEventListener('input', () => {
        clearTimeout(timer);
        // Scanners are fast, wait 300ms after last char to auto-submit
        if (scanInput.value.length >= 8) { 
            timer = setTimeout(() => {
                scanForm.submit();
            }, 300); 
        }
    });
}
</script>
