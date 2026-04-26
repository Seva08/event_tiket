<?php
if (isset($_POST['simpan'])) {
    $kode_voucher = strtoupper(mysqli_real_escape_string($conn, $_POST['kode_voucher']));
    $potongan     = (int)$_POST['potongan'];
    $kuota        = (int)$_POST['kuota'];
    $status       = mysqli_real_escape_string($conn, $_POST['status']);

    $cek = mysqli_query($conn, "SELECT * FROM voucher WHERE kode_voucher = '$kode_voucher'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Kode Sudah Ada', 'text' => "Kode voucher $kode_voucher sudah digunakan!"];
    } else {
        $insert = mysqli_query($conn, "INSERT INTO voucher (kode_voucher, potongan, kuota, status) VALUES ('$kode_voucher', $potongan, $kuota, '$status')");
        if ($insert) {
            $_SESSION['alert'] = ['type' => 'success', 'title' => 'Berhasil!', 'text' => 'Voucher baru berhasil ditambahkan.'];
            header("Location: ?p=admin_voucher");
            exit;
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal Simpan', 'text' => mysqli_error($conn)];
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
                    <h2 class="fw-bold mb-1"><i class="bi bi-tags text-primary me-2"></i>Tambah Voucher</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item small"><a href="?p=dashboard_admin" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item small"><a href="?p=admin_voucher" class="text-decoration-none">Master Voucher</a></li>
                            <li class="breadcrumb-item small active" aria-current="page">Tambah Baru</li>
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
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle icon-box d-inline-flex mb-3" style="width: 70px; height: 70px;">
                                <i class="bi bi-ticket-perforated-fill fs-2"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Buat Voucher Promo</h5>
                            <small class="text-muted">Tentukan kode unik dan besaran potongan harga</small>
                        </div>
                        <div class="card-body px-4 px-md-5 pb-5">
                            <form method="POST">
                                <div class="row g-4">
                                    <!-- Kode Voucher -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Kode Voucher</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" name="kode_voucher" class="form-control form-control-lg border-0 bg-light shadow-none fs-6 fw-bold text-uppercase" placeholder="Contoh: PROMOAKHIRTAHUN" required>
                                        </div>
                                    </div>

                                    <!-- Potongan & Kuota -->
                                    <div class="col-md-7">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Potongan Harga</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 fw-bold">Rp</span>
                                            <input type="number" name="potongan" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" placeholder="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Kuota Pakai</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-calculator"></i></span>
                                            <input type="number" name="kuota" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" placeholder="0" required>
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Status Awal</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-toggle-on"></i></span>
                                            <select name="status" class="form-select form-select-lg border-0 bg-light shadow-none fs-6" required>
                                                <option value="aktif">Aktif</option>
                                                <option value="nonaktif">Nonaktif</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-12 pt-3">
                                        <button type="submit" name="simpan" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold w-100 shadow border-0">
                                            <i class="bi bi-cloud-plus-fill me-2"></i>Simpan Voucher
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
