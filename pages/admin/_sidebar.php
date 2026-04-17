<?php
$current_page = isset($_GET['p']) ? $_GET['p'] : '';
$menu = [
    ['p' => 'dashboard_admin',    'icon' => 'bi-speedometer2',    'label' => 'Dashboard'],
    ['p' => 'admin_laporan',      'icon' => 'bi-file-earmark-text','label' => 'Laporan'],
    ['p' => 'admin_checkin',      'icon' => 'bi-qr-code-scan',    'label' => 'Check-in'],
    ['separator' => 'MASTER DATA'],
    ['p' => 'admin_venue',        'icon' => 'bi-geo-alt',          'label' => 'Data Venue'],
    ['p' => 'admin_event',        'icon' => 'bi-calendar-event',   'label' => 'Data Event'],
    ['p' => 'admin_tiket',        'icon' => 'bi-ticket',           'label' => 'Data Tiket'],
    ['p' => 'admin_voucher',      'icon' => 'bi-tag',              'label' => 'Data Voucher'],
];
?>
<nav class="col-md-2 d-none d-md-block sidebar py-3">
    <div class="sidebar-brand">
        <h6>ADMIN PANEL</h6>
    </div>
    <ul class="nav flex-column mt-1">
        <?php foreach ($menu as $item): ?>
            <?php if (isset($item['separator'])): ?>
                <li><div class="sidebar-section-label"><?= $item['separator'] ?></div></li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === $item['p'] ? 'active' : '' ?>" href="?p=<?= $item['p'] ?>">
                        <i class="bi <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
        <li class="mt-3"><div class="sidebar-section-label">AKUN</div></li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="?p=logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </li>
    </ul>
</nav>
