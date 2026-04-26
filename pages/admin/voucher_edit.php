<?php
if (!isset($_GET['id'])) { header("Location: ?p=admin_voucher"); exit; }
$id_voucher = (int)$_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM voucher WHERE id_voucher = $id_voucher"));

if (!$data) { 
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Error', 'text' => 'Data voucher tidak ditemukan!'];
    header("Location: ?p=admin_voucher"); exit; 
}

if (isset($_POST['update'])) {
    $kode_voucher = strtoupper(mysqli_real_escape_string($conn, $_POST['kode_voucher']));
    $potongan     = (int)$_POST['potongan'];
    $kuota        = (int)$_POST['kuota'];
    $status       = mysqli_real_escape_string($conn, $_POST['status']);

    $cek = mysqli_query($conn, "SELECT * FROM voucher WHERE kode_voucher = '$kode_voucher' AND id_voucher != $id_voucher");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Kode Sudah Ada', 'text' => "Kode voucher $kode_voucher sudah digunakan!"];
    } else {
        $update = mysqli_query($conn, "UPDATE voucher SET kode_voucher='$kode_voucher', potongan=$potongan, kuota=$kuota, status='$status' WHERE id_voucher=$id_voucher");
        if ($update) {
            $_SESSION['alert'] = ['type' => 'success', 'title' => 'Berhasil!', 'text' => 'Data voucher telah diperbarui.'];
            header("Location: ?p=admin_voucher");
            exit;
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal Update', 'text' => mysqli_error($conn)];
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title"><i class="bi bi-pencil-square"></i> Edit Voucher</h2>
                    <p class="text-muted mb-0">Sesuaikan promosi diskon kamu</p>
                </div>
                <a href="?p=admin_voucher" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <form method="POST">
                                <div class="mb-4 text-center">
                                    <div class="d-inline-flex p-3 rounded-circle bg-warning bg-opacity-10 mb-3">
                                        <i class="bi bi-ticket-perforated fs-1 text-warning"></i>
                                    </div>
                                    <h5 class="fw-bold">Perbarui Voucher</h5>
                                    <p class="text-muted small">ID Voucher: #<?= $id_voucher ?></p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Kode Voucher</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-hash"></i></span>
                                        <input type="text" name="kode_voucher" class="form-control form-control-lg border-0 bg-light fw-bold text-uppercase" value="<?= htmlspecialchars($data['kode_voucher']) ?>" required>
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Potongan Harga</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0">Rp</span>
                                            <input type="number" name="potongan" class="form-control form-control-lg border-0 bg-light" value="<?= $data['potongan'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Kuota Penggunaan</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-people"></i></span>
                                            <input type="number" name="kuota" class="form-control form-control-lg border-0 bg-light" value="<?= $data['kuota'] ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-5">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Status Voucher</label>
                                    <select name="status" class="form-select form-select-lg border-0 bg-light rounded-3" required>
                                        <option value="aktif" <?= $data['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="nonaktif" <?= $data['status'] == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                    </select>
                                </div>

                                <div class="d-grid pt-2">
                                    <button type="submit" name="update" class="btn btn-warning btn-lg fw-bold shadow-sm p-3 rounded-pill">
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
