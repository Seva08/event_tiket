<?php
ob_start();
session_start();
include 'config.php';

$expired_orders = mysqli_query($conn, "SELECT id_order, id_voucher FROM orders WHERE status = 'pending' AND tanggal_order < NOW() - INTERVAL 24 HOUR");
if (mysqli_num_rows($expired_orders) > 0) {
    while ($eo = mysqli_fetch_assoc($expired_orders)) {
        if (!empty($eo['id_voucher'])) {
            mysqli_query($conn, "UPDATE voucher SET kuota = kuota + 1 WHERE id_voucher = " . $eo['id_voucher']);
        }
        
        $id_ord = $eo['id_order'];
        $q_det = mysqli_query($conn, "SELECT id_tiket, qty FROM order_detail WHERE id_order = $id_ord");
        while ($det = mysqli_fetch_assoc($q_det)) {
            mysqli_query($conn, "UPDATE tiket SET kuota = kuota + " . $det['qty'] . " WHERE id_tiket = " . $det['id_tiket']);
        }
    }
    mysqli_query($conn, "UPDATE orders SET status = 'cancel' WHERE status = 'pending' AND tanggal_order < NOW() - INTERVAL 24 HOUR");
}

$page = isset($_GET['p']) ? $_GET['p'] : 'home';

$allowed_pages = [
    'home', 'login', 'logout', 'register',
    'event_detail', 'tiket_pesan', 'riwayat', 'order_bayar', 'tiket_print',
    'dashboard_admin', 'dashboard_user', 'dashboard_petugas',
    'admin_venue', 'admin_venue_tambah', 'admin_venue_edit', 'admin_venue_hapus',
    'admin_event', 'admin_event_tambah', 'admin_event_edit',
    'admin_tiket', 'admin_tiket_tambah', 'admin_tiket_edit',
    'admin_voucher', 'admin_voucher_tambah', 'admin_voucher_edit',
    'admin_laporan', 'admin_checkin', 'admin_order_detail', 'admin_order_list',
    'petugas_checkin', 'profile'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

if ($page === 'login' && isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $q        = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $data     = mysqli_fetch_assoc($q);
    
    if ($data) {
        $login_success = false;
        
        if (password_verify($password, $data['password'])) {
            $login_success = true;
        } elseif (md5($password) === $data['password']) {
            $login_success = true;
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$new_hash' WHERE id_user=" . $data['id_user']);
        }
        
        if ($login_success) {
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['role']    = $data['role'];
            $_SESSION['nama']    = $data['nama'];
            $_SESSION['email']   = $data['email'];
            if ($data['role'] == 'admin') {
                header("Location: index.php?p=dashboard_admin");
            } elseif ($data['role'] == 'petugas') {
                header("Location: index.php?p=dashboard_petugas");
            } else {
                header("Location: index.php?p=dashboard_user");
            }
            exit;
        }
    }
    
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Login Gagal',
        'text' => 'Email atau Password Salah!'
    ];
    header("Location: index.php?p=login");
    exit;
}

if ($page === 'register' && isset($_POST['register'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = 'user'; 

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['alert'] = [
            'type' => 'warning',
            'title' => 'Email Terdaftar',
            'text' => 'Email sudah terdaftar! Gunakan email lain.'
        ];
        header("Location: index.php?p=register");
        exit;
    } else {
        mysqli_query($conn, "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', '$role')");
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Registrasi Berhasil',
            'text' => 'Akun Anda telah dibuat. Silakan login.'
        ];
        header("Location: index.php?p=login");
        exit;
    }
}

if ($page === 'logout') {
    session_destroy();
    header("Location: index.php?p=home");
    exit;
}

if ($page === 'tiket_print') {
    include 'pages/user/tiket_print.php';
    exit;
}

include 'includes/header.php';

switch ($page) {
    case 'home':             include 'pages/home.php'; break;
    case 'login':            include 'pages/login.php'; break;
    case 'register':         include 'pages/register.php'; break;
    case 'logout':           include 'pages/logout.php'; break;
    case 'event_detail':     include 'pages/user/event_detail.php'; break;
    case 'tiket_pesan':      include 'pages/user/tiket_pesan.php'; break;
    case 'riwayat':          include 'pages/user/riwayat.php'; break;
    case 'order_bayar':      include 'pages/user/order_bayar.php'; break;
    case 'profile':          include 'pages/user/profile.php'; break;

    case 'dashboard_admin':  include 'pages/admin/dashboard.php'; break;
    case 'admin_venue':      include 'pages/admin/venue_list.php'; break;
    case 'admin_venue_tambah': include 'pages/admin/venue_tambah.php'; break;
    case 'admin_venue_edit':   include 'pages/admin/venue_edit.php'; break;
    case 'admin_venue_hapus':  include 'pages/admin/venue_hapus.php'; break;
    case 'admin_event':      include 'pages/admin/event_list.php'; break;
    case 'admin_event_tambah': include 'pages/admin/event_tambah.php'; break;
    case 'admin_event_edit':   include 'pages/admin/event_edit.php'; break;
    case 'admin_tiket':      include 'pages/admin/tiket_list.php'; break;
    case 'admin_tiket_tambah': include 'pages/admin/tiket_tambah.php'; break;
    case 'admin_tiket_edit':   include 'pages/admin/tiket_edit.php'; break;
    case 'admin_voucher':    include 'pages/admin/voucher_list.php'; break;
    case 'admin_voucher_tambah': include 'pages/admin/voucher_tambah.php'; break;
    case 'admin_voucher_edit':   include 'pages/admin/voucher_edit.php'; break;
    case 'admin_laporan':    include 'pages/admin/laporan.php'; break;
    case 'admin_checkin':    include 'pages/admin/checkin.php'; break;
    case 'admin_order_detail': include 'pages/admin/order_detail.php'; break;
    case 'admin_order_list':   include 'pages/admin/order_list.php'; break;

    case 'dashboard_petugas': include 'pages/petugas/dashboard.php'; break;
    case 'petugas_checkin':   include 'pages/petugas/checkin.php'; break;

    case 'dashboard_user':    include 'pages/user/dashboard.php'; break;

    default:                  include 'pages/home.php'; break;
}

include 'includes/footer.php';
?>
