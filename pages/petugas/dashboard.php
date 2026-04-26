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
        <nav class="col-md-2 d-none d-md-block bg-dark py-2" id="adminSidebar">
            <div class="px-3 py-3 mb-1">
                <div class="d-flex align-items-center gap-2">
                    <span class="bg-success text-white rounded-2 d-inline-flex align-items-center justify-content-center p-2">
                        <i class="bi bi-person-badge-fill small"></i>
                    </span>
                    <div>
                        <div class="text-light fw-bold small lh-sm">Panel Petugas</div>
                        <div class="text-secondary small"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></div>
                    </div>
                </div>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-light d-flex align-items-center gap-2 mx-2 rounded-2 <?= (isset($_GET['p']) && $_GET['p'] === 'dashboard_petugas') ? 'active bg-primary' : '' ?>"
                       href="?p=dashboard_petugas">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light d-flex align-items-center gap-2 mx-2 rounded-2 <?= (isset($_GET['p']) && $_GET['p'] === 'petugas_checkin') ? 'active bg-primary' : '' ?>"
                       href="?p=petugas_checkin">
                        <i class="bi bi-qr-code-scan"></i> Scan Check-in
                    </a>
                </li>
                <li><div class="text-secondary fw-bold text-uppercase px-3 pt-3 pb-1 small">AKUN</div></li>
                <li class="nav-item">
                    <a class="nav-link text-light d-flex align-items-center gap-2 mx-2 rounded-2 text-danger"
                       href="?p=logout">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold"><i class="bi bi-person-badge"></i> Dashboard Petugas</h2>
                    <p class="text-muted mb-0">Kelola transaksi dan check-in pengunjung</p>
                </div>
                <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-calendar3"></i> <?= date('d M Y') ?></span>
            </div>

            <!-- Welcome Banner -->
            <div class="card border-0 shadow-sm mb-4 bg-primary text-white rounded-3">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 me-4 d-none d-sm-block">
                        <i class="bi bi-person-vcard fs-1"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Selamat Datang, Petugas <?= htmlspecialchars($_SESSION['nama']) ?>! 👋</h4>
                        <p class="mb-0 text-white text-opacity-75">Pantau statistik kehadiran event dan lakukan validasi tiket masuk pengunjung di sini.</p>
                    </div>
                </div>
            </div>

            <!-- Statistik Check-in Utama -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3 bg-success text-white">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase fw-bold text-white opacity-75 mb-1 small"><i class="bi bi-qr-code-scan me-2"></i>Total Check-in Hari Ini</h6>
                                    <h1 class="display-4 fw-bold mb-0"><?= $total_checkin ?> <span class="fs-4 fw-normal text-white text-opacity-75">Orang</span></h1>
                                </div>
                                <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center d-none d-sm-flex p-4 ratio ratio-1x1 w-auto">
                                    <i class="bi bi-person-check-fill fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Order Pendukung -->
            <h6 class="fw-bold text-muted text-uppercase mb-3 small">Statistik Transaksi Keseluruhan</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted fw-bold text-uppercase small">Total Order</small>
                                <div class="bg-primary bg-opacity-10 rounded p-1"><i class="bi bi-receipt text-primary"></i></div>
                            </div>
                            <h3 class="fw-bold mb-0 text-dark"><?= $total_order ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted fw-bold text-uppercase small">Pending</small>
                                <div class="bg-warning bg-opacity-10 rounded p-1"><i class="bi bi-clock text-warning"></i></div>
                            </div>
                            <h3 class="fw-bold mb-0 text-dark"><?= $pending ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted fw-bold text-uppercase small">Paid</small>
                                <div class="bg-success bg-opacity-10 rounded p-1"><i class="bi bi-check-circle text-success"></i></div>
                            </div>
                            <h3 class="fw-bold mb-0 text-dark"><?= $paid ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted fw-bold text-uppercase small">Cancelled</small>
                                <div class="bg-danger bg-opacity-10 rounded p-1"><i class="bi bi-x-circle text-danger"></i></div>
                            </div>
                            <h3 class="fw-bold mb-0 text-dark"><?= $cancelled ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Check-in -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="mb-0"><i class="bi bi-qr-code-scan"></i> Check-in Manual</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error_checkin)): ?>
                                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $error_checkin ?></div>
                            <?php endif; ?>
                            <form method="POST" class="row g-3">
                                <div class="col-md-8">
                                    <input type="text" name="kode_tiket" class="form-control form-control-lg" placeholder="Masukkan kode tiket..." required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" name="checkin_manual" class="btn btn-success btn-lg w-100">
                                        <i class="bi bi-check-lg"></i> Check-in
                                    </button>
                                </div>
                            </form>
                            <div class="mt-3 text-center">
                                <a href="?p=petugas_checkin" class="btn btn-outline-primary">
                                    <i class="bi bi-camera"></i> Buka Scanner QR
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 rounded-circle p-3 me-4">
                                    <i class="bi bi-info-circle fs-1"></i>
                                </div>
                                <div>
                                    <h5 class="mb-2">Panduan Petugas</h5>
                                    <p class="mb-0 opacity-75">
                                        • Lakukan check-in menggunakan Scan QR<br>
                                        • Lakukan check-in manual menggunakan Kode Tiket<br>
                                        • Pantau statistik pengunjung dan kehadiran
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
