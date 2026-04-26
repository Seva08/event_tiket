<?php
if (isset($_POST['simpan'])) {
    $nama_event  = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tanggal     = $_POST['tanggal'];
    $id_venue    = (int)$_POST['id_venue'];
    $limit_tiket = (int)$_POST['limit_tiket'];
    
    // Cek Bentrok
    $q_bentrok = mysqli_query($conn, "SELECT nama_event FROM event WHERE id_venue = '$id_venue' AND tanggal = '$tanggal'");
    if (mysqli_num_rows($q_bentrok) > 0) {
        $bentrok = mysqli_fetch_assoc($q_bentrok);
        $nama_bentrok = addslashes($bentrok['nama_event']);
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Jadwal Bentrok!',
            'text' => "Sudah ada event lain ($nama_bentrok) di venue ini pada tanggal tersebut."
        ];
    } else {
        $gambar_name = NULL;
        $upload_ok = true;
        if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0){
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if(in_array($ext, $allowed)){
                $gambar_name = time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $gambar_name);
            } else {
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'title' => 'Format Salah',
                    'text' => 'Format gambar tidak didukung! Gunakan JPG, PNG, atau WebP.'
                ];
                $upload_ok = false;
            }
        }

        if ($upload_ok) {
            $val_gambar = $gambar_name ? "'$gambar_name'" : "NULL";
            $insert = mysqli_query($conn, "INSERT INTO event (nama_event, tanggal, id_venue, gambar, limit_tiket) VALUES ('$nama_event', '$tanggal', '$id_venue', $val_gambar, $limit_tiket)");
            if ($insert) {
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Event baru telah ditambahkan.'
                ];
                header("Location: ?p=admin_event");
                exit;
            } else {
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'title' => 'Gagal Simpan',
                    'text' => 'Terjadi kesalahan: ' . mysqli_error($conn)
                ];
            }
        }
    }
}

// Data untuk client-side check
$q_all = mysqli_query($conn, "SELECT id_venue, tanggal, nama_event FROM event");
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
                    <h2 class="page-title"><i class="bi bi-calendar-plus"></i> Tambah Event</h2>
                    <p class="text-muted mb-0">Publikasikan event baru kamu</p>
                </div>
                <a href="?p=admin_event" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: 24px;">
                        <div class="card-body p-4 p-md-5">
                            <form method="POST" enctype="multipart/form-data" id="formEvent">
                                <div class="mb-4 text-center">
                                    <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 mb-3">
                                        <i class="bi bi-calendar-event fs-1 text-primary"></i>
                                    </div>
                                    <h5 class="fw-bold">Detail Event</h5>
                                    <p class="text-muted small">Lengkapi informasi di bawah ini</p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Nama Event</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-pencil-square"></i></span>
                                        <input type="text" name="nama_event" class="form-control form-control-lg border-0 bg-light" placeholder="Masukkan nama event..." required style="border-radius: 0 12px 12px 0;">
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Tanggal Event</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-calendar3"></i></span>
                                            <input type="date" name="tanggal" id="inputTanggal" class="form-control form-control-lg border-0 bg-light" required style="border-radius: 0 12px 12px 0;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Lokasi Venue</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-geo-alt"></i></span>
                                            <select name="id_venue" id="selectVenue" class="form-select form-select-lg border-0 bg-light" required style="border-radius: 0 12px 12px 0;">
                                                <option value="">-- Pilih Venue --</option>
                                                <?php
                                                $q_venue = mysqli_query($conn, "SELECT * FROM venue ORDER BY nama_venue ASC");
                                                while($v = mysqli_fetch_assoc($q_venue)) echo "<option value='{$v['id_venue']}'>{$v['nama_venue']} (Kap: {$v['kapasitas']})</option>";
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Limit Tiket Per User</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-shield-lock"></i></span>
                                        <input type="number" name="limit_tiket" class="form-control form-control-lg border-0 bg-light" value="5" min="1" required style="border-radius: 0 12px 12px 0;">
                                    </div>
                                    <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Berapa maksimal tiket yang bisa dibeli oleh satu akun untuk event ini?</div>
                                </div>

                                <div class="mb-5">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Banner Event</label>
                                    <div class="p-4 border-2 border-dashed rounded-4 text-center bg-light position-relative" style="border-style: dashed !important; border-color: #cbd5e1 !important;">
                                        <i class="bi bi-cloud-upload text-muted display-6 mb-2 d-block"></i>
                                        <p class="small text-muted mb-3">Klik atau drag gambar banner ke sini (16:9 disarankan)</p>
                                        <input type="file" name="gambar" class="form-control border-0 bg-white shadow-sm" accept="image/*" style="border-radius: 10px;">
                                    </div>
                                </div>

                                <div class="d-grid pt-2">
                                    <button type="submit" name="simpan" class="btn btn-primary btn-lg fw-bold shadow-sm" style="border-radius: 50px; padding: 16px;">
                                        <i class="bi bi-check-circle-fill me-2"></i> Publikasikan Event
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
