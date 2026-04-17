<?php
if (!isset($_GET['id'])) { header("Location: ?p=admin_tiket"); exit; }
$id_tiket = (int)$_GET['id'];
$data   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tiket WHERE id_tiket = $id_tiket"));
$events = mysqli_query($conn, "SELECT * FROM event ORDER BY tanggal DESC");
if (!$data) { echo "<script>alert('Data tidak ditemukan!'); window.location='?p=admin_tiket';</script>"; exit; }
if (isset($_POST['update'])) {
    $id_event   = (int)$_POST['id_event'];
    $nama_tiket = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $harga      = (int)$_POST['harga'];
    $kuota      = (int)$_POST['kuota'];
    $update = mysqli_query($conn, "UPDATE tiket SET id_event='$id_event', nama_tiket='$nama_tiket', harga='$harga', kuota='$kuota' WHERE id_tiket=$id_tiket");
    if ($update) { echo "<script>alert('Tiket berhasil diupdate!'); window.location='?p=admin_tiket';</script>"; }
    else { echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>"; }
}
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2><i class="bi bi-pencil-square"></i> Edit Tiket</h2>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="?p=dashboard_admin">Dashboard</a></li><li class="breadcrumb-item"><a href="?p=admin_tiket">Tiket</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
        <div class="card"><div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Event <span class="text-danger">*</span></label>
                    <select name="id_event" class="form-select" required><option value="">-- Pilih Event --</option>
                    <?php while ($e = mysqli_fetch_assoc($events)): ?><option value="<?= $e['id_event'] ?>" <?= $e['id_event']==$data['id_event']?'selected':'' ?>><?= htmlspecialchars($e['nama_event']) ?> (<?= $e['tanggal'] ?>)</option><?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Nama Tiket <span class="text-danger">*</span></label><input type="text" name="nama_tiket" class="form-control" value="<?= htmlspecialchars($data['nama_tiket']) ?>" required></div>
                <div class="mb-3"><label class="form-label">Harga (Rp) <span class="text-danger">*</span></label><input type="number" name="harga" class="form-control" value="<?= $data['harga'] ?>" min="0" required></div>
                <div class="mb-3"><label class="form-label">Kuota <span class="text-danger">*</span></label><input type="number" name="kuota" class="form-control" value="<?= $data['kuota'] ?>" min="0" required></div>
                <button type="submit" name="update" class="btn btn-warning"><i class="bi bi-save"></i> Update</button>
                <a href="?p=admin_tiket" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </form>
        </div></div>
    </main>
</div></div>
