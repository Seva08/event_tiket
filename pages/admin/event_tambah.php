<?php
$venues = mysqli_query($conn, "SELECT * FROM venue ORDER BY nama_venue");
if (isset($_POST['simpan'])) {
    $nama_event = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tanggal    = $_POST['tanggal'];
    $id_venue   = (int)$_POST['id_venue'];
    $insert = mysqli_query($conn, "INSERT INTO event (nama_event, tanggal, id_venue) VALUES ('$nama_event', '$tanggal', '$id_venue')");
    if ($insert) {
        echo "<script>alert('Event berhasil ditambahkan!'); window.location='?p=admin_event';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2><i class="bi bi-plus-circle"></i> Tambah Event</h2>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=dashboard_admin">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="?p=admin_event">Event</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol></nav>
        <div class="card"><div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Nama Event <span class="text-danger">*</span></label><input type="text" name="nama_event" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Tanggal Event <span class="text-danger">*</span></label><input type="date" name="tanggal" class="form-control" required></div>
                <div class="mb-3">
                    <label class="form-label">Venue <span class="text-danger">*</span></label>
                    <select name="id_venue" class="form-select" required>
                        <option value="">-- Pilih Venue --</option>
                        <?php while ($v = mysqli_fetch_assoc($venues)): ?>
                            <option value="<?= $v['id_venue'] ?>"><?= htmlspecialchars($v['nama_venue']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="simpan" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="?p=admin_event" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </form>
        </div></div>
    </main>
</div></div>
