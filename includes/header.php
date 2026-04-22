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
        /* ━━━━━━━━━━━━━━ DESIGN TOKENS ━━━━━━━━━━━━━━ */
        :root {
            --c-primary: #6366f1;
            --c-success: #10b981;
            --c-warning: #f59e0b;
            --c-danger:  #ef4444;
            --c-info:    #06b6d4;

            --g-primary: linear-gradient(135deg, #6366f1, #8b5cf6);
            --g-success: linear-gradient(135deg, #10b981, #06b6d4);
            --g-warning: linear-gradient(135deg, #f59e0b, #ef4444);
            --g-danger:  linear-gradient(135deg, #ef4444, #ec4899);
            --g-info:    linear-gradient(135deg, #06b6d4, #6366f1);
            --g-dark:    linear-gradient(160deg, #1e293b, #0f172a);

            --bg:        #f1f5f9;
            --bg-card:   #ffffff;
            --border:    #e2e8f0;
            --txt:       #1e293b;
            --txt-muted: #64748b;

            --r-sm: 8px;  --r-md: 12px;  --r-lg: 18px;  --r-xl: 24px;
            --sh-sm: 0 1px 4px rgba(0,0,0,.06);
            --sh-md: 0 4px 20px rgba(0,0,0,.09);
            --sh-lg: 0 12px 40px rgba(0,0,0,.14);
        }

        /* ━━━━━━━━━━━━━━ BASE ━━━━━━━━━━━━━━ */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--txt);
            font-size: .9rem;
            line-height: 1.65;
        }
        h1,h2,h3,h4,h5,h6 { font-weight: 700; }
        a { text-decoration: none; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 6px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--c-primary); }

        /* ━━━━━━━━━━━━━━ NAVBAR ━━━━━━━━━━━━━━ */
        .navbar {
            background: #fff !important;
            border-bottom: 1px solid var(--border);
            box-shadow: var(--sh-sm);
            padding: .55rem 0;
            position: sticky; top: 0; z-index: 1030;
        }
        .navbar-brand {
            font-weight: 800; font-size: 1.2rem; letter-spacing: -.5px;
            color: var(--c-primary) !important;
            display: flex; align-items: center; gap: .5rem;
        }
        .brand-icon {
            background: var(--g-primary);
            width: 33px; height: 33px; border-radius: 9px;
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff; font-size: .95rem; flex-shrink: 0;
        }
        .navbar .nav-link {
            color: var(--txt-muted) !important; font-weight: 500;
            font-size: .865rem; padding: .45rem .8rem !important;
            border-radius: var(--r-sm); transition: all .2s;
        }
        .navbar .nav-link:hover { color: var(--c-primary) !important; background: rgba(99,102,241,.08); }
        .navbar .nav-link i { margin-right: 3px; }
        .navbar .dropdown-menu {
            border: 1px solid var(--border); border-radius: var(--r-md);
            box-shadow: var(--sh-lg); padding: .35rem;
            margin-top: .4rem !important; min-width: 170px;
        }
        .navbar .dropdown-item {
            border-radius: var(--r-sm); font-size: .855rem;
            padding: .5rem .8rem; font-weight: 500; transition: all .18s; color: var(--txt);
        }
        .navbar .dropdown-item:hover { background: rgba(99,102,241,.08); color: var(--c-primary); }
        .navbar .dropdown-item.text-danger:hover { background: rgba(239,68,68,.08); }
        .user-pill {
            background: var(--g-primary); color: #fff;
            padding: .3rem .9rem; border-radius: 50px;
            font-weight: 600; font-size: .8rem;
            display: inline-flex; align-items: center; gap: .35rem;
        }
        .navbar .btn-login-nav {
            background: var(--c-primary) !important; color: #fff !important;
            padding: .38rem 1.1rem !important; border-radius: 50px !important;
            font-weight: 600 !important; border: none !important;
        }
        .navbar .btn-login-nav:hover { opacity: .88; color: #fff !important; background: var(--c-primary) !important; }

        /* ━━━━━━━━━━━━━━ SIDEBAR ━━━━━━━━━━━━━━ */
        .sidebar {
            min-height: calc(100vh - 58px);
            background: #0f172a;
            box-shadow: 4px 0 24px rgba(0,0,0,.18);
            padding-top: .5rem; padding-bottom: 2rem;
        }
        .sidebar-label {
            color: #334155; font-size: .63rem; font-weight: 700;
            letter-spacing: 1.4px; text-transform: uppercase;
            padding: .85rem 1.2rem .3rem;
        }
        .sidebar .nav-link {
            color: #94a3b8 !important; font-weight: 500; font-size: .85rem;
            border-radius: var(--r-md); margin: 1px 8px;
            padding: .6rem .95rem !important; transition: all .18s;
            display: flex; align-items: center; gap: .55rem;
        }
        .sidebar .nav-link i { font-size: .95rem; width: 18px; text-align: center; flex-shrink: 0; }
        .sidebar .nav-link:hover { background: rgba(99,102,241,.14); color: #e2e8f0 !important; transform: translateX(3px); }
        .sidebar .nav-link.active { background: rgba(99,102,241,.22); color: #a5b4fc !important; font-weight: 600; }
        .sidebar .nav-link.active i { color: #818cf8; }
        .sidebar .nav-link.text-danger { color: #f87171 !important; }
        .sidebar .nav-link.text-danger:hover { background: rgba(239,68,68,.12); color: #fca5a5 !important; }

        /* ━━━━━━━━━━━━━━ CARDS ━━━━━━━━━━━━━━ */
        .card {
            border: 1px solid var(--border) !important;
            border-radius: var(--r-lg) !important;
            box-shadow: var(--sh-sm); background: var(--bg-card);
            transition: box-shadow .2s;
        }
        .card:hover { box-shadow: var(--sh-md); }
        .card-header {
            border-radius: var(--r-lg) var(--r-lg) 0 0 !important;
            border-bottom: 1px solid var(--border) !important;
            background: #f8fafc; padding: .9rem 1.2rem; font-weight: 600;
        }
        .card-header.bg-primary   { background: var(--g-primary) !important; border-bottom: none !important; }
        .card-header.bg-success   { background: var(--g-success) !important; border-bottom: none !important; }
        .card-header.bg-dark      { background: var(--g-dark)    !important; border-bottom: none !important; }
        .card-header.bg-secondary { background: #475569           !important; border-bottom: none !important; }
        .card-header.bg-warning   { background: var(--g-warning)  !important; border-bottom: none !important; color:#fff; }
        .card-header.bg-info      { background: var(--g-info)     !important; border-bottom: none !important; }

        /* stat cards */
        .stat-card {
            border: none !important; border-radius: var(--r-lg) !important;
            padding: 1.4rem; color: #fff;
            position: relative; overflow: hidden;
            transition: transform .25s, box-shadow .25s;
        }
        .stat-card::before {
            content: ''; position: absolute; top: -18px; right: -18px;
            width: 80px; height: 80px; background: rgba(255,255,255,.12); border-radius: 50%;
        }
        .stat-card::after {
            content: ''; position: absolute; bottom: -28px; right: 14px;
            width: 110px; height: 110px; background: rgba(255,255,255,.07); border-radius: 50%;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--sh-lg) !important; }
        .stat-card.primary { background: var(--g-primary); }
        .stat-card.success { background: var(--g-success); }
        .stat-card.warning { background: var(--g-warning); }
        .stat-card.danger  { background: var(--g-danger);  }
        .stat-card.info    { background: var(--g-info);    }
        .stat-card h2, .stat-card h4 { font-weight: 800; }

        /* event cards */
        .card-event {
            border: 1px solid var(--border) !important;
            border-radius: var(--r-xl) !important;
            overflow: hidden;
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }
        .card-event:hover { transform: translateY(-6px); box-shadow: var(--sh-lg) !important; }
        .card-event .card-header {
            background: var(--g-primary) !important;
            border: none !important; color: #fff;
            position: relative; overflow: hidden;
        }
        .card-event .card-header::before {
            content: ''; position: absolute; top: -20px; right: -20px;
            width: 80px; height: 80px; background: rgba(255,255,255,.1); border-radius: 50%;
        }

        /* ━━━━━━━━━━━━━━ TABLE ━━━━━━━━━━━━━━ */
        .table-container {
            background: var(--bg-card); border-radius: var(--r-lg);
            padding: 1.4rem; border: 1px solid var(--border); box-shadow: var(--sh-sm);
        }
        .table { --bs-table-border-color: #f1f5f9; }
        .table thead th {
            background: #f8fafc; border-bottom: 2px solid var(--border);
            font-weight: 600; font-size: .78rem; letter-spacing: .5px;
            text-transform: uppercase; color: var(--txt-muted); padding: .8rem 1rem;
        }
        .table tbody td { padding: .8rem 1rem; vertical-align: middle; border-color: #f1f5f9; }
        .table tbody tr { transition: background .15s; }
        .table tbody tr:hover { background: #f8fafc; }

        /* ━━━━━━━━━━━━━━ BUTTONS ━━━━━━━━━━━━━━ */
        .btn {
            border-radius: var(--r-md); font-weight: 600; font-size: .875rem;
            padding: .52rem 1.2rem; transition: all .2s; border: none; letter-spacing: .15px;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,.15); }
        .btn:active { transform: translateY(0); }
        .btn-primary   { background: var(--g-primary); color: #fff; }
        .btn-success   { background: var(--g-success); color: #fff; }
        .btn-warning   { background: var(--g-warning); color: #fff; }
        .btn-danger    { background: var(--g-danger);  color: #fff; }
        .btn-info      { background: var(--g-info);    color: #fff; }
        .btn-secondary { background: #e2e8f0; color: var(--txt); }
        .btn-secondary:hover { background: #cbd5e1; color: var(--txt); transform: translateY(-1px); }
        .btn-outline-primary  { border: 2px solid var(--c-primary) !important; color: var(--c-primary); background: transparent; }
        .btn-outline-primary:hover  { background: var(--g-primary); color: #fff; border-color: transparent !important; }
        .btn-outline-secondary { border: 2px solid var(--border) !important; color: var(--txt-muted); background: transparent; }
        .btn-outline-secondary:hover { background: #f1f5f9; color: var(--txt); }
        .btn-sm { padding: .32rem .7rem; font-size: .785rem; border-radius: var(--r-sm); }
        .btn-lg { padding: .7rem 1.65rem; font-size: .95rem; }

        /* ━━━━━━━━━━━━━━ FORMS ━━━━━━━━━━━━━━ */
        .form-control, .form-select {
            border-radius: var(--r-md); border: 1.5px solid var(--border);
            padding: .62rem 1rem; font-size: .875rem; background: #f8fafc; transition: all .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--c-primary); background: #fff;
            box-shadow: 0 0 0 3px rgba(99,102,241,.15);
        }
        .form-label {
            font-weight: 600; font-size: .8rem; color: var(--txt-muted);
            text-transform: uppercase; letter-spacing: .4px; margin-bottom: .35rem;
        }
        .input-group-text {
            background: #f1f5f9; border: 1.5px solid var(--border); color: var(--txt-muted);
        }

        /* ━━━━━━━━━━━━━━ MISC ━━━━━━━━━━━━━━ */
        .badge { padding: .38em .82em; border-radius: 50px; font-weight: 600; font-size: .74rem; }

        .pagination .page-link {
            border-radius: var(--r-sm) !important; border: 1.5px solid var(--border);
            color: var(--txt-muted); font-weight: 500; font-size: .84rem;
            padding: .38rem .72rem; margin: 0 2px; transition: all .18s;
        }
        .pagination .page-link:hover { background: rgba(99,102,241,.1); border-color: var(--c-primary); color: var(--c-primary); }
        .pagination .page-item.active .page-link { background: var(--g-primary); border-color: transparent; color: #fff; }

        .alert { border-radius: var(--r-md); border: none; font-weight: 500; font-size: .875rem; }

        .page-title { font-weight: 800; font-size: 1.35rem; color: var(--txt); margin-bottom: .2rem; }
        .breadcrumb { background: none; padding: 0; font-size: .8rem; }
        .breadcrumb-item+.breadcrumb-item::before { color: var(--txt-muted); }
        .breadcrumb-item a { color: var(--c-primary); font-weight: 500; }
        .breadcrumb-item.active { color: var(--txt-muted); }

        .hero-section {
            background: var(--g-primary); position: relative;
            overflow: hidden; padding: 5rem 0 4rem;
        }
        .hero-section::before {
            content: ''; position: absolute; top: -70px; right: -70px;
            width: 380px; height: 380px; background: rgba(255,255,255,.06); border-radius: 50%;
        }
        .hero-section::after {
            content: ''; position: absolute; bottom: -55px; left: -35px;
            width: 280px; height: 280px; background: rgba(255,255,255,.04); border-radius: 50%;
        }

        .ticket-code {
            font-family: 'Courier New', monospace;
            background: var(--g-primary);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800; letter-spacing: 2px; font-size: 1.05rem;
        }
        .floating-icon { animation: floatY 3.5s ease-in-out infinite; }
        @keyframes floatY { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-16px); } }
        .text-gradient {
            background: var(--g-primary);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        footer { background: #fff !important; border-top: 1px solid var(--border) !important; color: var(--txt-muted); font-size: .82rem; }

        .nav-tabs { border-bottom: 2px solid var(--border); }
        .nav-tabs .nav-link {
            border: none; border-bottom: 2px solid transparent; border-radius: 0;
            margin-bottom: -2px; color: var(--txt-muted); font-weight: 600;
            font-size: .875rem; padding: .62rem 1.2rem; transition: all .2s;
        }
        .nav-tabs .nav-link:hover { color: var(--c-primary); border-bottom-color: #a5b4fc; }
        .nav-tabs .nav-link.active { color: var(--c-primary); border-bottom-color: var(--c-primary); background: none; }

        @media (max-width: 768px) {
            .sidebar { min-height: auto; }
            .stat-card { margin-bottom: 1rem; }
            .page-title { font-size: 1.15rem; }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="?p=home">
                <span class="brand-icon"><i class="bi bi-ticket-perforated-fill"></i></span>
                YuiPass
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-4" style="color:var(--txt-muted)"></i>
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
                                <span class="user-pill">
                                    <i class="bi bi-person-circle"></i>
                                    <?= htmlspecialchars($_SESSION['nama']) ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header" style="font-size:.72rem;letter-spacing:.5px"><?= strtoupper($_SESSION['role']) ?></h6></li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item" href="?p=profile"><i class="bi bi-person-lines-fill me-2"></i>Profil Saya</a></li>
                                <li><a class="dropdown-item text-danger" href="?p=logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-1">
                            <a class="nav-link btn-login-nav" href="?p=login"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>