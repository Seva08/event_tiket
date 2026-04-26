<?php
if (isset($_POST['simpan'])) {
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
        
        $total_kuota_sekarang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(kuota) as total FROM tiket WHERE id_event = $id_event"))['total'] ?? 0;
        $sisa_kapasitas = $kapasitas_venue - $total_kuota_sekarang;

        if ($kuota > $sisa_kapasitas) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'title' => 'Kuota Melebihi Batas!',
                'text' => "Kapasitas Venue: $kapasitas_venue\nTotal Kuota Saat Ini: $total_kuota_sekarang\nMaksimal yang bisa ditambah: $sisa_kapasitas"
            ];
        } else {
            $insert = mysqli_query($conn, "INSERT INTO tiket (id_event, nama_tiket, harga, kuota) VALUES ($id_event, '$nama_tiket', $harga, $kuota)");
            if ($insert) {
                $_SESSION['alert'] = ['type' => 'success', 'title' => 'Berhasil!', 'text' => 'Tiket baru berhasil diterbitkan.'];
                header("Location: ?p=admin_tiket");
                exit;
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal Simpan', 'text' => mysqli_error($conn)];
            }
        }
    }
}

// Data untuk client-side check
$events = mysqli_query($conn, "SELECT e.id_event, e.nama_event, v.kapasitas, 
    (SELECT SUM(kuota) FROM tiket t WHERE t.id_event = e.id_event) as kuota_terpakai
    FROM event e JOIN venue v ON e.id_venue = v.id_venue");
$event_list = [];
while($ev = mysqli_fetch_assoc($event_list_q = $events)) $event_list[] = $ev;
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'pages/admin/_sidebar.php'; ?>
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold"><i class="bi bi-ticket-perforated"></i> Terbitkan Tiket</h2>
                    <p class="text-muted mb-0">Kelola kategori dan kuota tiket per event</p>
                </div>
                <a href="?p=admin_tiket" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <form method="POST" id="formTiket">
                                <div class="mb-4 text-center">
                                    <div class="d-inline-flex p-3 rounded-circle bg-info bg-opacity-10 mb-3">
                                        <i class="bi bi-ticket-detailed fs-1 text-info"></i>
                                    </div>
                                    <h5 class="fw-bold">Kategori Tiket</h5>
                                    <p class="text-muted small">Tentukan harga dan jumlah ketersediaan tiket</p>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Pilih Event</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-event"></i></span>
                                        <select name="id_event" id="selectEvent" class="form-select form-select-lg border-0 bg-light" required>
                                            <option value="">-- Pilih Event --</option>
                                            <?php
                                            $q_ev = mysqli_query($conn, "SELECT e.id_event, e.nama_event FROM event e ORDER BY e.tanggal DESC");
                                            while($ev = mysqli_fetch_assoc($q_ev)) echo "<option value='{$ev['id_event']}'>{$ev['nama_event']}</option>";
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase opacity-75">Nama Kategori Tiket</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="bi bi-tag"></i></span>
                                        <input type="text" name="nama_tiket" class="form-control form-control-lg border-0 bg-light" placeholder="Contoh: VIP, Reguler, Early Bird" required>
                                    </div>
                                </div>

                                <div class="row g-4 mb-5">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Harga Tiket</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0">Rp</span>
                                            <input type="number" name="harga" class="form-control form-control-lg border-0 bg-light" placeholder="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase opacity-75">Kuota (Jumlah)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="bi bi-people"></i></span>
                                            <input type="number" name="kuota" id="inputKuota" class="form-control form-control-lg border-0 bg-light" placeholder="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid pt-2">
                                    <button type="submit" name="simpan" class="btn btn-info text-white btn-lg fw-bold shadow-sm p-3 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-2"></i> Terbitkan Tiket
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
const eventData = <?= json_encode($event_list) ?>;
// I'll skip complex dynamic client side check for now to keep it simple but safe on server side.
</script>
