<?php
if (!isset($_GET['id'])) { header("Location: ?p=admin_venue"); exit; }
$id_venue = (int)$_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM venue WHERE id_venue = $id_venue"));

if (!$data) { 
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Error', 'text' => 'Data venue tidak ditemukan!'];
    header("Location: ?p=admin_venue"); exit; 
}

if (isset($_POST['update'])) {
    $nama_venue = mysqli_real_escape_string($conn, $_POST['nama_venue']);
    $alamat     = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kapasitas  = (int)$_POST['kapasitas'];

    $update = mysqli_query($conn, "UPDATE venue SET nama_venue='$nama_venue', alamat='$alamat', kapasitas=$kapasitas WHERE id_venue=$id_venue");
    if ($update) {
        $_SESSION['alert'] = ['type' => 'success', 'title' => 'Berhasil!', 'text' => 'Data venue telah diperbarui.'];
        header("Location: ?p=admin_venue");
        exit;
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal Update', 'text' => mysqli_error($conn)];
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
                    <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Venue</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item small"><a href="?p=dashboard_admin" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item small"><a href="?p=admin_venue" class="text-decoration-none">Master Venue</a></li>
                            <li class="breadcrumb-item small active" aria-current="page">Edit Data</li>
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
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle icon-box d-inline-flex mb-3" style="width: 70px; height: 70px;">
                                <i class="bi bi-geo-alt-fill fs-2"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Konfigurasi Lokasi</h5>
                            <small class="text-muted">Perbarui informasi detail venue ID #VN-<?= $id_venue ?></small>
                        </div>
                        <div class="card-body px-4 px-md-5 pb-5">
                            <form method="POST">
                                <div class="row g-4">
                                    <!-- Nama Venue -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Nama Venue</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-building"></i></span>
                                            <input type="text" name="nama_venue" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="<?= htmlspecialchars($data['nama_venue']) ?>" placeholder="Contoh: Gelora Bung Karno" required>
                                        </div>
                                    </div>

                                    <!-- Kapasitas -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Kapasitas Maksimal</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-people"></i></span>
                                            <input type="number" name="kapasitas" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="<?= $data['kapasitas'] ?>" placeholder="Contoh: 50000" required>
                                            <span class="input-group-text bg-light border-0 fw-semibold small">ORANG</span>
                                        </div>
                                    </div>

                                    <!-- Alamat -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Alamat Lengkap</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-pin-map"></i></span>
                                            <textarea name="alamat" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" rows="4" placeholder="Masukkan alamat lengkap lokasi..." required><?= htmlspecialchars($data['alamat']) ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-12 pt-3">
                                        <button type="submit" name="update" class="btn btn-warning btn-lg rounded-pill px-5 py-3 fw-bold w-100 shadow border-0 text-white">
                                            <i class="bi bi-save-fill me-2"></i>Simpan Perubahan Venue
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
