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

    // Cek status saat ini
    $curr = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM orders WHERE id_order = $id_order"));
    if ($curr['status'] === 'paid') {
        echo json_encode(['success' => false, 'message' => 'Order yang sudah PAID tidak dapat diubah lagi!']);
        exit;
    }
    if ($curr['status'] === 'cancel' && $new_status === 'pending') {
        echo json_encode(['success' => false, 'message' => 'Order yang sudah CANCEL tidak dapat dikembalikan ke Pending!']);
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

// Filter & Search
$filter_status = isset($_GET['status']) && in_array($_GET['status'], ['all', 'pending', 'paid', 'cancel']) ? $_GET['status'] : 'all';
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';

$where_clauses = [];
if ($filter_status !== 'all') {
    $where_clauses[] = "o.status = '$filter_status'";
}
if (!empty($q)) {
    $where_clauses[] = "(u.nama LIKE '%$q%' OR u.email LIKE '%$q%' OR o.id_order LIKE '%$q%')";
}
$where = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : '';

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Global counts (for the tabs)
$cnt_all     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'];
$cnt_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='pending'"))['c'];
$cnt_paid    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='paid'"))['c'];
$cnt_cancel  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='cancel'"))['c'];

// Count total filtered for pagination
$total_filtered = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders o JOIN users u ON o.id_user = u.id_user $where"))['c'];
$total_pages = ceil($total_filtered / $limit);

$orders = mysqli_query($conn, "
    SELECT o.id_order, o.tanggal_order, o.total, o.status,
           u.nama, u.email,
           GROUP_CONCAT(DISTINCT e.nama_event SEPARATOR ', ') as events,
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
    LIMIT $limit OFFSET $offset
");
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">

        <!-- Page Header -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                        <i class="bi bi-receipt-cutoff fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-0">Manajemen Order</h2>
                        <p class="text-muted mb-0 small">Verifikasi & kelola transaksi masuk</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <form method="GET" action="index.php" class="position-relative">
                    <input type="hidden" name="p" value="admin_order_list">
                    <input type="hidden" name="status" value="<?= $filter_status ?>">
                    <input type="text" name="q" class="form-control shadow-sm ps-5 py-2 fw-medium rounded-3" 
                           placeholder="Cari ID, nama, atau email..." value="<?= htmlspecialchars($q) ?>">
                    <i class="bi bi-search position-absolute text-muted fs-5 ms-3 top-50 translate-middle-y"></i>
                    <?php if($q): ?>
                        <a href="?p=admin_order_list&status=<?= $filter_status ?>" class="position-absolute text-muted me-3 top-50 translate-middle-y end-0">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-2 text-md-end">
                <div class="p-2 px-3 bg-white shadow-sm rounded-3 d-inline-block border">
                    <small class="text-muted d-block small text-uppercase fw-bold">Total</small>
                    <span class="fs-5 fw-bold text-dark"><?= $cnt_all ?></span>
                </div>
            </div>
        </div>

        <!-- Alert toast container -->
        <div id="toastContainer" class="position-fixed top-0 end-0 p-3 z-3"></div>

        <!-- Status Filter Tabs -->
        <div class="mb-4">
            <div class="d-flex flex-wrap gap-2 p-1 bg-white shadow-sm rounded-3 border d-inline-flex">
                <?php
                $tabs = [
                    ['status' => 'all',     'label' => 'Semua',   'icon' => 'bi-grid-fill',      'count' => $cnt_all,     'color' => 'primary'],
                    ['status' => 'pending', 'label' => 'Pending', 'icon' => 'bi-clock-fill',     'count' => $cnt_pending, 'color' => 'warning'],
                    ['status' => 'paid',    'label' => 'Paid',    'icon' => 'bi-check-circle-fill','count' => $cnt_paid,    'color' => 'success'],
                    ['status' => 'cancel',  'label' => 'Cancel',  'icon' => 'bi-x-circle-fill',    'count' => $cnt_cancel,  'color' => 'danger'],
                ];
                foreach ($tabs as $tab):
                    $active = $filter_status === $tab['status'] ? 'active' : '';
                    $btn_class = $active ? "bg-{$tab['color']} text-white shadow" : "text-muted";
                ?>
                <a href="?p=admin_order_list&status=<?= $tab['status'] ?>&q=<?= urlencode($q) ?>"
                   class="px-4 py-2 rounded-3 text-decoration-none fw-semibold d-flex align-items-center gap-2 small <?= $btn_class ?>">
                    <i class="bi <?= $tab['icon'] ?>"></i>
                    <span><?= $tab['label'] ?></span>
                    <span class="badge rounded-pill <?= $active ? 'bg-white text-dark' : 'bg-light text-muted border' ?> small">
                        <?= $tab['count'] ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-list-task me-2 text-primary"></i>
                    Daftar Order
                    <?php if ($filter_status !== 'all'): ?>
                        <span class="ms-2 badge bg-<?= $tab['color'] ?> bg-opacity-10 text-<?= $tab['color'] ?> fs-6 fw-medium"><?= ucfirst($filter_status) ?></span>
                    <?php endif; ?>
                </h5>
                <div class="d-flex gap-2">
                    <?php if ($cnt_pending > 0): ?>
                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis px-3 py-2 border border-warning border-opacity-25 rounded-pill small">
                            <i class="bi bi-lightning-fill"></i> <?= $cnt_pending ?> Pending
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 small text-uppercase fw-bold text-muted">#Order</th>
                                <th class="py-3 small text-uppercase fw-bold text-muted">Customer</th>
                                <th class="py-3 small text-uppercase fw-bold text-muted">Event & Tiket</th>
                                <th class="py-3 text-center small text-uppercase fw-bold text-muted">Status</th>
                                <th class="py-3 text-end small text-uppercase fw-bold text-muted">Total</th>
                                <th class="py-3 text-center small text-uppercase fw-bold text-muted">Tanggal</th>
                                <th class="py-3 text-center pe-4 small text-uppercase fw-bold text-muted">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($orders) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                                            <i class="bi bi-search fs-1 text-muted"></i>
                                        </div>
                                        <h5 class="text-dark fw-bold">Tidak ada hasil ditemukan</h5>
                                        <p class="text-muted">Coba ubah filter atau kata kunci pencarian Anda.</p>
                                        <a href="?p=admin_order_list" class="btn btn-primary btn-sm rounded-pill px-4">Reset Filter</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php while ($row = mysqli_fetch_assoc($orders)):
                            $status_info = [
                                'paid'    => ['bg' => 'success', 'icon' => 'bi-check-circle-fill'],
                                'pending' => ['bg' => 'warning', 'icon' => 'bi-clock-fill'],
                                'cancel'  => ['bg' => 'danger',  'icon' => 'bi-x-circle-fill']
                            ];
                            $st = $status_info[$row['status']];
                        ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-dark">#<?= $row['id_order'] ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0 p-3 ratio ratio-1x1 w-auto min-vw-5">
                                        <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($row['nama']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($row['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-dark fw-medium small mb-1"><?= htmlspecialchars($row['events'] ?? '-') ?></div>
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="badge bg-light text-muted border px-2 py-1 small">
                                        <i class="bi bi-ticket-perforated-fill me-1"></i><?= $row['total_qty'] ?? 0 ?> Qty
                                    </span>
                                    <?php if ($row['kode_voucher']): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 small">
                                            <i class="bi bi-tag-fill me-1"></i><?= $row['kode_voucher'] ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $st['bg'] ?> bg-opacity-10 text-<?= $st['bg'] ?> border border-<?= $st['bg'] ?> border-opacity-25 px-3 py-2 rounded-pill d-inline-flex align-items-center gap-2 fw-semibold small">
                                    <i class="<?= $st['icon'] ?> fs-6"></i>
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp <?= number_format($row['total'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center">
                                <div class="fw-medium text-dark small"><?= date('d M Y', strtotime($row['tanggal_order'])) ?></div>
                                <div class="text-muted small"><?= date('H:i', strtotime($row['tanggal_order'])) ?> WIB</div>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="?p=admin_order_detail&id=<?= $row['id_order'] ?>"
                                       class="btn btn-sm btn-light border" title="Lihat Detail">
                                        <i class="bi bi-eye text-primary"></i>
                                    </a>
                                    <?php if ($row['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-success shadow-sm"
                                            onclick="updateStatus(<?= $row['id_order'] ?>, 'paid', this)"
                                            title="Konfirmasi Paid">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger shadow-sm"
                                            onclick="updateStatus(<?= $row['id_order'] ?>, 'cancel', this)"
                                            title="Batalkan Order">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <?php elseif ($row['status'] === 'paid'): ?>
                                    <div class="btn btn-sm btn-outline-secondary disabled" title="Order Terkunci">
                                        <i class="bi bi-lock-fill"></i>
                                    </div>
                                    <?php elseif ($row['status'] === 'cancel'): ?>
                                    <span class="text-muted small"><i class="bi bi-x-circle"></i> Batal</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-light py-3 border-top-0">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link rounded-pill px-3 border-0 shadow-sm" href="?p=admin_order_list&status=<?= $filter_status ?>&q=<?= urlencode($q) ?>&page=<?= $page - 1 ?>">
                                    <i class="bi bi-chevron-left me-1"></i>Prev
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link rounded-pill border-0 shadow-sm mx-1" href="?p=admin_order_list&status=<?= $filter_status ?>&q=<?= urlencode($q) ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link rounded-pill px-3 border-0 shadow-sm" href="?p=admin_order_list&status=<?= $filter_status ?>&q=<?= urlencode($q) ?>&page=<?= $page + 1 ?>">
                                    Next<i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div></div>

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
