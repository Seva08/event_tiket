<?php
if (!isset($_GET['id'])) { header("Location: ?p=admin_order_list"); exit; }
$id_order = (int)$_GET['id'];

// Handle POST: update status order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $new_status = $_POST['new_status'];
    $allowed_status = ['paid', 'cancel', 'pending'];
    if (in_array($new_status, $allowed_status)) {
        // Generate tiket jika dikonfirmasi paid
        if ($new_status === 'paid') {
            $det = mysqli_query($conn, "SELECT id_detail, qty FROM order_detail WHERE id_order = $id_order");
            while ($d = mysqli_fetch_assoc($det)) {
                $exists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendee WHERE id_detail = {$d['id_detail']}"));
                if ($exists['c'] == 0) {
                    for ($i = 0; $i < $d['qty']; $i++) {
                        $kode = 'TKT-' . strtoupper(substr(md5(uniqid()), 0, 8));
                        mysqli_query($conn, "INSERT INTO attendee (id_detail, kode_tiket) VALUES ({$d['id_detail']}, '$kode')");
                    }
                }
            }
        }
        mysqli_query($conn, "UPDATE orders SET status = '$new_status' WHERE id_order = $id_order");
        $msg = $new_status === 'paid' ? 'Order berhasil dikonfirmasi & tiket diterbitkan!' :
               ($new_status === 'cancel' ? 'Order berhasil dibatalkan.' : 'Order dikembalikan ke pending.');
        $_SESSION['flash_success'] = $msg;
    }
    echo "<script>window.location='?p=admin_order_detail&id=$id_order';</script>";
    exit;
}

