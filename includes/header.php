<?php
// Cek jika belum ada session_start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include config dari root
if (file_exists('config.php')) {
    include 'config.php';
} elseif (file_exists('../config.php')) {
    include '../config.php';
} elseif (file_exists('../../config.php')) {
    include '../../config.php';
}

// Cek login untuk halaman tertentu (berdasarkan parameter 'p')
$page_param = isset($_GET['p']) ? $_GET['p'] : 'home';
$protected_pages = [
    'dashboard_admin',
    'dashboard_user',
    'dashboard_petugas',
    'riwayat',
    'pesanan_saya',
    'admin_venue',
    'admin_event',
    'admin_tiket',
    'admin_voucher',
    'admin_laporan',
    'admin_checkin',
    'admin_order_detail',
    'petugas_checkin'
];

if (in_array($page_param, $protected_pages) && !isset($_SESSION['id_user'])) {
    header("Location: ?p=login");
    exit;
}

// Cek role admin
$admin_pages = ['dashboard_admin', 'admin_venue', 'admin_event', 'admin_tiket', 'admin_voucher', 'admin_laporan', 'admin_checkin', 'admin_order_detail'];
if (in_array($page_param, $admin_pages) && (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin')) {
    header("Location: ?p=dashboard_user");
    exit;
}

// Cek role petugas
$petugas_pages = ['dashboard_petugas', 'petugas_checkin'];
if (in_array($page_param, $petugas_pages) && (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'petugas']))) {
    header("Location: ?p=dashboard_user");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YuiPass — Sistem Pemesanan Tiket Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .icon-box {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .icon-box-sm {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="?p=home">
                <span class="bg-primary text-white rounded-3 icon-box shadow-sm">
                    <i class="bi bi-ticket-perforated-fill fs-5"></i>
                </span>
                <span class="ls-tight">YuiPass</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-4 text-secondary"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link" href="?p=home"><i class="bi bi-house-door"></i> Home</a>
                    </li>
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?p=dashboard_admin"><i class="bi bi-speedometer2"></i> Dashboard</a>
                            </li>
                        <?php elseif ($_SESSION['role'] == 'petugas'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?p=dashboard_petugas"><i class="bi bi-person-badge"></i> Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="?p=petugas_checkin"><i class="bi bi-qr-code-scan"></i> Check-in</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?p=dashboard_user"><i class="bi bi-person"></i> Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="?p=riwayat"><i class="bi bi-clock-history"></i> Riwayat</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-0" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="bg-primary text-white rounded-pill px-3 py-1 fw-medium shadow-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-person-circle"></i>
                                    <span class="d-none d-sm-inline small"><?= htmlspecialchars($_SESSION['nama']) ?></span>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2 animated fadeIn" aria-labelledby="userDropdown" style="min-width: 200px;">
                                <li>
                                    <div class="px-3 py-2">
                                        <div class="fw-bold text-dark small"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                                        <div class="text-muted small" style="font-size: 0.75rem;"><?= strtoupper($_SESSION['role']) ?></div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider opacity-50"></li>
                                <li><a class="dropdown-item rounded-3 py-2" href="?p=profile"><i class="bi bi-person-badge me-2 text-primary"></i>Profil Saya</a></li>
                                <?php if($_SESSION['role'] == 'user'): ?>
                                    <li><a class="dropdown-item rounded-3 py-2" href="?p=riwayat"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pesanan</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider opacity-50"></li>
                                <li><a class="dropdown-item rounded-3 py-2 text-danger" href="?p=logout"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-primary text-white rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" href="?p=login">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>