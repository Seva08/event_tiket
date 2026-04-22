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
                        <h2 class="fw-bold mb-0" style="letter-spacing: -0.5px;">Manajemen Order</h2>
                        <p class="text-muted mb-0 small">Verifikasi & kelola transaksi masuk</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <form method="GET" action="index.php" class="position-relative search-box">
                    <input type="hidden" name="p" value="admin_order_list">
                    <input type="hidden" name="status" value="<?= $filter_status ?>">
                    <input type="text" name="q" class="form-control border-0 shadow-sm ps-5 py-2 fw-medium" 
                           placeholder="Cari ID, nama, atau email..." value="<?= htmlspecialchars($q) ?>"
                           style="border-radius:12px; height: 48px;">
                    <i class="bi bi-search position-absolute text-muted fs-5" style="left:18px; top:50%; transform:translateY(-50%);"></i>
                    <?php if($q): ?>
                        <a href="?p=admin_order_list&status=<?= $filter_status ?>" class="position-absolute text-muted btn-clear-search" style="right:15px; top:50%; transform:translateY(-50%);">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-2 text-md-end">
                <div class="p-2 px-3 bg-white shadow-sm rounded-3 d-inline-block border">
                    <small class="text-muted d-block" style="font-size: 0.65rem; text-transform: uppercase; font-weight: 700;">Total</small>
                    <span class="fs-5 fw-bold text-dark"><?= $cnt_all ?></span>
                </div>
            </div>
        </div>

        <!-- Alert toast container -->
        <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index:9999"></div>

        <!-- Status Filter Tabs -->
        <div class="mb-4">
            <div class="d-flex flex-wrap gap-2 p-1 bg-white shadow-sm rounded-4 border d-inline-flex">
                <?php
                $tabs = [
                    ['status' => 'all',     'label' => 'Semua',   'icon' => 'bi-grid-fill',      'count' => $cnt_all,     'color' => 'primary'],
                    ['status' => 'pending', 'label' => 'Pending', 'icon' => 'bi-clock-fill',     'count' => $cnt_pending, 'color' => 'warning'],
                    ['status' => 'paid',    'label' => 'Paid',    'icon' => 'bi-check-circle-fill','count' => $cnt_paid,    'color' => 'success'],
                    ['status' => 'cancel',  'label' => 'Cancel',  'icon' => 'bi-x-circle-fill',    'count' => $cnt_cancel,  'color' => 'danger'],
                ];
                foreach ($tabs as $tab):
                    $active = $filter_status === $tab['status'] ? 'active' : '';
                    $btn_class = $active ? "bg-{$tab['color']} text-white shadow" : "text-muted hover-bg-light";
                ?>
                <a href="?p=admin_order_list&status=<?= $tab['status'] ?>&q=<?= urlencode($q) ?>"
                   class="nav-filter-btn px-4 py-2 rounded-3 text-decoration-none fw-semibold d-flex align-items-center gap-2 <?= $btn_class ?>">
                    <i class="bi <?= $tab['icon'] ?>"></i>
                    <span><?= $tab['label'] ?></span>
                    <span class="badge rounded-pill <?= $active ? 'bg-white text-dark' : 'bg-light text-muted border' ?>" style="font-size: 0.7rem;">
                        <?= $tab['count'] ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
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
                    <table class="table table-hover align-middle mb-0 custom-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">#Order</th>
                                <th class="py-3">Customer</th>
                                <th class="py-3">Event & Tiket</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="py-3 text-end">Total</th>
                                <th class="py-3 text-center">Tanggal</th>
                                <th class="py-3 text-center pe-4">Aksi</th>
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
                        <tr class="order-row">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">#<?= $row['id_order'] ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle shadow-sm fw-bold" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                                        <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($row['nama']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem"><?= htmlspecialchars($row['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-dark fw-medium small mb-1"><?= htmlspecialchars($row['events'] ?? '-') ?></div>
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.65rem; font-weight: 600;">
                                        <i class="bi bi-ticket-perforated-fill me-1"></i><?= $row['total_qty'] ?? 0 ?> Qty
                                    </span>
                                    <?php if ($row['kode_voucher']): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1" style="font-size: 0.65rem; font-weight: 600;">
                                            <i class="bi bi-tag-fill me-1"></i><?= $row['kode_voucher'] ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $st['bg'] ?> bg-opacity-10 text-<?= $st['bg'] ?> border border-<?= $st['bg'] ?> border-opacity-25 px-3 py-2 rounded-pill d-inline-flex align-items-center gap-2 fw-semibold" style="font-size: 0.75rem;">
                                    <i class="<?= $st['icon'] ?> fs-6"></i>
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp <?= number_format($row['total'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center">
                                <div class="fw-medium text-dark small"><?= date('d M Y', strtotime($row['tanggal_order'])) ?></div>
                                <div class="text-muted" style="font-size: 0.7rem;"><?= date('H:i', strtotime($row['tanggal_order'])) ?> WIB</div>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="?p=admin_order_detail&id=<?= $row['id_order'] ?>"
                                       class="btn btn-icon btn-light border" title="Lihat Detail">
                                        <i class="bi bi-eye text-primary"></i>
                                    </a>
                                    <?php if ($row['status'] === 'pending'): ?>
                                    <button class="btn btn-icon btn-success shadow-sm btn-verify"
                                            onclick="updateStatus(<?= $row['id_order'] ?>, 'paid', this)"
                                            title="Konfirmasi Paid">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-icon btn-danger shadow-sm"
                                            onclick="updateStatus(<?= $row['id_order'] ?>, 'cancel', this)"
                                            title="Batalkan Order">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <?php elseif ($row['status'] === 'paid'): ?>
                                    <div class="btn btn-icon btn-outline-secondary disabled border-dashed" title="Order Terkunci">
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

<style>
    .search-box input:focus {
        box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.15) !important;
        background: #fff !important;
    }
    .nav-filter-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.85rem;
    }
    .nav-filter-btn:not(.active):hover {
        background-color: #f8fafc;
        transform: translateY(-1px);
    }
    .custom-table thead th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
    }
    .order-row {
        transition: background-color 0.2s ease;
    }
    .order-row:hover {
        background-color: #fcfdfe !important;
    }
    .avatar-circle {
        width: 38px; height: 38px; border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-size: 0.9rem; flex-shrink: 0;
    }
    .btn-icon {
        width: 34px; height: 34px; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px; transition: all 0.2s;
    }
    .btn-icon:hover {
        transform: scale(1.1);
    }
    .btn-verify { animation: pulse-green 2.5s infinite; }
    @keyframes pulse-green {
        0%, 100% { box-shadow: 0 0 0 0 rgba(25,135,84, 0.4); }
        50% { box-shadow: 0 0 0 6px rgba(25,135,84, 0); }
    }
    .pagination .page-link {
        color: #475569;
        font-weight: 600;
    }
    .pagination .active .page-link {
        background-color: #4f46e5;
        color: #fff;
    }
    .btn-clear-search {
        transition: color 0.2s;
        opacity: 0.5;
    }
    .btn-clear-search:hover {
        color: #ef4444 !important;
        opacity: 1;
    }
    .border-dashed {
        border-style: dashed !important;
    }
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
