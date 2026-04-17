<?php
if (!isset($_GET['id'])) { header("Location: ?p=admin_venue"); exit; }
$id_venue = (int)$_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM venue WHERE id_venue = $id_venue"));
if (!$data) { echo "<script>alert('Data tidak ditemukan!'); window.location='?p=admin_venue';</script>"; exit; }

if (isset($_POST['update'])) {
    $nama_venue = mysqli_real_escape_string($conn, $_POST['nama_venue']);
    $alamat     = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kapasitas  = (int)$_POST['kapasitas'];
    $update = mysqli_query($conn, "UPDATE venue SET nama_venue='$nama_venue', alamat='$alamat', kapasitas='$kapasitas' WHERE id_venue=$id_venue");
    if ($update) {
        echo "<script>alert('Venue berhasil diupdate!'); window.location='?p=admin_venue';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2><i class="bi bi-pencil-square"></i> Edit Venue</h2>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=dashboard_admin">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="?p=admin_venue">Venue</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
        <div class="card"><div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Nama Venue <span class="text-danger">*</span></label><input type="text" name="nama_venue" class="form-control" value="<?= htmlspecialchars($data['nama_venue']) ?>" required></div>
                <div class="mb-3"><label class="form-label">Alamat <span class="text-danger">*</span></label><textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($data['alamat']) ?></textarea></div>
                <div class="mb-3"><label class="form-label">Kapasitas <span class="text-danger">*</span></label><input type="number" name="kapasitas" class="form-control" value="<?= $data['kapasitas'] ?>" min="1" required></div>
                <button type="submit" name="update" class="btn btn-warning"><i class="bi bi-save"></i> Update</button>
                <a href="?p=admin_venue" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </form>
        </div></div>
    </main>
</div></div>
