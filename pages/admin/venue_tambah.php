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
            <!-- Breadcrumb & Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-geo-alt text-primary me-2"></i>Tambah Venue</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item small"><a href="?p=dashboard_admin" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item small"><a href="?p=admin_venue" class="text-decoration-none">Master Venue</a></li>
                            <li class="breadcrumb-item small active" aria-current="page">Tambah Baru</li>
                        </ol>
                    </nav>
                </div>
                <a href="?p=admin_venue" class="btn btn-light rounded-pill px-4 border shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Batal & Kembali
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-4 px-4 px-md-5 text-center">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle icon-box d-inline-flex mb-3" style="width: 70px; height: 70px;">
                                <i class="bi bi-geo-alt-fill fs-2"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Daftarkan Lokasi Baru</h5>
                            <small class="text-muted">Masukkan informasi detail venue penyelenggaraan event</small>
                        </div>
                        <div class="card-body px-4 px-md-5 pb-5">
                            <form method="POST">
                                <div class="row g-4">
                                    <!-- Nama Venue -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Nama Venue</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-building"></i></span>
                                            <input type="text" name="nama_venue" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" placeholder="Contoh: Gelora Bung Karno" required>
                                        </div>
                                    </div>

                                    <!-- Kapasitas -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Kapasitas Maksimal</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-people"></i></span>
                                            <input type="number" name="kapasitas" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" placeholder="Contoh: 50000" required>
                                            <span class="input-group-text bg-light border-0 fw-semibold small">ORANG</span>
                                        </div>
                                        <div class="form-text small opacity-75">Batas maksimal tiket yang bisa dijual di lokasi ini.</div>
                                    </div>

                                    <!-- Alamat -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Alamat Lengkap</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-pin-map"></i></span>
                                            <textarea name="alamat" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" rows="4" placeholder="Masukkan alamat lengkap lokasi..." required></textarea>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-12 pt-3">
                                        <button type="submit" name="simpan" class="btn btn-success btn-lg rounded-pill px-5 py-3 fw-bold w-100 shadow border-0">
                                            <i class="bi bi-check-circle-fill me-2"></i>Simpan Venue
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
