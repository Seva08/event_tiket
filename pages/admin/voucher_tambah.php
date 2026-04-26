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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title"><i class="bi bi-tag"></i> Tambah Voucher</h2>
                    <p class="text-muted mb-0">Buat promosi diskon untuk pengguna</p>
                </div>
                <a href="?p=admin_voucher" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <form method="POST">
                                <div class="mb-4 text-center">
                                    <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 mb-3">
                                        <i class="bi bi-ticket-perforated fs-1 text-primary"></i>
                                    </div>
                                    <h5 class="fw-bold">Detail Voucher</h5>
                                    <p class="text-muted small">Buat kode unik untuk potongan harga</p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Kode Voucher</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-hash"></i></span>
                                        <input type="text" name="kode_voucher" class="form-control form-control-lg border-0 bg-light fw-bold text-uppercase" placeholder="Contoh: DISKON10" required>
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Potongan Harga</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0">Rp</span>
                                            <input type="number" name="potongan" class="form-control form-control-lg border-0 bg-light" placeholder="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Kuota Penggunaan</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-people"></i></span>
                                            <input type="number" name="kuota" class="form-control form-control-lg border-0 bg-light" placeholder="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-5">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Status Voucher</label>
                                    <select name="status" class="form-select form-select-lg border-0 bg-light" required>
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>

                                <div class="d-grid pt-2">
                                    <button type="submit" name="simpan" class="btn btn-primary btn-lg fw-bold shadow-sm p-3 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-2"></i> Simpan Voucher
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