$order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT o.*, u.nama, u.email, v.kode_voucher, v.potongan FROM orders o JOIN users u ON o.id_user = u.id_user LEFT JOIN voucher v ON o.id_voucher = v.id_voucher WHERE o.id_order = $id_order"));
if (!$order) { echo "<script>alert('Order tidak ditemukan!'); window.location='?p=admin_order_list';</script>"; exit; }
$details   = mysqli_query($conn, "SELECT od.*, t.nama_tiket, t.harga, e.nama_event, e.tanggal, v2.nama_venue FROM order_detail od JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event JOIN venue v2 ON e.id_venue = v2.id_venue WHERE od.id_order = $id_order");
$attendees = mysqli_query($conn, "SELECT a.*, t.nama_tiket, e.nama_event FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE od.id_order = $id_order");
$status_badge = $order['status']=='paid' ? 'success' : ($order['status']=='pending' ? 'warning' : 'danger');
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div><?= $_SESSION['flash_success'] ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); endif; ?>
        <nav aria-label="breadcrumb" class="mb-4"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="?p=dashboard_admin">Dashboard</a></li><li class="breadcrumb-item"><a href="?p=admin_order_list">Orders</a></li><li class="breadcrumb-item active">Order #<?= $id_order ?></li></ol></nav>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h2 class="page-title"><i class="bi bi-receipt"></i> Detail Order #<?= $id_order ?></h2><p class="text-muted mb-0">Informasi lengkap transaksi</p></div>
            <span class="badge bg-<?= $status_badge ?> fs-5 px-4 py-2"><?= ucfirst($order['status']) ?></span>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4"><div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-cart"></i> Informasi Pembelian</h5></div>
                <div class="card-body"><div class="table-responsive"><table class="table table-borderless">
                    <thead class="table-light"><tr><th>Event</th><th>Tiket</th><th class="text-center">Qty</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        <?php while ($d = mysqli_fetch_assoc($details)): ?>
                        <tr><td><?= htmlspecialchars($d['nama_event']) ?><br><small class="text-muted"><?= date('d M Y', strtotime($d['tanggal'])) ?></small></td><td><?= htmlspecialchars($d['nama_tiket']) ?></td><td class="text-center"><?= $d['qty'] ?></td><td class="text-end">Rp <?= number_format($d['harga'], 0, ',', '.') ?></td><td class="text-end fw-bold">Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td></tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-group-divider">
                        <?php if ($order['kode_voucher']): ?><tr class="text-success"><td colspan="4" class="text-end"><strong>Diskon (<?= $order['kode_voucher'] ?>)</strong></td><td class="text-end">- Rp <?= number_format($order['potongan'], 0, ',', '.') ?></td></tr><?php endif; ?>
                        <tr class="table-primary"><td colspan="4" class="text-end"><h5 class="mb-0"><strong>Total Bayar</strong></h5></td><td class="text-end"><h5 class="mb-0 text-primary fw-bold">Rp <?= number_format($order['total'], 0, ',', '.') ?></h5></td></tr>
                    </tfoot>
                </table></div></div></div>

                <div class="card"><div class="card-header bg-success text-white"><h5 class="mb-0"><i class="bi bi-ticket-perforated"></i> Tiket yang Diterbitkan</h5></div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($attendees) > 0): ?>
                    <div class="row">
                        <?php while ($a = mysqli_fetch_assoc($attendees)):
                            $cb = $a['status_checkin']=='sudah' ? 'success' : 'warning';
                        ?>
                        <div class="col-md-6 mb-3"><div class="card bg-light border-0"><div class="card-body">
                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($a['nama_event']) ?></h6>
                            <p class="text-muted mb-2 small"><?= htmlspecialchars($a['nama_tiket']) ?></p>
                            <div class="bg-white rounded-3 p-2 text-center mb-2"><small class="text-muted d-block">Kode Tiket</small><h5 class="ticket-code mb-0"><?= $a['kode_tiket'] ?></h5></div>
                            <span class="badge bg-<?= $cb ?>"><i class="bi bi-<?= $a['status_checkin']=='sudah'?'check-circle':'clock' ?>"></i> <?= $a['status_checkin']=='sudah'?'Sudah':'Belum' ?> Check-in</span>
                        </div></div></div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?><div class="text-center py-4"><i class="bi bi-ticket-perforated fs-1 text-muted"></i><p class="text-muted mt-2">Tiket belum diterbitkan</p></div><?php endif; ?>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4"><div class="card-header bg-dark text-white"><h5 class="mb-0"><i class="bi bi-person"></i> Customer</h5></div>
                <div class="card-body"><h6 class="mb-0 fw-bold"><?= htmlspecialchars($order['nama']) ?></h6><small class="text-muted"><?= htmlspecialchars($order['email']) ?></small></div></div>
                <div class="card"><div class="card-header bg-secondary text-white"><h5 class="mb-0"><i class="bi bi-info-circle"></i> Info Order</h5></div>
                <div class="card-body">
                    <p class="mb-2"><strong>ID:</strong> #<?= $order['id_order'] ?></p>
                    <p class="mb-2"><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($order['tanggal_order'])) ?></p>
                    <p class="mb-0"><strong>Status:</strong> <span class="badge bg-<?= $status_badge ?>"><?= ucfirst($order['status']) ?></span></p>
                </div></div>
                <!-- Action Buttons -->
                <div class="mt-4">
                    <?php if ($order['status'] === 'pending'): ?>
                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning text-dark fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>Menunggu Verifikasi</div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">Konfirmasi setelah menerima bukti transfer dari customer.</p>
                            <form method="POST" action="?p=admin_order_detail&id=<?= $id_order ?>" onsubmit="return confirm('Konfirmasi order #<?= $id_order ?> sebagai PAID? Tiket akan diterbitkan otomatis.')">
                                <input type="hidden" name="new_status" value="paid">
                                <button type="submit" class="btn btn-success w-100 mb-2">
                                    <i class="bi bi-check-circle-fill me-2"></i>Konfirmasi Paid
                                </button>
                            </form>
                            <form method="POST" action="?p=admin_order_detail&id=<?= $id_order ?>" onsubmit="return confirm('Batalkan order #<?= $id_order ?>?')">
                                <input type="hidden" name="new_status" value="cancel">
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-x-circle me-2"></i>Batalkan Order
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php elseif ($order['status'] === 'paid'): ?>
                    <form method="POST" action="?p=admin_order_detail&id=<?= $id_order ?>" onsubmit="return confirm('Refund/batalkan order yang sudah PAID?')">
                        <input type="hidden" name="new_status" value="cancel">
                        <button type="submit" class="btn btn-outline-danger w-100 mb-2">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Refund / Batalkan
                        </button>
                    </form>
                    <?php elseif ($order['status'] === 'cancel'): ?>
                    <form method="POST" action="?p=admin_order_detail&id=<?= $id_order ?>" onsubmit="return confirm('Kembalikan order ke status Pending?')">
                        <input type="hidden" name="new_status" value="pending">
                        <button type="submit" class="btn btn-outline-warning w-100 mb-2">
                            <i class="bi bi-arrow-clockwise me-2"></i>Kembalikan ke Pending
                        </button>
                    </form>
                    <?php endif; ?>
                    <a href="?p=admin_order_list" class="btn btn-outline-secondary w-100 mt-1">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Orders
                    </a>
                </div>
            </div>
        </div>
    </main>
</div></div>
