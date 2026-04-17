<?php
if (isset($_POST['simpan'])) {
    $nama_venue = mysqli_real_escape_string($conn, $_POST['nama_venue']);
    $alamat     = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kapasitas  = (int)$_POST['kapasitas'];
    $insert = mysqli_query($conn, "INSERT INTO venue (nama_venue, alamat, kapasitas) VALUES ('$nama_venue', '$alamat', '$kapasitas')");
    if ($insert) {
        echo "<script>alert('Venue berhasil ditambahkan!'); window.location='?p=admin_venue';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2><i class="bi bi-plus-circle"></i> Tambah Venue</h2>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=dashboard_admin">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="?p=admin_venue">Venue</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol></nav>
        <div class="card"><div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Nama Venue <span class="text-danger">*</span></label><input type="text" name="nama_venue" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Alamat <span class="text-danger">*</span></label><textarea name="alamat" class="form-control" rows="3" required></textarea></div>
                <div class="mb-3"><label class="form-label">Kapasitas <span class="text-danger">*</span></label><input type="number" name="kapasitas" class="form-control" min="1" required></div>
                <button type="submit" name="simpan" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="?p=admin_venue" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </form>
        </div></div>
    </main>
</div></div>
