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
            <!-- Breadcrumb & Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Voucher</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item small"><a href="?p=dashboard_admin" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item small"><a href="?p=admin_voucher" class="text-decoration-none">Master Voucher</a></li>
                            <li class="breadcrumb-item small active" aria-current="page">Edit Data</li>
                        </ol>
                    </nav>
                </div>
                <a href="?p=admin_voucher" class="btn btn-light rounded-pill px-4 border shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Batal & Kembali
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-4 px-4 px-md-5 text-center">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle icon-box d-inline-flex mb-3" style="width: 70px; height: 70px;">
                                <i class="bi bi-ticket-perforated-fill fs-2"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Konfigurasi Voucher</h5>
                            <small class="text-muted">Perbarui promosi dan kuota ID #VCR-<?= $id_voucher ?></small>
                        </div>
                        <div class="card-body px-4 px-md-5 pb-5">
                            <form method="POST">
                                <div class="row g-4">
                                    <!-- Kode Voucher -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Kode Voucher</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" name="kode_voucher" class="form-control form-control-lg border-0 bg-light shadow-none fs-6 fw-bold text-uppercase" value="<?= htmlspecialchars($data['kode_voucher']) ?>" placeholder="Contoh: PROMOAKHIRTAHUN" required>
                                        </div>
                                    </div>

                                    <!-- Potongan & Kuota -->
                                    <div class="col-md-7">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Potongan Harga</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 fw-bold">Rp</span>
                                            <input type="number" name="potongan" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="<?= $data['potongan'] ?>" placeholder="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Kuota Pakai</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-calculator"></i></span>
                                            <input type="number" name="kuota" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="<?= $data['kuota'] ?>" placeholder="0" required>
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Status Voucher</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-toggle-on"></i></span>
                                            <select name="status" class="form-select form-select-lg border-0 bg-light shadow-none fs-6" required>
                                                <option value="aktif" <?= $data['status'] == 'aktif' ? 'selected' : '' ?>>Aktif (Bisa Digunakan)</option>
                                                <option value="nonaktif" <?= $data['status'] == 'nonaktif' ? 'selected' : '' ?>>Nonaktif (Draft)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-12 pt-3">
                                        <button type="submit" name="update" class="btn btn-warning btn-lg rounded-pill px-5 py-3 fw-bold w-100 shadow border-0 text-white">
                                            <i class="bi bi-save-fill me-2"></i>Simpan Perubahan Voucher
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
