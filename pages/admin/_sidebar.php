<?php
$cp = isset($_GET['p']) ? $_GET['p'] : '';
$role = $_SESSION['role'] ?? 'petugas';

// Define menu based on role
if ($role === 'admin') {
    $menu = [
        ['p' => 'dashboard_admin',  'icon' => 'bi-speedometer2',          'label' => 'Dashboard'],
        ['p' => 'admin_laporan',    'icon' => 'bi-file-earmark-bar-graph', 'label' => 'Laporan'],
        ['p' => 'admin_order_list', 'icon' => 'bi-receipt-cutoff',         'label' => 'Orders'],
        ['p' => 'admin_checkin',    'icon' => 'bi-qr-code-scan',           'label' => 'Check-in'],
        ['sep' => 'MASTER DATA'],
        ['p' => 'admin_venue',      'icon' => 'bi-geo-alt',           'label' => 'Venue'],
        ['p' => 'admin_event',      'icon' => 'bi-calendar-event',    'label' => 'Event'],
        ['p' => 'admin_tiket',      'icon' => 'bi-ticket-perforated', 'label' => 'Tiket'],
        ['p' => 'admin_voucher',    'icon' => 'bi-tag',               'label' => 'Voucher'],
        ['sep' => 'AKUN'],
        ['p' => 'logout',           'icon' => 'bi-box-arrow-right',   'label' => 'Logout', 'extra' => 'text-danger'],
    ];
    $panel_label = "Admin Panel";
    $panel_icon = "bi-shield-lock-fill";
} else {
    // Role Petugas
    $menu = [
        ['p' => 'dashboard_petugas','icon' => 'bi-speedometer2',      'label' => 'Dashboard'],
        ['p' => 'petugas_checkin',  'icon' => 'bi-qr-code-scan',      'label' => 'Scan Check-in'],
        ['sep' => 'AKUN'],
        ['p' => 'logout',           'icon' => 'bi-box-arrow-right',   'label' => 'Logout', 'extra' => 'text-danger'],
    ];
    $panel_label = "Panel Petugas";
    $panel_icon = "bi-person-badge-fill";
}
?>
<nav class="col-md-2 d-none d-md-block bg-dark text-white py-2 shadow" id="adminSidebar">
    <div class="px-3 py-3 mb-1">
        <div class="d-flex align-items-center gap-2">
            <span class="bg-primary text-white rounded-2 icon-box-sm">
                <i class="bi <?= $panel_icon ?> small"></i>
            </span>
            <div>
                <div class="text-light fw-bold small lh-sm"><?= $panel_label ?></div>
                <div class="text-secondary small"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></div>
            </div>
        </div>
    </div>
    <ul class="nav flex-column">
        <?php foreach ($menu as $item): ?>
            <?php if (isset($item['sep'])): ?>
                <li><div class="text-secondary fw-bold text-uppercase px-3 pt-3 pb-1 small" style="font-size: 0.65rem; letter-spacing: 1px;"><?= $item['sep'] ?></div></li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link text-light d-flex align-items-center gap-2 mx-2 rounded-2 <?= $cp === $item['p'] ? 'active bg-primary' : '' ?> <?= $item['extra'] ?? '' ?>"
                       href="?p=<?= $item['p'] ?>">
                        <i class="bi <?= $item['icon'] ?>"></i>
                        <span class="small fw-medium"><?= $item['label'] ?></span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</nav>
