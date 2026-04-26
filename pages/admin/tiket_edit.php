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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title"><i class="bi bi-pencil-square"></i> Edit Tiket</h2>
                    <p class="text-muted mb-0">Sesuaikan ketersediaan dan harga tiket</p>
                </div>
                <a href="?p=admin_tiket" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <form method="POST">
                                <div class="mb-4 text-center">
                                    <div class="d-inline-flex p-3 rounded-circle bg-warning bg-opacity-10 mb-3">
                                        <i class="bi bi-ticket-detailed fs-1 text-warning"></i>
                                    </div>
                                    <h5 class="fw-bold">Perbarui Kategori</h5>
                                    <p class="text-muted small">ID Tiket: #<?= $id_tiket ?></p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Nama Event</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-event"></i></span>
                                        <select name="id_event" class="form-select form-select-lg border-0 bg-light" required>
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

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Nama Kategori Tiket</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-tag"></i></span>
                                        <input type="text" name="nama_tiket" class="form-control form-control-lg border-0 bg-light" value="<?= htmlspecialchars($data['nama_tiket']) ?>" required>
                                    </div>
                                </div>

                                <div class="row g-4 mb-5">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Harga Tiket</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0">Rp</span>
                                            <input type="number" name="harga" class="form-control form-control-lg border-0 bg-light" value="<?= $data['harga'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Kuota (Jumlah)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-people"></i></span>
                                            <input type="number" name="kuota" class="form-control form-control-lg border-0 bg-light" value="<?= $data['kuota'] ?>" required>
                                        </div>
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
