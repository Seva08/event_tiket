<?php
if (!isset($_GET['id'])) { header("Location: ?p=admin_event"); exit; }
$id_event = (int)$_GET['id'];
$data   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM event WHERE id_event = $id_event"));
$venues = mysqli_query($conn, "SELECT * FROM venue ORDER BY nama_venue");

// Ambil semua jadwal event kecuali event ini untuk validasi client-side
$all_events = [];
$q_all = mysqli_query($conn, "SELECT id_venue, tanggal, nama_event FROM event WHERE id_event != $id_event");
while ($row = mysqli_fetch_assoc($q_all)) {
    $all_events[] = $row;
}
$json_events = json_encode($all_events);
if (!$data) { echo "<script>alert('Data tidak ditemukan!'); window.location='?p=admin_event';</script>"; exit; }

if (isset($_POST['update'])) {
    $nama_event = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tanggal    = $_POST['tanggal'];
    $id_venue   = (int)$_POST['id_venue'];
    
    // Cek bentrok event
    $q_bentrok = mysqli_query($conn, "SELECT nama_event FROM event WHERE id_venue = '$id_venue' AND tanggal = '$tanggal' AND id_event != $id_event");
    if (mysqli_num_rows($q_bentrok) > 0) {
        $bentrok = mysqli_fetch_assoc($q_bentrok);
        $nama_bentrok = addslashes($bentrok['nama_event']);
        echo "<script>alert('Gagal! Sudah ada event lain ($nama_bentrok) di venue ini pada tanggal tersebut.');</script>";
    } else {
        $gambar_query = "";
        if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0){
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if(in_array($ext, $allowed)){
                $gambar_name = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $gambar_name);
                if($data['gambar'] && file_exists('uploads/' . $data['gambar'])){
                    unlink('uploads/' . $data['gambar']);
                }
                $gambar_query = ", gambar='$gambar_name'";
            } else {
                echo "<script>alert('Format gambar tidak didukung!');</script>";
            }
        }
        $update = mysqli_query($conn, "UPDATE event SET nama_event='$nama_event', tanggal='$tanggal', id_venue='$id_venue' $gambar_query WHERE id_event=$id_event");
        if ($update) {
            echo "<script>alert('Event berhasil diupdate!'); window.location='?p=admin_event';</script>";
        } else {
            echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>
<div class="container-fluid"><div class="row">
    <?php include 'pages/admin/_sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <h2><i class="bi bi-pencil-square"></i> Edit Event</h2>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=dashboard_admin">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="?p=admin_event">Event</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
        <div class="card"><div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3"><label class="form-label">Nama Event <span class="text-danger">*</span></label><input type="text" name="nama_event" class="form-control" value="<?= htmlspecialchars($data['nama_event']) ?>" required></div>
                <div class="mb-3">
                    <label class="form-label">Gambar Event <span class="text-muted">(Opsional)</span></label>
                    <?php if($data['gambar']): ?>
                        <div class="mb-2"><img src="uploads/<?= $data['gambar'] ?>" alt="Event Image" class="img-thumbnail" style="max-height: 150px;"></div>
                    <?php endif; ?>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                </div>
                <div class="mb-3"><label class="form-label">Tanggal Event <span class="text-danger">*</span></label><input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal'] ?>" required></div>
                <div class="mb-3">
                    <label class="form-label">Venue <span class="text-danger">*</span></label>
                    <select name="id_venue" class="form-select" required>
                        <option value="">-- Pilih Venue --</option>
                        <?php while ($v = mysqli_fetch_assoc($venues)): ?>
                            <option value="<?= $v['id_venue'] ?>" <?= $v['id_venue'] == $data['id_venue'] ? 'selected' : '' ?>><?= htmlspecialchars($v['nama_venue']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="update" class="btn btn-warning"><i class="bi bi-save"></i> Update</button>
                <a href="?p=admin_event" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </form>
        </div></div>
    </main>
</div></div>

<script>
const scheduledEvents = <?= $json_events ?>;
document.querySelector('form').addEventListener('submit', function(e) {
    const inputTanggal = document.querySelector('input[name="tanggal"]').value;
    const selectVenue = document.querySelector('select[name="id_venue"]').value;
    
    if (inputTanggal && selectVenue) {
        const conflict = scheduledEvents.find(ev => ev.id_venue == selectVenue && ev.tanggal === inputTanggal);
        if (conflict) {
            e.preventDefault();
            alert('Gagal! Sudah ada event lain (' + conflict.nama_event + ') di venue ini pada tanggal tersebut.');
        }
    }
});
</script>
