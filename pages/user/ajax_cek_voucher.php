<?php
include '../../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

$id_user = $_SESSION['id_user'];
$kode = mysqli_real_escape_string($conn, $_GET['kode'] ?? '');
$subtotal = (int)($_GET['subtotal'] ?? 0);

if (empty($kode)) {
    echo json_encode(['status' => 'error', 'message' => 'Masukkan kode voucher!']);
    exit;
}

$q = mysqli_query($conn, "SELECT * FROM voucher WHERE kode_voucher = '$kode' AND status = 'aktif'");
$v = mysqli_fetch_assoc($q);

if (!$v) {
    echo json_encode(['status' => 'error', 'message' => 'Kode voucher tidak valid atau sudah tidak aktif.']);
    exit;
}

// Cek kuota
if ($v['kuota'] <= 0 && $v['kuota'] != 0) { // Asumsi 0 adalah unlimited jika ada logic itu, tapi di sini kuota > 0
    echo json_encode(['status' => 'error', 'message' => 'Maaf, kuota voucher ini sudah habis.']);
    exit;
}

// Cek pemakaian user
$q_used = mysqli_query($conn, "SELECT id_order FROM orders WHERE id_user = $id_user AND id_voucher = {$v['id_voucher']} AND status != 'cancel'");
if (mysqli_num_rows($q_used) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Anda sudah pernah menggunakan kode voucher ini.']);
    exit;
}

// Cek nominal
if ($v['potongan'] >= $subtotal) {
    echo json_encode(['status' => 'error', 'message' => 'Nominal voucher melebihi total harga tiket.']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Voucher berhasil diterapkan!',
    'potongan' => (int)$v['potongan'],
    'potongan_fmt' => number_format($v['potongan'], 0, ',', '.')
]);
