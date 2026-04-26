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
            <!-- Breadcrumb & Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Event</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item small"><a href="?p=dashboard_admin" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item small"><a href="?p=admin_event" class="text-decoration-none">Master Event</a></li>
                            <li class="breadcrumb-item small active" aria-current="page">Edit Data</li>
                        </ol>
                    </nav>
                </div>
                <a href="?p=admin_event" class="btn btn-light rounded-pill px-4 border shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Batal & Kembali
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-4 px-4 px-md-5">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 text-warning rounded-3 icon-box me-3">
                                    <i class="bi bi-gear-fill fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">Konfigurasi Event</h5>
                                    <small class="text-muted">Perbarui data event ID #EVT-<?= $id_event ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body px-4 px-md-5 pb-5">
                            <form method="POST" enctype="multipart/form-data" id="formEvent">
                                <div class="row g-4">
                                    <!-- Nama Event -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Nama Lengkap Event</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-type"></i></span>
                                            <input type="text" name="nama_event" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="<?= htmlspecialchars($data['nama_event']) ?>" placeholder="Contoh: Konser Musik Harmoni 2024" required>
                                        </div>
                                    </div>

                                    <!-- Tanggal & Venue -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Tanggal Pelaksanaan</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-event"></i></span>
                                            <input type="date" name="tanggal" id="inputTanggal" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="<?= $data['tanggal'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Lokasi Venue</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-geo-alt"></i></span>
                                            <select name="id_venue" id="selectVenue" class="form-select form-select-lg border-0 bg-light shadow-none fs-6" required>
                                                <?php
                                                $q_venue = mysqli_query($conn, "SELECT * FROM venue ORDER BY nama_venue ASC");
                                                while($v = mysqli_fetch_assoc($q_venue)) {
                                                    $sel = ($v['id_venue'] == $data['id_venue']) ? 'selected' : '';
                                                    echo "<option value='{$v['id_venue']}' $sel>{$v['nama_venue']} (Kap: {$v['kapasitas']})</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Limit Tiket -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Limit Beli Per User</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-person-lock"></i></span>
                                            <input type="number" name="limit_tiket" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="<?= $data['limit_tiket'] ?>" min="1" required>
                                        </div>
                                    </div>

                                    <!-- Banner Image -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Banner Promosi</label>
                                        
                                        <div class="row g-3">
                                            <?php if($data['gambar']): ?>
                                            <div class="col-md-4">
                                                <div class="small fw-bold text-muted mb-2"><i class="bi bi-image me-1"></i>Banner Sekarang:</div>
                                                <div class="position-relative">
                                                    <img src="uploads/<?= $data['gambar'] ?>" class="rounded-3 shadow-sm border img-fluid w-100" style="height: 150px; object-fit: cover;">
                                                    <span class="badge bg-dark bg-opacity-75 position-absolute bottom-0 start-0 m-2">Aktif</span>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="<?= $data['gambar'] ? 'col-md-8' : 'col-12' ?>">
                                                <div class="small fw-bold text-muted mb-2"><i class="bi bi-cloud-arrow-up me-1"></i>Ganti Banner:</div>
                                                <div class="upload-area p-4 border border-2 border-dashed rounded-4 text-center bg-light transition-all h-100 d-flex flex-column justify-content-center" id="dropZone">
                                                    <div id="previewContainer" class="d-none">
                                                        <img id="imgPreview" src="#" alt="Preview" class="rounded-3 shadow-sm border img-fluid mb-2" style="max-height: 120px;">
                                                        <div class="mt-1">
                                                            <button type="button" id="btnRemoveImg" class="btn btn-sm btn-danger rounded-pill px-3"><i class="bi bi-x-circle me-1"></i>Batal Ganti</button>
                                                        </div>
                                                    </div>
                                                    <div id="uploadPlaceholder">
                                                        <p class="mb-2 text-muted small">Klik tombol di bawah untuk memilih file baru</p>
                                                        <input type="file" name="gambar" id="inputGambar" class="form-control border-0 bg-white shadow-sm rounded-pill" accept="image/*">
                                                        <div class="form-text small mt-2">Biarkan kosong jika tidak ingin mengubah banner.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-12 pt-3">
                                        <button type="submit" name="update" class="btn btn-warning btn-lg rounded-pill px-5 py-3 fw-bold w-100 shadow border-0 text-white">
                                            <i class="bi bi-save-fill me-2"></i>Simpan Perubahan Event
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.border-dashed { border-style: dashed !important; }
.transition-all { transition: all 0.3s ease; }
.upload-area:hover { border-color: var(--bs-warning) !important; background-color: #fffdf5 !important; }
</style>

<script>
const scheduledEvents = <?= $json_events ?>;
const inputGambar = document.getElementById('inputGambar');
const imgPreview = document.getElementById('imgPreview');
const previewContainer = document.getElementById('previewContainer');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const btnRemoveImg = document.getElementById('btnRemoveImg');

// Preview Image Logic
inputGambar.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imgPreview.src = e.target.result;
            previewContainer.classList.remove('d-none');
            uploadPlaceholder.classList.add('d-none');
        }
        reader.readAsDataURL(file);
    }
});

btnRemoveImg.addEventListener('click', function() {
    inputGambar.value = '';
    previewContainer.classList.add('d-none');
    uploadPlaceholder.classList.remove('d-none');
});

// Conflict Check
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
