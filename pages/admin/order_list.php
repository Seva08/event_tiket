<?php
// Handle AJAX update status
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id_order  = (int)$_POST['id_order'];
    $new_status = $_POST['new_status'];
    $allowed_status = ['paid', 'cancel', 'pending'];

    if (!in_array($new_status, $allowed_status)) {
        echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
        exit;
    }

    // Kalau paid, generate tiket di attendee jika belum ada
    if ($new_status === 'paid') {
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
    }

    $update = mysqli_query($conn, "UPDATE orders SET status = '$new_status' WHERE id_order = $id_order");
    if ($update) {
        echo json_encode(['success' => true, 'message' => 'Status berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal update status']);
    }
    exit;
}

// Filter status
$filter_status = isset($_GET['status']) && in_array($_GET['status'], ['all', 'pending', 'paid', 'cancel']) ? $_GET['status'] : 'all';
$where = $filter_status !== 'all' ? "WHERE o.status = '$filter_status'" : '';

// Counts per status
$cnt_all     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'];
$cnt_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='pending'"))['c'];
$cnt_paid    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='paid'"))['c'];
$cnt_cancel  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='cancel'"))['c'];

$orders = mysqli_query($conn, "
    SELECT o.id_order, o.tanggal_order, o.total, o.status,
           u.nama, u.email,
           GROUP_CONCAT(e.nama_event SEPARATOR ', ') as events,
           SUM(od.qty) as total_qty,
           v.kode_voucher
    FROM orders o
    JOIN users u ON o.id_user = u.id_user
    LEFT JOIN order_detail od ON o.id_order = od.id_order
    LEFT JOIN tiket t ON od.id_tiket = t.id_tiket
    LEFT JOIN event e ON t.id_event = e.id_event
    LEFT JOIN voucher v ON o.id_voucher = v.id_voucher
    $where
    GROUP BY o.id_order
    ORDER BY o.tanggal_order DESC
");
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="page-title"><i class="bi bi-receipt-cutoff"></i> Manajemen Order</h2>
                <p class="text-muted mb-0">Verifikasi pembayaran & kelola status order</p>
            </div>
            <div class="text-end">
                <small class="text-muted d-block">Total Order</small>
                <span class="fs-3 fw-bold text-primary"><?= $cnt_all ?></span>
            </div>
        </div>

        <!-- Alert toast container -->
        <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index:9999"></div>

        <!-- Status Filter Tabs -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="d-flex flex-wrap">
                    <?php
                    $tabs = [
                        ['status' => 'all',     'label' => 'Semua',   'icon' => 'bi-list-ul',        'count' => $cnt_all,     'color' => 'primary'],
                        ['status' => 'pending', 'label' => 'Pending', 'icon' => 'bi-clock-history',  'count' => $cnt_pending, 'color' => 'warning'],
                        ['status' => 'paid',    'label' => 'Paid',    'icon' => 'bi-check-circle',   'count' => $cnt_paid,    'color' => 'success'],
                        ['status' => 'cancel',  'label' => 'Cancel',  'icon' => 'bi-x-circle',       'count' => $cnt_cancel,  'color' => 'danger'],
                    ];
                    foreach ($tabs as $tab):
                        $active = $filter_status === $tab['status'] ? 'active' : '';
                    ?>
                    <a href="?p=admin_order_list&status=<?= $tab['status'] ?>"
                       class="order-tab-btn flex-grow-1 text-center py-3 px-4 text-decoration-none border-0 <?= $active ?>"
                       data-color="<?= $tab['color'] ?>">
                        <i class="bi <?= $tab['icon'] ?> d-block fs-5 mb-1"></i>
                        <div class="fw-semibold"><?= $tab['label'] ?></div>
                        <span class="badge bg-<?= $tab['color'] ?> mt-1"><?= $tab['count'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3"
                 style="background: linear-gradient(135deg, var(--g-primary), var(--g-secondary)); color:#fff;">
                <h5 class="mb-0">
                    <i class="bi bi-table me-2"></i>
                    Daftar Order
                    <?php if ($filter_status !== 'all'): ?>
                        — <span class="text-warning"><?= ucfirst($filter_status) ?></span>
                    <?php endif; ?>
                </h5>
                <?php if ($cnt_pending > 0): ?>
                <span class="badge bg-warning text-dark fs-6 px-3 py-2 d-flex align-items-center gap-1">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?= $cnt_pending ?> menunggu verifikasi
                </span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="orderTable">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">#Order</th>
                                <th>Customer</th>
                                <th>Event</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                                <th>Voucher</th>
                                <th class="text-center">Status</th>
                                <th>Tanggal</th>
                                <th class="text-center pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($orders) === 0): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada order <?= $filter_status !== 'all' ? "dengan status <strong>$filter_status</strong>" : '' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php while ($row = mysqli_fetch_assoc($orders)):
                            $badge = $row['status'] === 'paid' ? 'success' : ($row['status'] === 'pending' ? 'warning' : 'danger');
                            $rowClass = $row['status'] === 'pending' ? 'table-warning-subtle' : '';
                        ?>
                        <tr class="order-row <?= $rowClass ?>" id="row-<?= $row['id_order'] ?>">
                            <td class="ps-3">
                                <span class="fw-bold text-primary">#<?= $row['id_order'] ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle bg-primary">
                                        <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small"><?= htmlspecialchars($row['nama']) ?></div>
                                        <div class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($row['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= htmlspecialchars($row['events'] ?? '-') ?></div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill"><?= $row['total_qty'] ?? 0 ?> tiket</span>
                            </td>
                            <td class="text-end fw-bold">
                                Rp <?= number_format($row['total'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <?php if ($row['kode_voucher']): ?>
                                    <span class="badge bg-info text-dark"><i class="bi bi-tag-fill me-1"></i><?= $row['kode_voucher'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $badge ?> status-badge px-3 py-2" id="badge-<?= $row['id_order'] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted"><?= date('d M Y', strtotime($row['tanggal_order'])) ?></small>
                                <br><small class="text-muted" style="font-size:.7rem"><?= date('H:i', strtotime($row['tanggal_order'])) ?></small>
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex gap-1 justify-content-center flex-wrap">
                                    <a href="?p=admin_order_detail&id=<?= $row['id_order'] ?>"
                                       class="btn btn-sm btn-outline-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($row['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-success btn-verify"
                                            onclick="updateStatus(<?= $row['id_order'] ?>, 'paid', this)"
                                            title="Konfirmasi Pembayaran">
                                        <i class="bi bi-check-lg"></i> Paid
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-cancel"
                                            onclick="updateStatus(<?= $row['id_order'] ?>, 'cancel', this)"
                                            title="Batalkan Order">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <?php elseif ($row['status'] === 'paid'): ?>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="updateStatus(<?= $row['id_order'] ?>, 'cancel', this)"
                                            title="Batalkan (refund)">
                                        <i class="bi bi-arrow-counterclockwise"></i> Refund
                                    </button>
                                    <?php elseif ($row['status'] === 'cancel'): ?>
                                    <button class="btn btn-sm btn-outline-warning"
                                            onclick="updateStatus(<?= $row['id_order'] ?>, 'pending', this)"
                                            title="Kembalikan ke Pending">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div></div>

<style>
.order-tab-btn {
    color: #64748b;
    background: #fff;
    border-right: 1px solid #e2e8f0 !important;
    transition: all .2s ease;
}
.order-tab-btn:last-child { border-right: none !important; }
.order-tab-btn:hover { background: #f8fafc; color: #1e293b; }
.order-tab-btn.active {
    background: linear-gradient(135deg, var(--g-primary), var(--g-secondary));
    color: #fff !important;
}
.order-tab-btn.active .badge { background: rgba(255,255,255,.25) !important; color:#fff !important; }
.table-warning-subtle { background: #fffbeb !important; }
.avatar-circle {
    width: 32px; height: 32px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-size: .75rem; font-weight: 700; flex-shrink: 0;
}
.btn-verify { animation: pulse-green 2s infinite; }
@keyframes pulse-green {
    0%, 100% { box-shadow: 0 0 0 0 rgba(25,135,84,.4); }
    50% { box-shadow: 0 0 0 6px rgba(25,135,84,0); }
}
.status-badge { font-size: .8rem; min-width: 68px; }
</style>

<script>
function updateStatus(id_order, new_status, btn) {
    const labels = { paid: 'KONFIRMASI PAID', cancel: 'CANCEL order', pending: 'kembalikan ke PENDING' };
    const icons  = { paid: '✅', cancel: '❌', pending: '🔄' };

    if (!confirm(`${icons[new_status]} Yakin ingin ${labels[new_status]} #${id_order}?`)) return;

    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id_order', id_order);
    formData.append('new_status', new_status);

    fetch('?p=admin_order_list', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Refresh baris setelah 800ms biar animasi selesai
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.message, 'danger');
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        })
        .catch(() => {
            showToast('Terjadi kesalahan jaringan', 'danger');
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
}

function showToast(message, type = 'success') {
    const id = 'toast-' + Date.now();
    const html = `
    <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>`;
    document.getElementById('toastContainer').insertAdjacentHTML('beforeend', html);
    const el = document.getElementById(id);
    const toast = new bootstrap.Toast(el, { delay: 3000 });
    toast.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
}
</script>
