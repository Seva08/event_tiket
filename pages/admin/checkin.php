<?php
$result = null;
$message = '';
$alert_class = '';

if (isset($_POST['proses_checkin'])) {
    $kode = mysqli_real_escape_string($conn, trim($_POST['kode_tiket']));
    $cek  = mysqli_query($conn, "SELECT a.*, u.nama, e.nama_event, e.tanggal, t.nama_tiket, o.status as order_status
                                FROM attendee a 
                                JOIN order_detail od ON a.id_detail = od.id_detail 
                                JOIN orders o ON od.id_order = o.id_order 
                                JOIN users u ON o.id_user = u.id_user 
                                JOIN tiket t ON od.id_tiket = t.id_tiket 
                                JOIN event e ON t.id_event = e.id_event 
                                WHERE a.kode_tiket = '$kode'");
    $data = mysqli_fetch_assoc($cek);

    if ($data) {
        $today = date('Y-m-d');
        $event_date = date('Y-m-d', strtotime($data['tanggal']));
        
        // Cek status pembayaran (mendukung 'success' atau 'paid')
        $status_lunas = ['success', 'paid'];
        if (!in_array(strtolower($data['order_status']), $status_lunas)) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'title' => 'Pembayaran Belum Valid',
                'text' => 'Tiket ini tidak bisa digunakan karena status pesanan masih: ' . strtoupper($data['order_status'])
            ];
        } elseif ($data['status_checkin'] == 'sudah') { 
            $_SESSION['alert'] = [
                'type' => 'warning',
                'title' => 'Sudah Check-in',
                'text' => 'Tiket ini sudah pernah digunakan pada: ' . date('d M Y H:i', strtotime($data['waktu_checkin']))
            ];
        } elseif ($event_date != $today) {
            $_SESSION['alert'] = [
                'type' => 'warning',
                'title' => 'Beda Tanggal Event',
                'text' => 'Event ini dijadwalkan tanggal ' . date('d M Y', strtotime($data['tanggal'])) . '. Sekarang tanggal ' . date('d M Y') . '.'
            ];
        } else { 
            mysqli_query($conn, "UPDATE attendee SET status_checkin='sudah', waktu_checkin=NOW() WHERE kode_tiket='$kode'"); 
            $_SESSION['alert'] = [
                'type' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Selamat datang, ' . $data['nama']
            ];
            $_SESSION['last_checkin_admin'] = $data;
        }
    } else { 
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Tidak Terdaftar',
            'text' => 'Kode Tiket ' . $kode . ' tidak ditemukan!'
        ];
    }
    header("Location: ?p=admin_checkin");
    exit;
}
$result = isset($_SESSION['last_checkin_admin']) ? $_SESSION['last_checkin_admin'] : null;
unset($_SESSION['last_checkin_admin']);
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title"><i class="bi bi-qr-code-scan"></i> Check-in Tiket</h2>
                <p class="text-muted mb-0">Scan atau masukkan kode tiket pengunjung untuk validasi</p>
            </div>
            <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-calendar3"></i> <?= date('d M Y') ?></span>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow" style="border-radius: var(--r-lg, 16px); overflow: hidden;">
                    <div class="card-header bg-primary text-white text-center py-4" style="border: none;">
                        <div class="bg-white bg-opacity-25 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 70px; height: 70px;">
                            <i class="bi bi-upc-scan fs-1"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">Proses Check-in</h4>
                    </div>
                    <div class="card-body p-4 bg-light">

                        <!-- Camera Scan Button & Container -->
                        <div class="text-center mb-4">
                            <button type="button" id="btnToggleCamera" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                <i class="bi bi-camera me-2"></i> Buka Kamera Scan
                            </button>
                            <div id="reader-container" class="mt-3 d-none">
                                <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto; border-radius: 12px; overflow: hidden; border: 3px solid var(--g-primary);"></div>
                                <button type="button" id="btnStopCamera" class="btn btn-sm btn-danger mt-2 rounded-pill px-3">
                                    <i class="bi bi-x-circle me-1"></i> Tutup Kamera
                                </button>
                            </div>
                        </div>

                        <form method="POST" class="mt-2" id="checkinForm">
                            <input type="hidden" name="proses_checkin" value="1">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Masukkan Kode Tiket</label>
                                <div class="input-group input-group-lg" style="box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-radius: var(--r-md, 8px); overflow: hidden;">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" name="kode_tiket" id="kode_tiket" class="form-control border-start-0 ps-0 fw-bold text-uppercase" placeholder="Contoh: TKT-ABCD1234" required autofocus style="box-shadow: none;">
                                    <button class="btn btn-primary fw-bold px-4" type="submit">
                                        <i class="bi bi-check-lg me-1"></i> Check-in
                                    </button>
                                </div>
                                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Gunakan kamera, hardware scanner, atau ketik kode manual.</div>
                            </div>
                        </form>

                        <?php if ($result): ?>
                            <div class="card mt-4 border-0" style="background: #e0f2fe; border-radius: var(--r-md, 8px);">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom border-primary border-opacity-25">
                                        <i class="bi bi-person-check-fill text-primary fs-2 me-3"></i>
                                        <div>
                                            <h5 class="card-title text-primary fw-bold mb-0">Check-in Berhasil!</h5>
                                            <small class="text-primary opacity-75">Detail Tiket Pengunjung</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1 text-uppercase" style="font-size:0.75rem;">Nama Pengunjung</small>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($result['nama']) ?></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1 text-uppercase" style="font-size:0.75rem;">Kode Tiket</small>
                                            <div class="fw-bold font-monospace text-primary"><?= $result['kode_tiket'] ?></div>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block mb-1 text-uppercase" style="font-size:0.75rem;">Nama Event</small>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($result['nama_event']) ?></div>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block mb-1 text-uppercase" style="font-size:0.75rem;">Tipe Tiket</small>
                                            <div class="badge bg-primary bg-opacity-25 text-primary px-3 py-2 fs-6 rounded-pill">
                                                <?= htmlspecialchars($result['nama_tiket']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="?p=dashboard_admin" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-arrow-left me-2"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </main>
</div></div>

<!-- Library HTML5 QR Code -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('kode_tiket');
    const form = document.getElementById('checkinForm');
    if (!input) return;

    // Selalu fokus ke input agar siap scan kapan saja (untuk hardware scanner)
    input.focus();
    document.addEventListener('click', (e) => {
        // Jangan auto-focus jika sedang pakai kamera
        if (!document.getElementById('reader-container').classList.contains('d-none')) return;
        input.focus();
    });

    // Auto Submit Logic (Untuk Hardware Scanner)
    let scanTimer;
    input.addEventListener('input', function() {
        clearTimeout(scanTimer);
        if (this.value.length >= 10) {
            scanTimer = setTimeout(() => {
                form.submit();
            }, 300);
        }
    });

    // Logic Kamera Scan
    const btnToggle = document.getElementById('btnToggleCamera');
    const btnStop = document.getElementById('btnStopCamera');
    const readerContainer = document.getElementById('reader-container');
    let html5QrCode;

    btnToggle.addEventListener('click', function() {
        readerContainer.classList.remove('d-none');
        btnToggle.classList.add('d-none');
        
        html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 150 } };

        // Start scanning
        html5QrCode.start(
            { facingMode: "environment" }, 
            config, 
            (decodedText) => {
                // Berhasil Scan
                input.value = decodedText;
                if (navigator.vibrate) navigator.vibrate(100);
                
                html5QrCode.stop().then(() => {
                    form.submit();
                });
            },
            (errorMessage) => { }
        ).catch((err) => {
            console.error("Gagal memulai kamera:", err);
            Swal.fire('Error Kamera', 'Pastikan izin kamera sudah diberikan dan gunakan HTTPS.', 'error');
            stopCamera();
        });
    });

    function stopCamera() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                readerContainer.classList.add('d-none');
                btnToggle.classList.remove('d-none');
            });
        } else {
            readerContainer.classList.add('d-none');
            btnToggle.classList.remove('d-none');
        }
    }

    btnStop.addEventListener('click', stopCamera);
});
</script>
