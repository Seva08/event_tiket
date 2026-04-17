<?php
$user_id = $_SESSION['id_user'];
$query     = mysqli_query($conn,
    "SELECT o.*, od.qty, t.nama_tiket, e.nama_event, e.tanggal, v.kode_voucher, v.potongan
     FROM orders o
     JOIN order_detail od ON o.id_order = od.id_order
     JOIN tiket t ON od.id_tiket = t.id_tiket
     JOIN event e ON t.id_event = e.id_event
     LEFT JOIN voucher v ON o.id_voucher = v.id_voucher
     WHERE o.id_user = $user_id ORDER BY o.tanggal_order DESC");
$attendees = mysqli_query($conn,
    "SELECT a.*, t.nama_tiket, e.nama_event, e.tanggal
     FROM attendee a
     JOIN order_detail od ON a.id_detail = od.id_detail
     JOIN orders o ON od.id_order = o.id_order
     JOIN tiket t ON od.id_tiket = t.id_tiket
     JOIN event e ON t.id_event = e.id_event
     WHERE o.id_user = $user_id ORDER BY e.tanggal DESC");
$total_order   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user=$user_id"))['c'];
$berhasil      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user=$user_id AND status='paid'"))['c'];
$pending       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user=$user_id AND status='pending'"))['c'];
$sudah_checkin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendee a JOIN order_detail od ON a.id_detail=od.id_detail JOIN orders o ON od.id_order=o.id_order WHERE o.id_user=$user_id AND a.status_checkin='sudah'"))['c'];
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=home">Home</a></li>
            <li class="breadcrumb-item"><a href="?p=dashboard_user">Dashboard</a></li>
            <li class="breadcrumb-item active">Riwayat</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title mb-1"><i class="bi bi-clock-history me-2" style="color:var(--c-primary)"></i>Riwayat Pembelian</h2>
            <p class="text-muted mb-0" style="font-size:.87rem;">Semua transaksi dan tiket yang kamu miliki</p>
        </div>
        <a href="?p=home" class="btn btn-primary btn-sm" style="border-radius:50px;">
            <i class="bi bi-plus-circle me-1"></i>Pesan Tiket
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <?php $stats = [
            ['primary','bi-cart-check',    $total_order,   'Total Pesanan'],
            ['success','bi-check-circle-fill',$berhasil,   'Berhasil Bayar'],
            ['warning','bi-clock-fill',    $pending,       'Menunggu'],
            ['info',   'bi-qr-code-scan',  $sudah_checkin, 'Sudah Check-in'],
        ]; foreach ($stats as $s): ?>
        <div class="col-6 col-md-3">
            <div class="stat-card <?= $s[0] ?> h-100" style="padding:1.2rem;">
                <i class="bi <?= $s[1] ?> mb-2 d-block" style="font-size:1.6rem;opacity:.85;"></i>
                <h3 class="mb-0" style="font-size:1.9rem;"><?= $s[2] ?></h3>
                <p class="mb-0" style="font-size:.76rem;opacity:.85;"><?= $s[3] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabs -->
    <div class="card mb-0">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill gap-1" id="riwayatTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-2" id="order-tab" data-bs-toggle="tab" data-bs-target="#order" type="button"
                            style="border-radius:var(--r-md);font-weight:600;font-size:.875rem;">
                        <i class="bi bi-cart me-2"></i>Daftar Pesanan
                        <span class="badge ms-1" style="background:rgba(255,255,255,.3);border-radius:50px;"><?= $total_order ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-2" id="tiket-tab" data-bs-toggle="tab" data-bs-target="#tiket" type="button"
                            style="border-radius:var(--r-md);font-weight:600;font-size:.875rem;">
                        <i class="bi bi-ticket-perforated me-2"></i>Tiket Saya
                        <span class="badge bg-primary ms-1" style="border-radius:50px;"><?= mysqli_num_rows($attendees) ?></span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content mt-3" id="riwayatTabContent">
        <!-- ── Tab Pesanan ── -->
        <div class="tab-pane fade show active" id="order" role="tabpanel">
            <?php if (mysqli_num_rows($query) > 0): ?>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="70">ID</th>
                                <th>Event &amp; Tiket</th>
                                <th class="text-center" width="70">Qty</th>
                                <th class="text-end">Total</th>
                                <th class="text-center" width="110">Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php mysqli_data_seek($query, 0); while ($row = mysqli_fetch_assoc($query)):
                                $bc = $row['status']=='paid' ? 'success' : ($row['status']=='pending' ? 'warning' : 'danger');
                            ?>
                            <tr>
                                <td class="fw-bold" style="color:var(--txt-muted);">#<?= $row['id_order'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:36px;height:36px;background:var(--g-primary);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <i class="bi bi-calendar-event text-white" style="font-size:.85rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="font-size:.88rem;"><?= htmlspecialchars($row['nama_event'] ?? '-') ?></div>
                                            <div style="font-size:.78rem;color:var(--txt-muted);"><?= htmlspecialchars($row['nama_tiket'] ?? '-') ?></div>
                                            <?php if ($row['kode_voucher']): ?>
                                                <span class="badge bg-warning text-dark mt-1" style="font-size:.68rem;border-radius:50px;">
                                                    <i class="bi bi-tag me-1"></i><?= $row['kode_voucher'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info" style="border-radius:50px;"><?= $row['qty'] ?>x</span>
                                </td>
                                <td class="text-end fw-bold">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $bc ?> px-3 py-2"><?= ucfirst($row['status']) ?></span>
                                </td>
                                <td style="font-size:.8rem;color:var(--txt-muted);">
                                    <?= date('d M Y', strtotime($row['tanggal_order'])) ?><br>
                                    <span style="font-size:.72rem;"><?= date('H:i', strtotime($row['tanggal_order'])) ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <div style="width:80px;height:80px;background:var(--g-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-cart-x text-white fs-3"></i>
                </div>
                <h5 class="fw-bold mb-1">Belum Ada Pesanan</h5>
                <p class="text-muted mb-3" style="font-size:.87rem;">Mulai jelajahi event dan pesan tiket sekarang!</p>
                <a href="?p=home" class="btn btn-primary" style="border-radius:50px;">
                    <i class="bi bi-calendar-event me-2"></i>Lihat Event
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Tab Tiket ── -->
        <div class="tab-pane fade" id="tiket" role="tabpanel">
            <?php mysqli_data_seek($attendees, 0); ?>
            <?php if (mysqli_num_rows($attendees) > 0): ?>
            <div class="row g-3">
                <?php while ($tkt = mysqli_fetch_assoc($attendees)):
                    $done  = $tkt['status_checkin'] == 'sudah';
                    $hdr_g = $done ? 'var(--g-success)' : 'var(--g-warning)';
                ?>
                <div class="col-md-4">
                    <div class="card h-100" style="overflow:hidden;">
                        <!-- Ticket header -->
                        <div style="background:<?= $hdr_g ?>;padding:1.1rem 1.3rem;color:#fff;position:relative;overflow:hidden;">
                            <div style="position:absolute;top:-15px;right:-15px;width:70px;height:70px;background:rgba(255,255,255,.12);border-radius:50%;"></div>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size:.7rem;opacity:.78;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.15rem;">Tiket</div>
                                    <div class="fw-bold"><?= htmlspecialchars($tkt['nama_tiket']) ?></div>
                                </div>
                                <i class="bi bi-<?= $done ? 'check-circle-fill' : 'qr-code-scan' ?>" style="font-size:1.6rem;opacity:.7;"></i>
                            </div>
                        </div>
                        <!-- Ticket body -->
                        <div class="card-body p-3">
                            <div class="fw-bold mb-1" style="font-size:.95rem;"><?= htmlspecialchars($tkt['nama_event']) ?></div>
                            <div class="text-muted mb-3" style="font-size:.8rem;">
                                <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($tkt['tanggal'])) ?>
                            </div>
                            <!-- QR Code area -->
                            <div class="p-3 mb-3 text-center"
                                 style="background:#f8fafc;border-radius:var(--r-md);border:1.5px dashed var(--border);">
                                <div style="font-size:.68rem;color:var(--txt-muted);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.3rem;">Kode Tiket</div>
                                <div class="ticket-code"><?= $tkt['kode_tiket'] ?></div>
                            </div>
                            <!-- Status -->
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge <?= $done ? 'bg-success' : 'bg-warning text-dark' ?> px-3 py-2" style="border-radius:50px;">
                                    <i class="bi bi-<?= $done ? 'check-circle' : 'clock' ?> me-1"></i>
                                    <?= $done ? 'Sudah Check-in' : 'Menunggu Check-in' ?>
                                </span>
                                <?php if ($done && $tkt['waktu_checkin']): ?>
                                    <small class="text-muted" style="font-size:.72rem;">
                                        <?= date('d M H:i', strtotime($tkt['waktu_checkin'])) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <div style="width:80px;height:80px;background:var(--g-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-ticket-perforated text-white fs-3"></i>
                </div>
                <h5 class="fw-bold mb-1">Belum Memiliki Tiket</h5>
                <p class="text-muted mb-3" style="font-size:.87rem;">Pesan tiket untuk event favoritmu!</p>
                <a href="?p=home" class="btn btn-primary" style="border-radius:50px;">
                    <i class="bi bi-calendar-event me-2"></i>Jelajahi Event
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
