<?php
$result = null; $message = ''; $alert_class = '';
if (isset($_POST['proses_checkin'])) {
    $kode = mysqli_real_escape_string($conn, $_POST['kode_tiket']);
    $cek  = mysqli_query($conn, "SELECT a.*, u.nama, e.nama_event, t.nama_tiket FROM attendee a JOIN order_detail od ON a.id_detail = od.id_detail JOIN orders o ON od.id_order = o.id_order JOIN users u ON o.id_user = u.id_user JOIN tiket t ON od.id_tiket = t.id_tiket JOIN event e ON t.id_event = e.id_event WHERE a.kode_tiket = '$kode'");
    $data = mysqli_fetch_assoc($cek);
    if ($data) {
        if ($data['status_checkin'] == 'sudah') { $message = "Tiket sudah pernah digunakan pada: " . $data['waktu_checkin']; $alert_class = 'warning'; }
        else { mysqli_query($conn, "UPDATE attendee SET status_checkin='sudah', waktu_checkin=NOW() WHERE kode_tiket='$kode'"); $message = "Check-in Berhasil! Selamat datang, " . htmlspecialchars($data['nama']); $alert_class = 'success'; $result = $data; }
    } else { $message = "Kode Tiket Tidak Terdaftar!"; $alert_class = 'danger'; }
}
?>
<div class="container-fluid"><div class="row">
    <nav class="col-md-2 d-none d-md-block bg-dark sidebar py-4">
        <div class="sidebar-sticky">
            <h5 class="text-white px-3 mb-3"><i class="bi bi-person-badge"></i> Menu Petugas</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link text-white" href="?p=dashboard_petugas"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white active" href="?p=petugas_checkin"><i class="bi bi-qr-code-scan"></i> Scan Check-in</a></li>
            </ul>
        </div>
    </nav>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2 class="mb-4"><i class="bi bi-qr-code-scan"></i> Scan Check-in Petugas</h2>
        <div class="row justify-content-center"><div class="col-md-6">
            <div class="card shadow"><div class="card-header bg-success text-white text-center"><h4 class="mb-0"><i class="bi bi-qr-code-scan"></i> Proses Check-in</h4></div>
            <div class="card-body">
                <?php if ($message): ?><div class="alert alert-<?= $alert_class ?> alert-dismissible fade show"><i class="bi bi-<?= $alert_class=='success'?'check-circle':($alert_class=='warning'?'exclamation-triangle':'x-circle') ?>"></i> <?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
                <form method="POST" class="mt-3">
                    <div class="mb-3"><label class="form-label"><i class="bi bi-ticket-perforated"></i> Kode Tiket</label>
                    <div class="input-group input-group-lg"><span class="input-group-text"><i class="bi bi-upc-scan"></i></span><input type="text" name="kode_tiket" class="form-control text-uppercase" placeholder="Contoh: TKT-ABCD1234" required autofocus><button class="btn btn-success" type="submit" name="proses_checkin"><i class="bi bi-check-lg"></i> Check-in</button></div>
                    </div>
                </form>
                <?php if ($result && $alert_class == 'success'): ?>
                <div class="card mt-4 border-success"><div class="card-body">
                    <h5 class="card-title text-success"><i class="bi bi-person-check"></i> Detail Pengunjung</h5>
                    <table class="table table-sm">
                        <tr><td>Nama</td><td>: <?= htmlspecialchars($result['nama']) ?></td></tr>
                        <tr><td>Event</td><td>: <?= htmlspecialchars($result['nama_event']) ?></td></tr>
                        <tr><td>Tiket</td><td>: <?= htmlspecialchars($result['nama_tiket']) ?></td></tr>
                        <tr><td>Kode</td><td>: <?= $result['kode_tiket'] ?></td></tr>
                    </table>
                </div></div>
                <?php endif; ?>
            </div></div>
            <div class="text-center mt-3"><a href="?p=dashboard_petugas" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a></div>
        </div></div>
    </main>
</div></div>
