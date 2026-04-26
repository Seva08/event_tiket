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
            <!-- Breadcrumb & Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-calendar-plus text-primary me-2"></i>Tambah Event</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item small"><a href="?p=dashboard_admin" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item small"><a href="?p=admin_event" class="text-decoration-none">Master Event</a></li>
                            <li class="breadcrumb-item small active" aria-current="page">Tambah Baru</li>
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
                                <div class="bg-primary bg-opacity-10 text-primary rounded-3 icon-box me-3">
                                    <i class="bi bi-file-earmark-plus fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">Informasi Dasar Event</h5>
                                    <small class="text-muted">Isi formulir berikut untuk mempublikasikan event baru</small>
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
                                            <input type="text" name="nama_event" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" placeholder="Contoh: Konser Musik Harmoni 2024" required>
                                        </div>
                                    </div>

                                    <!-- Tanggal & Venue -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Tanggal Pelaksanaan</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-event"></i></span>
                                            <input type="date" name="tanggal" id="inputTanggal" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Lokasi Venue</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-geo-alt"></i></span>
                                            <select name="id_venue" id="selectVenue" class="form-select form-select-lg border-0 bg-light shadow-none fs-6" required>
                                                <option value="">-- Pilih Venue --</option>
                                                <?php
                                                $q_venue = mysqli_query($conn, "SELECT * FROM venue ORDER BY nama_venue ASC");
                                                while($v = mysqli_fetch_assoc($q_venue)) echo "<option value='{$v['id_venue']}'>{$v['nama_venue']} (Kap: {$v['kapasitas']})</option>";
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Limit Tiket -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Limit Beli Per User</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-person-lock"></i></span>
                                            <input type="number" name="limit_tiket" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="5" min="1" required>
                                        </div>
                                        <div class="form-text small opacity-75">Maksimal tiket yang dapat dipesan satu akun.</div>
                                    </div>

                                    <!-- Banner Image -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Banner Promosi (16:9)</label>
                                        <div class="upload-area p-4 border border-2 border-dashed rounded-4 text-center bg-light transition-all" id="dropZone">
                                            <div id="previewContainer" class="d-none mb-3">
                                                <img id="imgPreview" src="#" alt="Preview" class="rounded-3 shadow-sm border img-fluid" style="max-height: 250px;">
                                                <div class="mt-2">
                                                    <button type="button" id="btnRemoveImg" class="btn btn-sm btn-danger rounded-pill px-3"><i class="bi bi-x-circle me-1"></i>Ganti Gambar</button>
                                                </div>
                                            </div>
                                            <div id="uploadPlaceholder">
                                                <i class="bi bi-cloud-arrow-up text-primary display-4 mb-2 d-block"></i>
                                                <p class="mb-3 text-muted small">Tarik file ke sini atau klik tombol di bawah</p>
                                                <input type="file" name="gambar" id="inputGambar" class="form-control border-0 bg-white shadow-sm rounded-pill" accept="image/*">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-12 pt-3">
                                        <button type="submit" name="simpan" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold w-100 shadow">
                                            <i class="bi bi-cloud-check-fill me-2"></i>Simpan & Publikasikan Event
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
.upload-area:hover { border-color: var(--bs-primary) !important; background-color: #f8f9fa !important; }
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
