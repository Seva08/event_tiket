<?php
// Validasi login & role
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    echo "<script>alert('Akses ditolak!'); window.location='?p=login';</script>";
    exit;
}

if (!isset($_GET['kode'])) {
    echo "Kode tiket tidak valid!";
    exit;
}

$kode_tiket = mysqli_real_escape_string($conn, $_GET['kode']);
$id_user = $_SESSION['id_user'];

$q_tiket = mysqli_query($conn, 
    "SELECT a.*, t.nama_tiket, e.nama_event, e.tanggal, v.nama_venue, v.alamat, u.nama as pembeli, o.status
     FROM attendee a 
     JOIN order_detail od ON a.id_detail = od.id_detail
     JOIN orders o ON od.id_order = o.id_order
     JOIN tiket t ON od.id_tiket = t.id_tiket
     JOIN event e ON t.id_event = e.id_event
     JOIN venue v ON e.id_venue = v.id_venue
     JOIN users u ON o.id_user = u.id_user
     WHERE a.kode_tiket = '$kode_tiket' AND o.id_user = $id_user");

$tiket = mysqli_fetch_assoc($q_tiket);

if (!$tiket) {
    echo "Tiket tidak ditemukan atau Anda tidak memiliki akses ke tiket ini.";
    exit;
}

if ($tiket['status'] !== 'paid') {
    echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h2 style='color:#f59e0b;'>Tiket Belum Aktif</h2>
            <p>Selesaikan pembayaran untuk mengaktifkan dan mencetak tiket ini.</p>
            <a href='?p=riwayat' style='color:#4f46e5; text-decoration:none; font-weight:bold;'>Kembali ke Riwayat</a>
          </div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Tiket <?= $tiket['kode_tiket'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; }
        .ticket-wrapper { max-width: 600px; margin: 40px auto; }
        .ticket-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .ticket-header { background: #4f46e5; color: #fff; padding: 25px; text-align: center; border-bottom: 2px dashed rgba(255,255,255,0.4); }
        .ticket-body { padding: 30px; }
        .barcode-container { text-align: center; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1; margin-top: 20px; }
        
        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .ticket-wrapper { margin: 0 auto; box-shadow: none; border: none; max-width: 100%; }
            .ticket-card { box-shadow: none; border: 1px solid #000; border-radius: 0; }
            .ticket-header { background: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .d-print-none { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container ticket-wrapper">
    <div class="d-flex justify-content-between mb-3 d-print-none">
        <a href="?p=riwayat" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Cetak ke PDF / Printer</button>
    </div>

    <div class="ticket-card">
        <div class="ticket-header">
            <h3 class="mb-1 fw-bold"><?= htmlspecialchars($tiket['nama_event']) ?></h3>
            <p class="mb-0 opacity-75">E-Tiket Resmi Event Tiket</p>
        </div>
        <div class="ticket-body">
            <div class="row mb-4">
                <div class="col-6">
                    <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Nama Pembeli</small>
                    <div class="fw-bold fs-5"><?= htmlspecialchars($tiket['pembeli']) ?></div>
                </div>
                <div class="col-6 text-end">
                    <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Jenis Tiket</small>
                    <div class="fw-bold fs-5 text-primary"><?= htmlspecialchars($tiket['nama_tiket']) ?></div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-12 mb-3">
                    <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Tanggal Event</small>
                    <div class="fw-bold fs-5"><i class="bi bi-calendar-event text-danger me-2"></i><?= date('d F Y', strtotime($tiket['tanggal'])) ?></div>
                </div>
                <div class="col-12 mb-3">
                    <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Lokasi / Venue</small>
                    <div class="fw-bold"><i class="bi bi-geo-alt-fill text-danger me-2"></i><?= htmlspecialchars($tiket['nama_venue']) ?></div>
                    <div class="text-muted small ps-4"><?= htmlspecialchars($tiket['alamat']) ?></div>
                </div>
            </div>

            <div class="barcode-container">
                <svg id="barcode"></svg>
                <div class="mt-2 fw-bold text-dark" style="letter-spacing: 3px; font-size: 1.2rem; font-family: monospace;">
                    <?= $tiket['kode_tiket'] ?>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <small class="text-muted">Harap tunjukkan barcode/kode ini saat di pintu masuk event untuk di-scan oleh petugas.</small>
            </div>
        </div>
    </div>
</div>

<!-- Load JsBarcode Library from CDN -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    JsBarcode("#barcode", "<?= $tiket['kode_tiket'] ?>", {
        format: "CODE128",
        lineColor: "#000",
        width: 2.5,
        height: 80,
        displayValue: false, // Kita sembunyikan nilai defaultnya karena kita render sendiri di bawahnya
        margin: 0
    });
</script>

</body>
</html>
