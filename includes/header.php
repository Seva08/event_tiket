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
    <title>Event Tiket - Sistem Pemesanan Tiket Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background: var(--primary-gradient) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            transform: translateY(-2px);
        }

        .card-event {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            border-radius: 16px;
            overflow: hidden;
            background: white;
        }

        .card-event:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card-event .card-header {
            background: var(--primary-gradient);
            border: none;
            padding: 1rem 1.5rem;
        }

        .card-event .card-body {
            padding: 1.5rem;
        }

        .card-event .card-footer {
            padding: 1rem 1.5rem 1.5rem;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-success {
            background: var(--success-gradient);
            border: none;
        }

        .btn-warning {
            background: var(--warning-gradient);
            border: none;
            color: white;
        }

        .sidebar {
            min-height: calc(100vh - 70px);
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link {
            border-radius: 10px;
            margin: 5px 15px;
            padding: 12px 20px;
            color: #a0a0a0 !important;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white !important;
            transform: translateX(5px);
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .stat-card {
            border: none;
            border-radius: 16px;
            padding: 1.5rem;
            color: white;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .stat-card.primary {
            background: var(--primary-gradient);
        }

        .stat-card.success {
            background: var(--success-gradient);
        }

        .stat-card.warning {
            background: var(--warning-gradient);
        }

        .stat-card.danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }

        .table-container {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #495057;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 0.5em 1em;
            border-radius: 20px;
            font-weight: 500;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .page-title {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .breadcrumb {
            background: none;
            padding: 0;
        }

        .hero-section {
            background: var(--primary-gradient);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            border-radius: 16px 16px 0 0 !important;
            border: none;
        }

        .ticket-code {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
            letter-spacing: 2px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="?p=home"><i class="bi bi-ticket-perforated"></i> EventTiket</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="?p=home"><i class="bi bi-house"></i> Home</a>
                    </li>
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?p=dashboard_admin"><i class="bi bi-speedometer2"></i> Dashboard</a>
                            </li>
                        <?php elseif ($_SESSION['role'] == 'petugas'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?p=dashboard_petugas"><i class="bi bi-person-badge"></i> Dashboard Petugas</a>
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
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nama']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="?p=logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?p=login"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>