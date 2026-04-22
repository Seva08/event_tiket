<?php
$result = null;
$message = '';
$alert_class = '';

if (isset($_POST['proses_checkin'])) {
    $kode = mysqli_real_escape_string($conn, $_POST['kode_tiket']);
    $cek  = mysqli_query($conn, "SELECT a.*, u.nama, e.nama_event, e.tanggal, t.nama_tiket 
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
        
        if ($event_date != $today) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'title' => 'Gagal Check-in',
                'text' => 'Event ini dijadwalkan pada ' . date('d M Y', strtotime($data['tanggal'])) . ', bukan hari ini.'
            ];
        } elseif ($data['status_checkin'] == 'sudah') { 
            $_SESSION['alert'] = [
                'type' => 'warning',
                'title' => 'Sudah Check-in',
                'text' => 'Tiket ini sudah digunakan pada: ' . $data['waktu_checkin']
            ];
        } else { 
            mysqli_query($conn, "UPDATE attendee SET status_checkin='sudah', waktu_checkin=NOW() WHERE kode_tiket='$kode'"); 
            $_SESSION['alert'] = [
                'type' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Selamat datang, ' . $data['nama']
            ];
            // Simpan data terakhir di session agar detail tetap muncul setelah redirect
            $_SESSION['last_checkin'] = $data;
        }
    } else { 
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Tidak Terdaftar',
            'text' => 'Kode Tiket ' . $kode . ' tidak ditemukan!'
        ];
    }
    header("Location: ?p=petugas_checkin");
    exit;
}

// Ambil data terakhir untuk ditampilkan di bawah form
$result = isset($_SESSION['last_checkin']) ? $_SESSION['last_checkin'] : null;
unset($_SESSION['last_checkin']);
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title"><i class="bi bi-qr-code-scan"></i> Scan Check-in Petugas</h2>
                <p class="text-muted mb-0">Scan atau masukkan kode tiket pengunjung untuk validasi</p>
            </div>
            <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-calendar3"></i> <?= date('d M Y') ?></span>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow" style="border-radius: var(--r-lg, 16px); overflow: hidden;">
                    <div class="card-header bg-success text-white text-center py-4" style="border: none;">
                        <div class="bg-white bg-opacity-25 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 70px; height: 70px;">
                            <i class="bi bi-qr-code-scan fs-1"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">Proses Check-in</h4>
                    </div>
                    <div class="card-body p-4 bg-light">

                        <form method="POST" class="mt-2">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Masukkan Kode Tiket</label>
                                <div class="input-group input-group-lg" style="box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-radius: var(--r-md, 8px); overflow: hidden;">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" name="kode_tiket" class="form-control border-start-0 ps-0 fw-bold text-uppercase" placeholder="Contoh: TKT-ABCD1234" required autofocus style="box-shadow: none;">
                                    <button class="btn btn-success fw-bold px-4" type="submit" name="proses_checkin">
                                        <i class="bi bi-check-lg me-1"></i> Check-in
                                    </button>
                                </div>
                                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Pastikan kode tiket sesuai dengan yang tertera pada e-tiket pengunjung.</div>
                            </div>
                        </form>

                        <?php if ($result): ?>
                            <div class="card mt-4 border-0" style="background: #ecfdf5; border-radius: var(--r-md, 8px);">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom border-success border-opacity-25">
                                        <i class="bi bi-person-check-fill text-success fs-2 me-3"></i>
                                        <div>
                                            <h5 class="card-title text-success fw-bold mb-0">Check-in Berhasil!</h5>
                                            <small class="text-success opacity-75">Detail Tiket Pengunjung</small>
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
                                            <div class="badge bg-success bg-opacity-25 text-success px-3 py-2 fs-6 rounded-pill">
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
                    <a href="?p=dashboard_petugas" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-arrow-left me-2"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </main>
</div></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.querySelector('input[name="kode_tiket"]');
    if (!input) return;

    // Selalu fokus ke input kode tiket
    input.focus();
    
    // Jika user klik di mana saja, kembalikan fokus ke input (untuk memudahkan scan terus menerus)
    document.addEventListener('click', function() {
        input.focus();
    });

    // Fungsi Auto Enter / Auto Submit
    let scanTimer;
    input.addEventListener('input', function() {
        // Bersihkan timer sebelumnya
        clearTimeout(scanTimer);
        
        // Kode tiket kita formatnya TKT-XXXXXXXX (12 karakter)
        // Jika panjang input mencapai 12, otomatis klik tombol check-in
        if (this.value.length >= 12) {
            scanTimer = setTimeout(() => {
                const btn = document.querySelector('button[name="proses_checkin"]');
                if (btn) btn.click();
            }, 100); // Delay kecil untuk memastikan scanner selesai input
        }
    });
});
</script>
