<?php
if (!isset($_GET['id'])) { header("Location: ?p=admin_event"); exit; }
$id_event = (int)$_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM event WHERE id_event = $id_event"));
if (!$data) { 
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Error', 'text' => 'Data tidak ditemukan!'];
    header("Location: ?p=admin_event"); 
    exit; 
}

if (isset($_POST['update'])) {
    $nama_event  = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tanggal     = $_POST['tanggal'];
    $id_venue    = (int)$_POST['id_venue'];
    $limit_tiket = (int)$_POST['limit_tiket'];
    
    $q_bentrok = mysqli_query($conn, "SELECT nama_event FROM event WHERE id_venue = '$id_venue' AND tanggal = '$tanggal' AND id_event != $id_event");
    if (mysqli_num_rows($q_bentrok) > 0) {
        $bentrok = mysqli_fetch_assoc($q_bentrok);
        $nama_bentrok = addslashes($bentrok['nama_event']);
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Jadwal Bentrok!',
            'text' => "Sudah ada event lain ($nama_bentrok) di venue ini pada tanggal tersebut."
        ];
    } else {
        $gambar_query = "";
        $upload_ok = true;
        if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0){
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if(in_array($ext, $allowed)){
                $gambar_name = time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $gambar_name);
                if($data['gambar'] && file_exists('uploads/' . $data['gambar'])) unlink('uploads/' . $data['gambar']);
                $gambar_query = ", gambar='$gambar_name'";
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'title' => 'Format Salah', 'text' => 'Format gambar tidak didukung!'];
                $upload_ok = false;
            }
        }

        if ($upload_ok) {
            $update = mysqli_query($conn, "UPDATE event SET nama_event='$nama_event', tanggal='$tanggal', id_venue=$id_venue, limit_tiket=$limit_tiket $gambar_query WHERE id_event=$id_event");
            if ($update) {
                $_SESSION['alert'] = ['type' => 'success', 'title' => 'Berhasil!', 'text' => 'Data event telah diperbarui.'];
                header("Location: ?p=admin_event");
                exit;
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal Update', 'text' => mysqli_error($conn)];
            }
        }
    }
}

// Data untuk client check
$q_all = mysqli_query($conn, "SELECT id_venue, tanggal, nama_event FROM event WHERE id_event != $id_event");
$all_events = [];
while ($row = mysqli_fetch_assoc($q_all)) $all_events[] = $row;
$json_events = json_encode($all_events);
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold"><i class="bi bi-pencil-square"></i> Edit Event</h2>
                    <p class="text-muted mb-0">Perbarui informasi event kamu</p>
                </div>
                <a href="?p=admin_event" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <form method="POST" enctype="multipart/form-data" id="formEvent">
                                <div class="mb-4 text-center">
                                    <div class="d-inline-flex p-3 rounded-circle bg-warning bg-opacity-10 mb-3">
                                        <i class="bi bi-pencil-square fs-1 text-warning"></i>
                                    </div>
                                    <h5 class="fw-bold">Perbarui Detail</h5>
                                    <p class="text-muted small">ID Event: #<?= $id_event ?></p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Nama Event</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-fonts"></i></span>
                                        <input type="text" name="nama_event" class="form-control form-control-lg border-0 bg-light" value="<?= htmlspecialchars($data['nama_event']) ?>" required>
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Tanggal Event</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-calendar3"></i></span>
                                            <input type="date" name="tanggal" id="inputTanggal" class="form-control form-control-lg border-0 bg-light" value="<?= $data['tanggal'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Lokasi Venue</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-geo-alt"></i></span>
                                            <select name="id_venue" id="selectVenue" class="form-select form-select-lg border-0 bg-light" required>
                                                <?php
                                                $q_venue = mysqli_query($conn, "SELECT * FROM venue ORDER BY nama_venue ASC");
                                                while($v = mysqli_fetch_assoc($q_venue)) {
                                                    $sel = ($v['id_venue'] == $data['id_venue']) ? 'selected' : '';
                                                    echo "<option value='{$v['id_venue']}' $sel>{$v['nama_venue']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Limit Tiket Per User</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-shield-lock"></i></span>
                                        <input type="number" name="limit_tiket" class="form-control form-control-lg border-0 bg-light" value="<?= $data['limit_tiket'] ?>" min="1" required>
                                    </div>
                                    <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Berapa maksimal tiket yang bisa dibeli oleh satu akun untuk event ini?</div>
                                </div>

                                <div class="mb-5">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Banner Event</label>
                                    <?php if($data['gambar']): ?>
                                        <div class="mb-3">
                                            <img src="uploads/<?= $data['gambar'] ?>" class="rounded-4 img-fluid shadow-sm object-fit-cover w-100 min-vh-25">
                                            <div class="form-text mt-2 text-warning"><i class="bi bi-info-circle me-1"></i>Mengunggah gambar baru akan menghapus gambar lama.</div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="p-4 border border-2 rounded-4 text-center bg-light position-relative">
                                        <i class="bi bi-cloud-upload text-muted display-6 mb-2 d-block"></i>
                                        <p class="small text-muted mb-3">Pilih gambar baru (Biarkan kosong jika tidak ingin ganti)</p>
                                        <input type="file" name="gambar" class="form-control border-0 bg-white shadow-sm rounded-3" accept="image/*">
                                    </div>
                                </div>

                                <div class="d-grid pt-2">
                                    <button type="submit" name="update" class="btn btn-warning btn-lg fw-bold shadow-sm p-3 rounded-pill">
                                        <i class="bi bi-save-fill me-2"></i> Simpan Perubahan
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

<script>
const scheduledEvents = <?= $json_events ?>;
document.getElementById('formEvent').addEventListener('submit', function(e) {
    const inputTanggal = document.getElementById('inputTanggal').value;
    const selectVenue = document.getElementById('selectVenue').value;
    
    if (inputTanggal && selectVenue) {
        const conflict = scheduledEvents.find(ev => ev.id_venue == selectVenue && ev.tanggal === inputTanggal);
        if (conflict) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Jadwal Bentrok!',
                text: 'Sudah ada event "' + conflict.nama_event + '" di venue ini pada tanggal tersebut.',
                confirmButtonColor: '#6366f1'
            });
        }
    }
});
</script>
