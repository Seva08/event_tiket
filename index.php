<?php
session_start();
include 'config.php';

// Routing berbasis parameter 'p'
$page = isset($_GET['p']) ? $_GET['p'] : 'home';

// Daftar halaman yang tersedia
$allowed_pages = [
    'home', 'login', 'logout', 'register',
    'event_detail', 'tiket_pesan', 'riwayat',
    'dashboard_admin', 'dashboard_user', 'dashboard_petugas',
    'admin_venue', 'admin_venue_tambah', 'admin_venue_edit', 'admin_venue_hapus',
    'admin_event', 'admin_event_tambah', 'admin_event_edit',
    'admin_tiket', 'admin_tiket_tambah', 'admin_tiket_edit',
    'admin_voucher', 'admin_voucher_tambah', 'admin_voucher_edit',
    'admin_laporan', 'admin_checkin', 'admin_order_detail', 'admin_order_list',
    'petugas_checkin'
];

// Security check
if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

// === PRE-PROCESS: Handle actions BEFORE any HTML output ===

// Handle login POST
if ($page === 'login' && isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']);
    $q        = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    $data     = mysqli_fetch_assoc($q);
    if (mysqli_num_rows($q) > 0) {
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['role']    = $data['role'];
        $_SESSION['nama']    = $data['nama'];
        if ($data['role'] == 'admin') {
            header("Location: index.php?p=dashboard_admin");
        } elseif ($data['role'] == 'petugas') {
            header("Location: index.php?p=dashboard_petugas");
        } else {
            header("Location: index.php?p=dashboard_user");
        }
        exit;
    } else {
        $_SESSION['login_error'] = 'Email atau Password Salah!';
        header("Location: index.php?p=login");
        exit;
    }
}

// Handle logout
if ($page === 'logout') {
    session_destroy();
    header("Location: index.php?p=home");
    exit;
}

// Include header (handles auth & outputs HTML head + navbar)
include 'includes/header.php';

// Dynamic content loading
switch ($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'login':
        include 'pages/login.php';
        break;
    case 'logout':
        include 'pages/logout.php';
        break;
    case 'event_detail':
        include 'pages/event_detail.php';
        break;
    case 'tiket_pesan':
        include 'pages/tiket_pesan.php';
        break;
    case 'riwayat':
        include 'pages/riwayat.php';
        break;

    // === ADMIN ===
    case 'dashboard_admin':
        include 'pages/admin/dashboard.php';
        break;
    case 'admin_venue':
        include 'pages/admin/venue_list.php';
        break;
    case 'admin_venue_tambah':
        include 'pages/admin/venue_tambah.php';
        break;
    case 'admin_venue_edit':
        include 'pages/admin/venue_edit.php';
        break;
    case 'admin_venue_hapus':
        include 'pages/admin/venue_hapus.php';
        break;
    case 'admin_event':
        include 'pages/admin/event_list.php';
        break;
    case 'admin_event_tambah':
        include 'pages/admin/event_tambah.php';
        break;
    case 'admin_event_edit':
        include 'pages/admin/event_edit.php';
        break;
    case 'admin_tiket':
        include 'pages/admin/tiket_list.php';
        break;
    case 'admin_tiket_tambah':
        include 'pages/admin/tiket_tambah.php';
        break;
    case 'admin_tiket_edit':
        include 'pages/admin/tiket_edit.php';
        break;
    case 'admin_voucher':
        include 'pages/admin/voucher_list.php';
        break;
    case 'admin_voucher_tambah':
        include 'pages/admin/voucher_tambah.php';
        break;
    case 'admin_voucher_edit':
        include 'pages/admin/voucher_edit.php';
        break;
    case 'admin_laporan':
        include 'pages/admin/laporan.php';
        break;
    case 'admin_checkin':
        include 'pages/admin/checkin.php';
        break;
    case 'admin_order_detail':
        include 'pages/admin/order_detail.php';
        break;
    case 'admin_order_list':
        include 'pages/admin/order_list.php';
        break;

    // === PETUGAS ===
    case 'dashboard_petugas':
        include 'pages/petugas/dashboard.php';
        break;
    case 'petugas_checkin':
        include 'pages/petugas/checkin.php';
        break;

    // === USER ===
    case 'dashboard_user':
        include 'pages/user/dashboard.php';
        break;

    default:
        include 'pages/home.php';
        break;
}

// Include footer
include 'includes/footer.php';
?>
