<?php
$events = mysqli_query($conn, "SELECT * FROM event ORDER BY tanggal DESC");
if (isset($_POST['simpan'])) {
    $id_event   = (int)$_POST['id_event'];
    $nama_tiket = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $harga      = (int)$_POST['harga'];
    $kuota      = (int)$_POST['kuota'];
    $insert = mysqli_query($conn, "INSERT INTO tiket (id_event, nama_tiket, harga, kuota) VALUES ('$id_event', '$nama_tiket', '$harga', '$kuota')");
    if ($insert) { echo "<script>alert('Tiket berhasil ditambahkan!'); window.location='?p=admin_tiket';</script>"; }
    else { echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>"; }
}
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2><i class="bi bi-plus-circle"></i> Tambah Tiket</h2>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="?p=dashboard_admin">Dashboard</a></li><li class="breadcrumb-item"><a href="?p=admin_tiket">Tiket</a></li><li class="breadcrumb-item active">Tambah</li></ol></nav>
        <div class="card"><div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Event <span class="text-danger">*</span></label>
                    <select name="id_event" class="form-select" required><option value="">-- Pilih Event --</option>
                    <?php while ($e = mysqli_fetch_assoc($events)): ?><option value="<?= $e['id_event'] ?>"><?= htmlspecialchars($e['nama_event']) ?> (<?= $e['tanggal'] ?>)</option><?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Nama Tiket <span class="text-danger">*</span></label><input type="text" name="nama_tiket" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Harga (Rp) <span class="text-danger">*</span></label><input type="number" name="harga" class="form-control" min="0" required></div>
                <div class="mb-3"><label class="form-label">Kuota <span class="text-danger">*</span></label><input type="number" name="kuota" class="form-control" min="0" required></div>
                <button type="submit" name="simpan" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="?p=admin_tiket" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </form>
        </div></div>
    </main>
</div></div>
