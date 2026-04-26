<?php
// Validasi login & role
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    header("Location: ?p=login");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: ?p=riwayat");
    exit;
}

$id_order = (int)$_GET['id'];
$id_user  = $_SESSION['id_user'];

// Ambil data order lengkap
$q_order = mysqli_query($conn, 
    "SELECT o.*, od.qty, t.nama_tiket, t.harga as harga_satuan, e.nama_event, e.gambar, v.kode_voucher, v.potongan 
     FROM orders o 
     JOIN order_detail od ON o.id_order = od.id_order
     JOIN tiket t ON od.id_tiket = t.id_tiket
     JOIN event e ON t.id_event = e.id_event
     LEFT JOIN voucher v ON o.id_voucher = v.id_voucher 
     WHERE o.id_order = $id_order AND o.id_user = $id_user");
$order = mysqli_fetch_assoc($q_order);

// Validasi
if (!$order || strtolower($order['status']) !== 'pending') {
    $_SESSION['alert'] = [
        'type' => 'warning',
        'title' => 'Sudah Diproses',
        'text' => 'Pesanan ini sudah dibayar atau tidak ditemukan.'
    ];
    echo "<script>window.location='?p=riwayat';</script>";
    exit;
}

// Proses Aksi
if (isset($_POST['aksi'])) {
    if ($_POST['aksi'] === 'bayar') {
        $id_detail = 0;
        $q_det = mysqli_query($conn, "SELECT id_detail FROM order_detail WHERE id_order = $id_order");
        if($d = mysqli_fetch_assoc($q_det)) $id_detail = $d['id_detail'];

        $check_attendee = mysqli_query($conn, "SELECT id_attendee FROM attendee WHERE id_detail = $id_detail");
        if (mysqli_num_rows($check_attendee) == 0) {
            for ($i = 0; $i < $order['qty']; $i++) {
                $kode = 'TKT-' . strtoupper(substr(md5(uniqid($id_order, true)), 0, 10));
                mysqli_query($conn, "INSERT INTO attendee (id_detail, kode_tiket, status_checkin) VALUES ($id_detail, '$kode', 'belum')");
            }
        }

        // UPDATE TANPA TANGGAL_PEMBAYARAN
        mysqli_query($conn, "UPDATE orders SET status = 'paid' WHERE id_order = $id_order");
        
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Pembayaran Berhasil!',
            'text' => 'Tiket kamu sudah aktif. Silakan cek di menu Tiket Saya.'
        ];
        header("Location: ?p=riwayat#tiket");
        exit;
    } elseif ($_POST['aksi'] === 'cancel') {
        // Balikin kuota
        if ($order['id_voucher']) {
            mysqli_query($conn, "UPDATE voucher SET kuota = kuota + 1 WHERE id_voucher = " . $order['id_voucher']);
        }
        mysqli_query($conn, "UPDATE tiket SET kuota = kuota + " . $order['qty'] . " WHERE id_tiket = (SELECT id_tiket FROM order_detail WHERE id_order = $id_order LIMIT 1)");
        mysqli_query($conn, "UPDATE orders SET status = 'cancel' WHERE id_order = $id_order");
        
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Dibatalkan',
            'text' => 'Pesanan berhasil dibatalkan.'
        ];
        header("Location: ?p=riwayat");
        exit;
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11 col-xl-9">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item small"><a href="?p=home" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item small"><a href="?p=riwayat" class="text-decoration-none">Riwayat</a></li>
                    <li class="breadcrumb-item small active">Checkout</li>
                </ol>
            </nav>

            <div class="row g-4">
                <!-- Left: Payment Instructions -->
                <div class="col-md-5 order-2 order-md-1">
                    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden mb-4">
                        <div class="card-body p-4 position-relative z-1">
                            <h5 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-wallet2 me-2"></i>Instruksi Pembayaran
                            </h5>
                            <div class="bg-white bg-opacity-10 rounded-4 p-4 border border-white border-opacity-10 mb-4">
                                <small class="opacity-75 d-block mb-1">Transfer ke Rekening BCA</small>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h2 class="fw-bold mb-0 tracking-widest">1234567890</h2>
                                    <button class="btn btn-sm btn-light rounded-pill px-3 fw-bold" onclick="copyToClipboard('1234567890')">Salin</button>
                                </div>
                                <small class="fw-bold">a.n YuiPass Event Management</small>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="d-flex gap-3">
                                    <div class="bg-white bg-opacity-20 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.8rem;">1</div>
                                    <p class="small mb-0">Lakukan transfer ATM / M-Banking sesuai nominal total bayar.</p>
                                </div>
                                <div class="d-flex gap-3">
                                    <div class="bg-white bg-opacity-20 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.8rem;">2</div>
                                    <p class="small mb-0">Setelah transfer, klik tombol <strong>Konfirmasi Bayar</strong> di samping.</p>
                                </div>
                                <div class="d-flex gap-3">
                                    <div class="bg-white bg-opacity-20 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.8rem;">3</div>
                                    <p class="small mb-0">Sistem akan memvalidasi pembayaran dan tiket akan aktif otomatis.</p>
                                </div>
                            </div>
                        </div>
                        <i class="bi bi-shield-check position-absolute end-0 bottom-0 opacity-10 display-1 m-n3"></i>
                    </div>

                    <div class="text-center p-3">
                        <p class="text-muted small mb-0">Ada kendala? <a href="#" class="fw-bold text-primary text-decoration-none">Hubungi Kami</a></p>
                    </div>
                </div>

                <!-- Right: Order Details -->
                <div class="col-md-7 order-1 order-md-2">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="fw-bold mb-4">Rincian Pesanan</h5>
                            
                            <div class="d-flex gap-3 mb-4">
                                <div class="flex-shrink-0" style="width: 100px; height: 100px;">
                                    <img src="<?= $order['gambar'] ? 'uploads/'.$order['gambar'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=400&q=80' ?>" 
                                         class="w-100 h-100 object-fit-cover rounded-4 border shadow-sm" alt="Event">
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($order['nama_event']) ?></h5>
                                    <div class="text-primary fw-bold mb-1 small text-uppercase ls-1"><?= htmlspecialchars($order['nama_tiket']) ?></div>
                                    <div class="text-muted small">Pesanan #<?= $order['id_order'] ?></div>
                                </div>
                            </div>

                            <div class="bg-light rounded-4 p-4 mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal (<?= $order['qty'] ?> Tiket)</span>
                                    <span class="fw-bold">Rp <?= number_format($order['harga_satuan'] * $order['qty'], 0, ',', '.') ?></span>
                                </div>
                                <?php if($order['potongan'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-success fw-bold">
                                    <span>Voucher: <?= $order['kode_voucher'] ?></span>
                                    <span>- Rp <?= number_format($order['potongan'], 0, ',', '.') ?></span>
                                </div>
                                <?php endif; ?>
                                <hr class="my-3 border-dashed opacity-10">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Total Bayar</h5>
                                    <h3 class="fw-bold text-primary mb-0">Rp <?= number_format($order['total'], 0, ',', '.') ?></h3>
                                </div>
                            </div>

                            <form method="POST" id="form-bayar">
                                <div class="d-grid gap-3">
                                    <button type="button" onclick="confirmAction('bayar')" class="btn btn-success py-3 rounded-pill fw-bold shadow-sm fs-5">
                                        <i class="bi bi-check-circle me-2"></i>Konfirmasi Bayar
                                    </button>
                                    <button type="button" onclick="confirmAction('cancel')" class="btn btn-white border rounded-pill py-2 fw-bold text-danger">
                                        Batalkan Pesanan
                                    </button>
                                </div>
                                <input type="hidden" name="aksi" id="input-aksi" value="">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-dashed { border-style: dashed !important; border-width: 1px !important; }
.tracking-widest { letter-spacing: 0.2rem; }
.space-y-4 > * + * { margin-top: 1.5rem; }
.ls-1 { letter-spacing: 1px; }
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Nomor rekening disalin!', showConfirmButton: false, timer: 1500 });
    });
}

function confirmAction(type) {
    const isBayar = type === 'bayar';
    Swal.fire({
        title: isBayar ? 'Konfirmasi Bayar?' : 'Batalkan Pesanan?',
        text: isBayar ? 'Pastikan Anda sudah transfer sesuai nominal.' : 'Pesanan akan dibatalkan permanen.',
        icon: isBayar ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: isBayar ? '#198754' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: isBayar ? 'Ya, Sudah Bayar' : 'Ya, Batalkan',
        cancelButtonText: 'Kembali',
        reverseButtons: true,
        customClass: { confirmButton: 'rounded-pill px-4 py-2', cancelButton: 'rounded-pill px-4 py-2' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('input-aksi').value = type;
            if(isBayar) {
                Swal.fire({ title: 'Memproses...', text: 'Sedang memvalidasi pembayaran', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            }
            document.getElementById('form-bayar').submit();
        }
    });
}
</script>
