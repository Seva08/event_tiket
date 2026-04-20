<?php
$events = mysqli_query($conn, "
    SELECT e.*, v.kapasitas, 
           COALESCE((SELECT SUM(kuota) FROM tiket WHERE id_event = e.id_event), 0) as total_kuota 
    FROM event e 
    JOIN venue v ON e.id_venue = v.id_venue 
    ORDER BY e.tanggal DESC
");
if (isset($_POST['simpan'])) {
    $id_event   = (int)$_POST['id_event'];
    $nama_tiket = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $harga      = (int)$_POST['harga'];
    $kuota      = (int)$_POST['kuota'];

    // Cek kapasitas venue
    $q_venue = mysqli_query($conn, "SELECT v.kapasitas FROM event e JOIN venue v ON e.id_venue = v.id_venue WHERE e.id_event = $id_event");
    $venue = mysqli_fetch_assoc($q_venue);
    $kapasitas_venue = $venue ? $venue['kapasitas'] : 0;

    // Cek total kuota tiket saat ini
    $q_kuota_saat_ini = mysqli_query($conn, "SELECT SUM(kuota) as total_kuota FROM tiket WHERE id_event = $id_event");
    $kuota_saat_ini = mysqli_fetch_assoc($q_kuota_saat_ini);
    $total_kuota_sekarang = $kuota_saat_ini['total_kuota'] ? $kuota_saat_ini['total_kuota'] : 0;

    if (($total_kuota_sekarang + $kuota) > $kapasitas_venue) {
        $sisa_kapasitas = $kapasitas_venue - $total_kuota_sekarang;
        echo "<script>alert('Gagal! Total kuota tiket melebihi kapasitas venue.\\nKapasitas Venue: $kapasitas_venue\\nTotal Kuota Saat Ini: $total_kuota_sekarang\\nMaksimal kuota yang bisa ditambahkan: $sisa_kapasitas');</script>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO tiket (id_event, nama_tiket, harga, kuota) VALUES ('$id_event', '$nama_tiket', '$harga', '$kuota')");
        if ($insert) { echo "<script>alert('Tiket berhasil ditambahkan!'); window.location='?p=admin_tiket';</script>"; }
        else { echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>"; }
    }
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
                    <select name="id_event" class="form-select" required><option value="" data-sisa="0">-- Pilih Event --</option>
                    <?php while ($e = mysqli_fetch_assoc($events)): 
                        $sisa = $e['kapasitas'] - $e['total_kuota'];
                    ?>
                        <option value="<?= $e['id_event'] ?>" data-sisa="<?= $sisa ?>">
                            <?= htmlspecialchars($e['nama_event']) ?> (Sisa Kapasitas: <?= $sisa ?>)
                        </option>
                    <?php endwhile; ?>
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

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const selectEvent = document.querySelector('select[name="id_event"]');
    const inputKuota = document.querySelector('input[name="kuota"]');
    const selectedOption = selectEvent.options[selectEvent.selectedIndex];
    
    if (selectedOption && selectedOption.value !== "") {
        const sisa = parseInt(selectedOption.getAttribute('data-sisa'));
        const inputVal = parseInt(inputKuota.value);
        if (inputVal > sisa) {
            e.preventDefault();
            alert('Gagal! Kuota tiket melebihi sisa kapasitas venue.\nMaksimal kuota yang bisa ditambahkan: ' + sisa);
        }
    }
});
</script>
