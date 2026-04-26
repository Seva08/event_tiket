<?php
if (isset($_POST['simpan'])) {
    $nama_venue = mysqli_real_escape_string($conn, $_POST['nama_venue']);
    $alamat     = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kapasitas  = (int)$_POST['kapasitas'];

    $insert = mysqli_query($conn, "INSERT INTO venue (nama_venue, alamat, kapasitas) VALUES ('$nama_venue', '$alamat', $kapasitas)");
    if ($insert) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Venue baru berhasil ditambahkan.'
        ];
        header("Location: ?p=admin_venue");
        exit;
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Gagal Simpan',
            'text' => 'Terjadi kesalahan: ' . mysqli_error($conn)
        ];
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title"><i class="bi bi-building-add"></i> Tambah Venue</h2>
                    <p class="text-muted mb-0">Daftarkan lokasi baru untuk penyelenggaraan event</p>
                </div>
                <a href="?p=admin_venue" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <form method="POST">
                                <div class="mb-4 text-center">
                                    <div class="d-inline-flex p-3 rounded-circle bg-success bg-opacity-10 mb-3">
                                        <i class="bi bi-geo-alt fs-1 text-success"></i>
                                    </div>
                                    <h5 class="fw-bold">Informasi Lokasi</h5>
                                    <p class="text-muted small">Lengkapi data venue di bawah ini</p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Nama Venue</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-building"></i></span>
                                        <input type="text" name="nama_venue" class="form-control form-control-lg border-0 bg-light" placeholder="Contoh: Gelora Bung Karno" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Alamat Lengkap</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-map"></i></span>
                                        <textarea name="alamat" class="form-control form-control-lg border-0 bg-light" rows="3" placeholder="Masukkan alamat lengkap..." required></textarea>
                                    </div>
                                </div>

                                <div class="mb-5">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Kapasitas Maksimal</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-people"></i></span>
                                        <input type="number" name="kapasitas" class="form-control form-control-lg border-0 bg-light" placeholder="Contoh: 50000" required>
                                        <span class="input-group-text bg-light border-0">Orang</span>
                                    </div>
                                    <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Kapasitas akan menjadi batas maksimal total tiket yang bisa dijual.</div>
                                </div>

                                <div class="d-grid pt-2">
                                    <button type="submit" name="simpan" class="btn btn-success btn-lg fw-bold shadow-sm p-3 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-2"></i> Daftarkan Venue
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
