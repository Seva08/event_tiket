<?php
if (!isset($_GET['id'])) { header("Location: ?p=admin_voucher"); exit; }
$id_voucher = (int)$_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM voucher WHERE id_voucher = $id_voucher"));
if (!$data) { echo "<script>alert('Data tidak ditemukan!'); window.location='?p=admin_voucher';</script>"; exit; }
if (isset($_POST['update'])) {
    $potongan = (int)$_POST['potongan'];
    $kuota    = (int)$_POST['kuota'];
    $status   = $_POST['status'];
    $update = mysqli_query($conn, "UPDATE voucher SET potongan='$potongan', kuota='$kuota', status='$status' WHERE id_voucher=$id_voucher");
    if ($update) { echo "<script>alert('Voucher berhasil diupdate!'); window.location='?p=admin_voucher';</script>"; }
    else { echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>"; }
}
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2><i class="bi bi-pencil-square"></i> Edit Voucher</h2>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="?p=dashboard_admin">Dashboard</a></li><li class="breadcrumb-item"><a href="?p=admin_voucher">Voucher</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
        <div class="card"><div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Kode Voucher</label><input type="text" class="form-control" value="<?= htmlspecialchars($data['kode_voucher']) ?>" disabled><small class="text-muted">Kode voucher tidak dapat diubah</small></div>
                <div class="mb-3"><label class="form-label">Potongan Harga (Rp) <span class="text-danger">*</span></label><input type="number" name="potongan" class="form-control" value="<?= $data['potongan'] ?>" min="0" required></div>
                <div class="mb-3"><label class="form-label">Kuota Penggunaan</label><input type="number" name="kuota" class="form-control" value="<?= $data['kuota'] ?>" min="0"><small class="text-muted">0 = Unlimited</small></div>
                <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="aktif" <?= $data['status']=='aktif'?'selected':'' ?>>Aktif</option><option value="nonaktif" <?= $data['status']=='nonaktif'?'selected':'' ?>>Nonaktif</option></select></div>
                <button type="submit" name="update" class="btn btn-warning"><i class="bi bi-save"></i> Update</button>
                <a href="?p=admin_voucher" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </form>
        </div></div>
    </main>
</div></div>
