<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak!";
    exit;
}

$id_order = (int)($_GET['id'] ?? 0);
$order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT o.*, u.nama, u.email, v.kode_voucher, v.potongan FROM orders o JOIN users u ON o.id_user = u.id_user LEFT JOIN voucher v ON o.id_voucher = v.id_voucher WHERE o.id_order = $id_order"));

if (!$order) {
    echo "Order tidak ditemukan!";
    exit;
}

$details = mysqli_query($conn, "SELECT od.*, t.nama_tiket, t.harga, e.nama_event, e.tanggal, v2.nama_venue FROM order_detail od JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event JOIN venue v2 ON e.id_venue = v2.id_venue WHERE od.id_order = $id_order");
$attendees = mysqli_query($conn, "SELECT a.*, t.nama_tiket, e.nama_event FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE od.id_order = $id_order");
$status_badge = $order['status']=='paid' ? 'success' : ($order['status']=='pending' ? 'warning' : 'danger');
?>

<div class="text-start">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">Order #<?= $id_order ?></h5>
            <small class="text-muted"><?= date('d M Y, H:i', strtotime($order['tanggal_order'])) ?></small>
        </div>
        <span class="badge bg-<?= $status_badge ?> fs-6"><?= ucfirst($order['status']) ?></span>
    </div>

    <div class="card bg-light border-0 mb-3">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-6">
                    <small class="text-muted d-block">Pembeli</small>
                    <div class="fw-bold"><?= htmlspecialchars($order['nama']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($order['email']) ?></small>
                </div>
                <div class="col-6 text-end">
                    <small class="text-muted d-block">Total Pembayaran</small>
                    <h5 class="fw-bold text-primary mb-0">Rp <?= number_format($order['total'], 0, ',', '.') ?></h5>
                </div>
            </div>
        </div>
    </div>

    <h6 class="fw-bold mb-2">Item Pesanan</h6>
    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered">
            <thead class="bg-light">
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Harga</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($d = mysqli_fetch_assoc($details)): ?>
                <tr>
                    <td><?= htmlspecialchars($d['nama_event']) ?> - <?= htmlspecialchars($d['nama_tiket']) ?></td>
                    <td class="text-center"><?= $d['qty'] ?></td>
                    <td class="text-end">Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                    <td class="text-end fw-bold">Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <?php if ($order['kode_voucher']): ?>
            <tfoot>
                <tr class="text-success small">
                    <td colspan="3" class="text-end">Voucher (<?= $order['kode_voucher'] ?>)</td>
                    <td class="text-end">- Rp <?= number_format($order['potongan'], 0, ',', '.') ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <?php if (mysqli_num_rows($attendees) > 0): ?>
    <h6 class="fw-bold mb-2">Tiket Diterbitkan</h6>
    <div class="row g-2">
        <?php while ($a = mysqli_fetch_assoc($attendees)): ?>
        <div class="col-6">
            <div class="p-2 border rounded bg-white text-center">
                <small class="text-muted d-block" style="font-size: 0.65rem;">Kode Tiket</small>
                <div class="fw-bold small text-primary" style="letter-spacing: 1px;"><?= $a['kode_tiket'] ?></div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>
