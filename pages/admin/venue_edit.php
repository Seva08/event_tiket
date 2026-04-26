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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold"><i class="bi bi-pencil-square"></i> Edit Venue</h2>
                    <p class="text-muted mb-0">Perbarui informasi lokasi penyelenggaraan</p>
                </div>
                <a href="?p=admin_venue" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <form method="POST">
                                <div class="mb-4 text-center">
                                    <div class="d-inline-flex p-3 rounded-circle bg-warning bg-opacity-10 mb-3">
                                        <i class="bi bi-geo-alt fs-1 text-warning"></i>
                                    </div>
                                    <h5 class="fw-bold">Perbarui Lokasi</h5>
                                    <p class="text-muted small">ID Venue: #<?= $id_venue ?></p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Nama Venue</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-building"></i></span>
                                        <input type="text" name="nama_venue" class="form-control form-control-lg border-0 bg-light" value="<?= htmlspecialchars($data['nama_venue']) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Alamat Lengkap</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-map"></i></span>
                                        <textarea name="alamat" class="form-control form-control-lg border-0 bg-light" rows="3" required><?= htmlspecialchars($data['alamat']) ?></textarea>
                                    </div>
                                </div>

                                <div class="mb-5">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Kapasitas Maksimal</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-people"></i></span>
                                        <input type="number" name="kapasitas" class="form-control form-control-lg border-0 bg-light" value="<?= $data['kapasitas'] ?>" required>
                                        <span class="input-group-text bg-light border-0">Orang</span>
                                    </div>
                                </div>

                                <div class="d-grid pt-2">
                                    <button type="submit" name="update" class="btn btn-warning btn-lg fw-bold shadow-sm text-dark p-3 rounded-pill">
                                        <i class="bi bi-save-fill me-2"></i> Simpan Perubahan
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
