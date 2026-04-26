<?php
if (!isset($_GET['id'])) { header("Location: ?p=admin_tiket"); exit; }
$id_tiket = (int)$_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tiket WHERE id_tiket = $id_tiket"));
if (!$data) { 
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Error', 'text' => 'Data tiket tidak ditemukan!'];
    header("Location: ?p=admin_tiket"); exit; 
}

if (isset($_POST['update'])) {
    $id_event   = (int)$_POST['id_event'];
    $nama_tiket = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $harga      = (int)$_POST['harga'];
    $kuota      = (int)$_POST['kuota'];

    if ($harga <= 0) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Harga tiket tidak boleh 0 atau kurang!'];
    } elseif ($kuota <= 0) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Kuota tiket tidak boleh 0 atau kurang!'];
    } else {
        // Validasi Kapasitas Venue
        $event_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT e.id_venue, v.kapasitas FROM event e JOIN venue v ON e.id_venue = v.id_venue WHERE e.id_event = $id_event"));
        $kapasitas_venue = $event_data['kapasitas'];
        
        $total_kuota_lain = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(kuota) as total FROM tiket WHERE id_event = $id_event AND id_tiket != $id_tiket"))['total'] ?? 0;
        $sisa_kapasitas = $kapasitas_venue - $total_kuota_lain;

        if ($kuota > $sisa_kapasitas) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'title' => 'Kuota Melebihi Batas!',
                'text' => "Kapasitas Venue: $kapasitas_venue\nSisa Kuota Tersedia: $sisa_kapasitas"
            ];
        } else {
            $update = mysqli_query($conn, "UPDATE tiket SET id_event=$id_event, nama_tiket='$nama_tiket', harga=$harga, kuota=$kuota WHERE id_tiket=$id_tiket");
            if ($update) {
                $_SESSION['alert'] = ['type' => 'success', 'title' => 'Berhasil!', 'text' => 'Data tiket telah diperbarui.'];
                header("Location: ?p=admin_tiket");
                exit;
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal Update', 'text' => mysqli_error($conn)];
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <!-- Breadcrumb & Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Tiket</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item small"><a href="?p=dashboard_admin" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item small"><a href="?p=admin_tiket" class="text-decoration-none">Master Tiket</a></li>
                            <li class="breadcrumb-item small active" aria-current="page">Edit Data</li>
                        </ol>
                    </nav>
                </div>
                <a href="?p=admin_tiket" class="btn btn-light rounded-pill px-4 border shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Batal & Kembali
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-4 px-4 px-md-5 text-center">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle icon-box d-inline-flex mb-3" style="width: 70px; height: 70px;">
                                <i class="bi bi-ticket-detailed-fill fs-2"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Konfigurasi Tiket</h5>
                            <small class="text-muted">Perbarui kategori dan kuota tiket ID #TKT-<?= $id_tiket ?></small>
                        </div>
                        <div class="card-body px-4 px-md-5 pb-5">
                            <form method="POST">
                                <div class="row g-4">
                                    <!-- Pilih Event -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Pilih Event</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-check"></i></span>
                                            <select name="id_event" class="form-select form-select-lg border-0 bg-light shadow-none fs-6" required>
                                                <?php
                                                $q_ev = mysqli_query($conn, "SELECT id_event, nama_event FROM event ORDER BY tanggal DESC");
                                                while($ev = mysqli_fetch_assoc($q_ev)) {
                                                    $sel = ($ev['id_event'] == $data['id_event']) ? 'selected' : '';
                                                    echo "<option value='{$ev['id_event']}' $sel>{$ev['nama_event']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Nama Tiket -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Nama Kategori Tiket</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-tag"></i></span>
                                            <input type="text" name="nama_tiket" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="<?= htmlspecialchars($data['nama_tiket']) ?>" placeholder="Contoh: VIP, Reguler, Presale" required>
                                        </div>
                                    </div>

                                    <!-- Harga & Kuota -->
                                    <div class="col-md-7">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Harga Tiket</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 fw-bold">Rp</span>
                                            <input type="number" name="harga" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="<?= $data['harga'] ?>" placeholder="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold text-muted small text-uppercase">Jumlah Kuota</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-box-seam"></i></span>
                                            <input type="number" name="kuota" class="form-control form-control-lg border-0 bg-light shadow-none fs-6" value="<?= $data['kuota'] ?>" placeholder="0" required>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-12 pt-3">
                                        <button type="submit" name="update" class="btn btn-warning btn-lg rounded-pill px-5 py-3 fw-bold w-100 shadow border-0 text-white">
                                            <i class="bi bi-save-fill me-2"></i>Simpan Perubahan Tiket
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
