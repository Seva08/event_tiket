<?php
if (isset($_POST['simpan'])) {
    $kode_voucher = mysqli_real_escape_string($conn, strtoupper($_POST['kode_voucher']));
    $potongan     = (int)$_POST['potongan'];
    $kuota        = (int)$_POST['kuota'];
    $status       = $_POST['status'];
    $cek = mysqli_query($conn, "SELECT * FROM voucher WHERE kode_voucher = '$kode_voucher'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Kode voucher sudah ada!');</script>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO voucher (kode_voucher, potongan, kuota, status) VALUES ('$kode_voucher', '$potongan', '$kuota', '$status')");
        if ($insert) { echo "<script>alert('Voucher berhasil ditambahkan!'); window.location='?p=admin_voucher';</script>"; }
        else { echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>"; }
    }
}
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2><i class="bi bi-plus-circle"></i> Tambah Voucher</h2>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="?p=dashboard_admin">Dashboard</a></li><li class="breadcrumb-item"><a href="?p=admin_voucher">Voucher</a></li><li class="breadcrumb-item active">Tambah</li></ol></nav>
        <div class="card"><div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Kode Voucher <span class="text-danger">*</span></label><input type="text" name="kode_voucher" class="form-control text-uppercase" placeholder="Contoh: DISKON10" required></div>
                <div class="mb-3"><label class="form-label">Potongan Harga (Rp) <span class="text-danger">*</span></label><input type="number" name="potongan" class="form-control" min="0" required></div>
                <div class="mb-3"><label class="form-label">Kuota Penggunaan</label><input type="number" name="kuota" class="form-control" min="0" value="0"><small class="text-muted">0 = Unlimited</small></div>
                <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select></div>
                <button type="submit" name="simpan" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="?p=admin_voucher" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </form>
        </div></div>
    </main>
</div></div>
