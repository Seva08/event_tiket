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

    <!-- ═══ Global Premium Style Layer ═══ -->
    <style>
        /* ── CSS Custom Properties ── */
        :root {
            --bs-body-font-family: 'Inter', system-ui, -apple-system, sans-serif;
            --bs-primary: #4f46e5;
            --bs-primary-rgb: 79, 70, 229;
            --bs-success: #10b981;
            --bs-success-rgb: 16, 185, 129;
            --bs-warning: #f59e0b;
            --bs-warning-rgb: 245, 158, 11;
            --bs-danger: #ef4444;
            --bs-danger-rgb: 239, 68, 68;
            --bs-info: #06b6d4;
            --bs-info-rgb: 6, 182, 212;
            --bs-dark: #1e293b;
            --bs-dark-rgb: 30, 41, 59;
            --bs-body-bg: #f1f5f9;
            --bs-border-radius: 0.75rem;
            --bs-border-radius-lg: 1rem;
            --bs-border-radius-sm: 0.5rem;
            --shadow-card: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.06);
            --shadow-card-hover: 0 4px 6px rgba(0,0,0,.04), 0 10px 24px rgba(0,0,0,.1);
            --transition-base: all .25s cubic-bezier(.4,0,.2,1);
        }

        /* ── Base ── */
        body {
            font-family: var(--bs-body-font-family);
            background-color: var(--bs-body-bg);
            color: #334155;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        ::selection {
            background: rgba(var(--bs-primary-rgb), .15);
            color: var(--bs-primary);
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── Links ── */
        a { transition: var(--transition-base); }

        /* ── Navbar ── */
        .navbar {
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            background: rgba(255,255,255,.85) !important;
            border-bottom: 1px solid rgba(0,0,0,.06) !important;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .navbar-brand { letter-spacing: -.025em; }
        .navbar .nav-link {
            font-weight: 500;
            font-size: .875rem;
            color: #475569;
            padding: .5rem .875rem !important;
            border-radius: .5rem;
            transition: var(--transition-base);
        }
        .navbar .nav-link:hover {
            color: var(--bs-primary);
            background: rgba(var(--bs-primary-rgb), .06);
        }

        /* ── Cards ── */
        .card {
            border-radius: var(--bs-border-radius) !important;
            border: 1px solid rgba(0,0,0,.06);
            box-shadow: var(--shadow-card);
            transition: var(--transition-base);
        }
        .card:hover {
            box-shadow: var(--shadow-card-hover);
        }
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.06);
            font-weight: 600;
        }

        /* ── Buttons ── */
        .btn {
            font-weight: 600;
            font-size: .875rem;
            letter-spacing: .01em;
            transition: var(--transition-base);
            border-radius: .5rem;
        }
        .btn:active { transform: scale(.97); }
        .btn-primary {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        .btn-primary:hover, .btn-primary:focus {
            background: #4338ca;
            border-color: #4338ca;
            box-shadow: 0 4px 14px rgba(var(--bs-primary-rgb), .4);
        }
        .btn-success {
            background: var(--bs-success);
            border-color: var(--bs-success);
        }
        .btn-success:hover, .btn-success:focus {
            background: #059669;
            border-color: #059669;
            box-shadow: 0 4px 14px rgba(var(--bs-success-rgb), .4);
        }
        .btn-danger {
            background: var(--bs-danger);
            border-color: var(--bs-danger);
        }
        .btn-danger:hover, .btn-danger:focus {
            background: #dc2626;
            border-color: #dc2626;
        }
        .btn-warning {
            background: var(--bs-warning);
            border-color: var(--bs-warning);
        }
        .btn-outline-primary {
            color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        .btn-outline-primary:hover {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
            box-shadow: 0 4px 14px rgba(var(--bs-primary-rgb), .3);
        }
        .rounded-pill { border-radius: 50rem !important; }

        /* ── Badges ── */
        .badge {
            font-weight: 600;
            letter-spacing: .02em;
        }
        .bg-primary { background-color: var(--bs-primary) !important; }
        .text-primary { color: var(--bs-primary) !important; }
        .bg-success { background-color: var(--bs-success) !important; }
        .text-success { color: var(--bs-success) !important; }
        .bg-warning { background-color: var(--bs-warning) !important; }
        .bg-danger { background-color: var(--bs-danger) !important; }
        .bg-info { background-color: var(--bs-info) !important; }
        .bg-dark { background-color: var(--bs-dark) !important; }
        .border-primary { border-color: var(--bs-primary) !important; }
        .border-success { border-color: var(--bs-success) !important; }
        .border-warning { border-color: var(--bs-warning) !important; }
        .border-danger { border-color: var(--bs-danger) !important; }

        /* ── Form Controls ── */
        .form-control, .form-select {
            border-radius: .625rem;
            padding: .625rem 1rem;
            border: 1.5px solid #e2e8f0;
            font-size: .875rem;
            transition: var(--transition-base);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), .12);
        }
        .form-control-lg { padding: .75rem 1.25rem; font-size: 1rem; }
        .input-group-text {
            border-radius: .625rem;
            border: 1.5px solid #e2e8f0;
            font-size: .875rem;
        }
        .form-label {
            font-weight: 600;
            font-size: .8125rem;
            color: #475569;
            margin-bottom: .375rem;
        }

        /* ── Tables ── */
        .table {
            font-size: .875rem;
            --bs-table-hover-bg: rgba(var(--bs-primary-rgb), .03);
        }
        .table > thead {
            background: linear-gradient(135deg, var(--bs-dark), #334155);
            color: white;
        }
        .table > thead th {
            font-weight: 600;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .875rem 1rem;
            border: none;
            white-space: nowrap;
        }
        .table > tbody > tr {
            transition: var(--transition-base);
        }
        .table > tbody > tr:hover {
            transform: translateX(2px);
        }
        .table > tbody td {
            padding: .75rem 1rem;
            vertical-align: middle;
        }
        .table-dark { --bs-table-bg: var(--bs-dark); }

        /* ── Admin Sidebar ── */
        #adminSidebar, .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, var(--bs-dark) 100%) !important;
            min-height: 100vh;
            border-right: 1px solid rgba(255,255,255,.06);
        }
        #adminSidebar .nav-link, .sidebar .nav-link {
            font-size: .8125rem;
            font-weight: 500;
            padding: .6rem .875rem;
            margin: 1px 0;
            border-radius: .5rem;
            transition: var(--transition-base);
            color: #94a3b8 !important;
        }
        #adminSidebar .nav-link:hover, .sidebar .nav-link:hover {
            background: rgba(255,255,255,.08);
            color: #e2e8f0 !important;
            transform: translateX(3px);
        }
        #adminSidebar .nav-link.active, .sidebar .nav-link.active {
            background: var(--bs-primary) !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), .4);
        }

        /* ── Alerts ── */
        .alert {
            border-radius: var(--bs-border-radius);
            border: none;
            font-size: .875rem;
        }

        /* ── Pagination ── */
        .page-link {
            border-radius: .5rem !important;
            font-weight: 600;
            font-size: .8125rem;
            padding: .5rem .75rem;
            border: none;
            color: #475569;
            transition: var(--transition-base);
        }
        .page-link:hover {
            background: rgba(var(--bs-primary-rgb), .1);
            color: var(--bs-primary);
        }
        .page-item.active .page-link {
            background: var(--bs-primary);
            color: white;
            box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), .4);
        }
        .pagination { gap: .25rem; }

        /* ── Dropdown ── */
        .dropdown-menu {
            border: 1px solid rgba(0,0,0,.06);
            box-shadow: 0 10px 40px rgba(0,0,0,.12);
            border-radius: .75rem;
            padding: .375rem;
            animation: dropdownFadeIn .2s ease;
        }
        .dropdown-item {
            border-radius: .5rem;
            font-size: .8125rem;
            font-weight: 500;
            padding: .5rem .75rem;
            transition: var(--transition-base);
        }
        .dropdown-item:hover {
            background: rgba(var(--bs-primary-rgb), .06);
            color: var(--bs-primary);
        }
        .dropdown-item.active, .dropdown-item:active {
            background: var(--bs-primary);
            color: white;
        }

        /* ── Breadcrumb ── */
        .breadcrumb {
            background: white;
            border-radius: .625rem;
            padding: .75rem 1.25rem;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(0,0,0,.04);
            font-size: .8125rem;
        }

        /* ── List Group ── */
        .list-group-item {
            border: none;
            font-size: .875rem;
            transition: var(--transition-base);
        }
        .list-group-item:hover {
            background: rgba(var(--bs-primary-rgb), .04);
            padding-left: 1.25rem;
        }
        .list-group-item.active {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        /* ── Tabs / Nav Pills ── */
        .nav-pills .nav-link {
            font-weight: 600;
            font-size: .875rem;
            transition: var(--transition-base);
        }
        .nav-pills .nav-link.active {
            background: var(--bs-primary);
            box-shadow: 0 2px 10px rgba(var(--bs-primary-rgb), .35);
        }

        /* ── Stats Cards ── */
        .bg-primary.card, .card.bg-primary { background: linear-gradient(135deg, var(--bs-primary) 0%, #6366f1 100%) !important; }
        .bg-success.card, .card.bg-success { background: linear-gradient(135deg, var(--bs-success) 0%, #34d399 100%) !important; }
        .bg-warning.card, .card.bg-warning { background: linear-gradient(135deg, var(--bs-warning) 0%, #fbbf24 100%) !important; }
        .bg-danger.card, .card.bg-danger  { background: linear-gradient(135deg, var(--bs-danger) 0%, #f87171 100%) !important; }
        .bg-info.card, .card.bg-info      { background: linear-gradient(135deg, var(--bs-info) 0%, #22d3ee 100%) !important; }

        /* ── Hero / Large bg-primary Sections ── */
        div.bg-primary.py-5,
        div.bg-primary.text-white.py-5,
        .card-header.bg-primary,
        .alert.bg-primary {
            background: linear-gradient(135deg, var(--bs-primary) 0%, #6366f1 60%, #818cf8 100%) !important;
        }

        /* ── Footer ── */
        footer {
            background: white;
            border-top: 1px solid rgba(0,0,0,.06) !important;
        }
        footer a:hover {
            color: var(--bs-primary) !important;
        }

        /* ── SweetAlert2 polish ── */
        .swal2-popup {
            border-radius: 1rem !important;
            font-family: var(--bs-body-font-family) !important;
        }

        /* ── Fade-in animation ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .container, .container-fluid { animation: fadeInUp .4s ease-out; }

        /* ── Utility: text truncation ── */
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ── Focus ring consistency ── */
        *:focus-visible {
            outline: 2px solid rgba(var(--bs-primary-rgb), .5);
            outline-offset: 2px;
        }

        /* ── Print hide ── */
        @media print {
            .navbar, footer, #adminSidebar, .sidebar, .btn, .breadcrumb { display: none !important; }
        }

        /* ── Page Title (admin pages) ── */
        .page-title {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -.02em;
            color: var(--bs-dark);
        }

        /* ── Table Container wrapper ── */
        .table-container {
            background: white;
            border-radius: var(--bs-border-radius);
            padding: 1.25rem;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(0,0,0,.04);
        }

        /* ── Responsive fix: mobile sidebar toggle ── */
        @media (max-width: 767.98px) {
            .col-md-10.ms-sm-auto { margin-left: 0 !important; }
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="?p=home">
                <span class="bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center p-2">
                    <i class="bi bi-ticket-perforated-fill"></i>
                </span>
                YuiPass
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
                        <li class="nav-item dropdown ms-2">
                            <a class="nav-link dropdown-toggle p-0" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold">
                                    <i class="bi bi-person-circle"></i>
                                    <?= htmlspecialchars($_SESSION['nama']) ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2 mt-2">
                                <li><h6 class="dropdown-header small text-uppercase"><?= strtoupper($_SESSION['role']) ?></h6></li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item rounded-2" href="?p=profile"><i class="bi bi-person-lines-fill me-2"></i>Profil Saya</a></li>
                                <li><a class="dropdown-item rounded-2 text-danger" href="?p=logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-1">
                            <a class="nav-link btn btn-primary text-white rounded-pill px-3 py-2 fw-semibold" href="?p=login"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>