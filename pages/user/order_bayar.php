<?php
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    $_SESSION['alert'] = [
        'type' => 'warning',
        'title' => 'Akses Ditolak',
        'text' => 'Hanya user yang dapat mengakses halaman ini!'
    ];
    header("Location: ?p=login");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: ?p=riwayat");
    exit;
}

$id_order = (int)$_GET['id'];
$id_user  = $_SESSION['id_user'];

$q_order = mysqli_query($conn, 
    "SELECT o.*, v.kode_voucher, v.potongan 
     FROM orders o 
     LEFT JOIN voucher v ON o.id_voucher = v.id_voucher 
     WHERE o.id_order = $id_order AND o.id_user = $id_user AND o.status = 'pending'");
$order = mysqli_fetch_assoc($q_order);

if (!$order) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Gagal',
        'text' => 'Pesanan tidak ditemukan atau sudah diproses!'
    ];
    header("Location: ?p=riwayat");
    exit;
}

if (isset($_POST['aksi'])) {
    if ($_POST['aksi'] === 'cancel') {
        if (!empty($order['id_voucher'])) {
            mysqli_query($conn, "UPDATE voucher SET kuota = kuota + 1, status = 'aktif' WHERE id_voucher = {$order['id_voucher']}");
        }

        $details = mysqli_query($conn, "SELECT id_tiket, qty FROM order_detail WHERE id_order = $id_order");
        while ($d = mysqli_fetch_assoc($details)) {
            mysqli_query($conn, "UPDATE tiket SET kuota = kuota + {$d['qty']} WHERE id_tiket = {$d['id_tiket']}");
        }

        mysqli_query($conn, "UPDATE orders SET status = 'cancel' WHERE id_order = $id_order");
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Pesanan Dibatalkan',
            'text' => 'Pesanan berhasil dibatalkan. Kuota tiket telah dikembalikan.'
        ];
        header("Location: ?p=riwayat");
        exit;
    } elseif ($_POST['aksi'] === 'bayar') {
        $details = mysqli_query($conn, "SELECT od.id_detail, od.qty FROM order_detail od WHERE od.id_order = $id_order");
        while ($d = mysqli_fetch_assoc($details)) {
            $existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM attendee WHERE id_detail = {$d['id_detail']}"));
            if ($existing['cnt'] == 0) {
                for ($i = 0; $i < $d['qty']; $i++) {
                    $kode = 'TKT-' . strtoupper(substr(md5(uniqid()), 0, 8));
                    mysqli_query($conn, "INSERT INTO attendee (id_detail, kode_tiket) VALUES ({$d['id_detail']}, '$kode')");
                }
            }
        }
        mysqli_query($conn, "UPDATE orders SET status = 'paid' WHERE id_order = $id_order");
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Pembayaran Berhasil!',
            'text' => 'Terima kasih! Tiket kamu sudah otomatis dicetak.'
        ];
        header("Location: ?p=riwayat");
        exit;
    }
}
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=home">Home</a></li>
            <li class="breadcrumb-item"><a href="?p=riwayat">Riwayat</a></li>
            <li class="breadcrumb-item active">Pembayaran</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="bg-primary p-4 text-white text-center">
                    <i class="bi bi-wallet2 fs-1 mb-2"></i>
                    <h4 class="fw-bold mb-0">Pembayaran Pesanan</h4>
                    <p class="mb-0 small opacity-75">#<?= $order['id_order'] ?></p>
                </div>
                <div class="card-body p-4 bg-light">
                    <div class="text-center mb-4">
                        <p class="text-muted mb-1">Total yang harus dibayar</p>
                        <h2 class="fw-bold text-dark">Rp <?= number_format($order['total'], 0, ',', '.') ?></h2>
                    </div>

                    <div class="alert alert-info rounded-3">
                        <i class="bi bi-info-circle-fill me-2"></i>Silakan transfer ke rekening berikut:<br>
                        <strong>BCA: 1234567890 a.n YuiPass</strong>
                    </div>

                    <form method="POST" action="">
                        <div class="d-grid gap-3">
                            <button type="submit" name="aksi" value="bayar" class="btn btn-success py-3 fw-bold rounded-pill shadow-sm fs-5">
                                <i class="bi bi-check-circle me-2"></i>Bayar Sekarang
                            </button>
                            <button type="submit" name="aksi" value="cancel" class="btn btn-outline-danger py-2 fw-bold rounded-pill" onclick="return confirm('Yakin ingin membatalkan pesanan ini? Kuota tiket akan dikembalikan ke sistem.');">
                                <i class="bi bi-x-circle me-2"></i>Batalkan Pesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
