<?php
$cp = isset($_GET['p']) ? $_GET['p'] : '';
$menu = [
    ['p' => 'dashboard_admin',  'icon' => 'bi-speedometer2',     'label' => 'Dashboard'],
    ['p' => 'admin_laporan',    'icon' => 'bi-file-earmark-bar-graph', 'label' => 'Laporan'],
    ['p' => 'admin_checkin',    'icon' => 'bi-qr-code-scan',     'label' => 'Check-in'],
    ['sep' => 'MASTER DATA'],
    ['p' => 'admin_venue',      'icon' => 'bi-geo-alt',           'label' => 'Venue'],
    ['p' => 'admin_event',      'icon' => 'bi-calendar-event',    'label' => 'Event'],
    ['p' => 'admin_tiket',      'icon' => 'bi-ticket-perforated', 'label' => 'Tiket'],
    ['p' => 'admin_voucher',    'icon' => 'bi-tag',               'label' => 'Voucher'],
    ['sep' => 'AKUN'],
    ['p' => 'logout', 'icon' => 'bi-box-arrow-right', 'label' => 'Logout', 'extra' => 'text-danger'],
];
?>
<nav class="col-md-2 d-none d-md-block sidebar py-2">
    <div class="px-3 py-3 mb-1">
        <div class="d-flex align-items-center gap-2">
            <span style="background:var(--g-primary);width:28px;height:28px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;flex-shrink:0;">
                <i class="bi bi-shield-lock-fill"></i>
            </span>
            <div>
                <div style="color:#e2e8f0;font-weight:700;font-size:.8rem;line-height:1.2">Admin Panel</div>
                <div style="color:#475569;font-size:.68rem;"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></div>
            </div>
        </div>
    </div>
    <ul class="nav flex-column">
        <?php foreach ($menu as $item): ?>
            <?php if (isset($item['sep'])): ?>
                <li><div class="sidebar-label"><?= $item['sep'] ?></div></li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?= $cp === $item['p'] ? 'active' : '' ?> <?= $item['extra'] ?? '' ?>"
                       href="?p=<?= $item['p'] ?>">
                        <i class="bi <?= $item['icon'] ?>"></i>
                        <?= $item['label'] ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</nav>
