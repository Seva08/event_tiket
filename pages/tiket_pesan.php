<?php
// Validasi login
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='?p=login';</script>";
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: ?p=home");
    exit;
}

$id_tiket = (int)$_GET['id'];
$id_user = $_SESSION['id_user'];

// Ambil detail tiket & event
$q_tiket = mysqli_query($conn, 
    "SELECT t.*, e.nama_event, e.tanggal, v.nama_venue, v.alamat 
     FROM tiket t 
     JOIN event e ON t.id_event = e.id_event 
     JOIN venue v ON e.id_venue = v.id_venue 
     WHERE t.id_tiket = $id_tiket");
$tiket = mysqli_fetch_assoc($q_tiket);

if (!$tiket) {
    echo "<script>alert('Tiket tidak ditemukan!'); window.location='?p=home';</script>";
    exit;
}

// Cek kuota sisa
$q_terjual = mysqli_query($conn, "SELECT SUM(od.qty) as terjual FROM order_detail od JOIN orders o ON od.id_order = o.id_order WHERE od.id_tiket = $id_tiket AND o.status='paid'");
$d_terjual = mysqli_fetch_assoc($q_terjual);
$terjual = $d_terjual['terjual'] ? $d_terjual['terjual'] : 0;
$sisa_kuota = $tiket['kuota'] - $terjual;

if ($sisa_kuota <= 0) {
    echo "<script>alert('Maaf, tiket ini sudah habis!'); window.location='?p=event_detail&id=".$tiket['id_event']."';</script>";
    exit;
}

// Handle Form Submit
$error = '';
if (isset($_POST['pesan'])) {
    $qty = (int)$_POST['qty'];
    $kode_voucher = mysqli_real_escape_string($conn, trim($_POST['kode_voucher'] ?? ''));
    
    if ($qty <= 0) {
        $error = "Jumlah tiket minimal 1!";
    } elseif ($qty > $sisa_kuota) {
        $error = "Sisa kuota hanya $sisa_kuota tiket.";
    } else {
        $subtotal = $qty * $tiket['harga'];
        $total = $subtotal;
        $id_voucher = 'NULL';
        
        // Cek voucher jika diisi
        if (!empty($kode_voucher)) {
            $q_v = mysqli_query($conn, "SELECT * FROM voucher WHERE kode_voucher='$kode_voucher' AND status='aktif'");
            if (mysqli_num_rows($q_v) > 0) {
                $v = mysqli_fetch_assoc($q_v);
                if ($v['kuota'] > 0) {
                    $total = $subtotal - $v['potongan'];
                    if ($total < 0) $total = 0;
                    $id_voucher = $v['id_voucher'];
                    
                    // Kurangi kuota voucher
                    mysqli_query($conn, "UPDATE voucher SET kuota = kuota - 1 WHERE id_voucher = {$v['id_voucher']}");
                } else {
                    $error = "Kuota voucher sudah habis.";
                }
            } else {
                $error = "Kode voucher tidak valid atau tidak aktif.";
            }
        }
        
        if (empty($error)) {
            // Insert Order
            $q_order = "INSERT INTO orders (id_user, total, status, id_voucher) VALUES ($id_user, $total, 'pending', $id_voucher)";
            if (mysqli_query($conn, $q_order)) {
                $id_order = mysqli_insert_id($conn);
                
                // Insert Order Detail
                $q_detail = "INSERT INTO order_detail (id_order, id_tiket, qty, subtotal) VALUES ($id_order, $id_tiket, $qty, $subtotal)";
                mysqli_query($conn, $q_detail);
                
                echo "<script>alert('Pesanan berhasil dibuat! Silakan lakukan pembayaran.'); window.location='?p=riwayat';</script>";
                exit;
            } else {
                $error = "Terjadi kesalahan sistem saat membuat pesanan.";
            }
        }
    }
}
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?p=home">Home</a></li>
            <li class="breadcrumb-item"><a href="?p=event_detail&id=<?= $tiket['id_event'] ?>"><?= htmlspecialchars($tiket['nama_event']) ?></a></li>
            <li class="breadcrumb-item active">Pesan Tiket</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <div style="width:50px;height:50px;background:var(--g-primary);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-right:1rem;color:white;">
                    <i class="bi bi-cart-plus fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold">Konfirmasi Pemesanan</h3>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">Selesaikan pesanan untuk mendapatkan tiketmu</p>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Info Event & Tiket -->
                <div class="col-md-5">
                    <div class="card h-100" style="border:none; border-radius:var(--r-lg); box-shadow:0 4px 20px rgba(0,0,0,0.05); overflow:hidden;">
                        <div style="background:var(--g-primary); padding:1.5rem; color:white; position:relative;">
                            <div style="position:absolute; top:-20px; right:-20px; width:100px; height:100px; background:rgba(255,255,255,0.1); border-radius:50%;"></div>
                            <h5 class="fw-bold mb-1 lh-sm"><?= htmlspecialchars($tiket['nama_event']) ?></h5>
                            <div style="font-size:0.85rem; opacity:0.9;">
                                <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($tiket['tanggal'])) ?>
                            </div>
                        </div>
                        <div class="card-body p-4 bg-light">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem; letter-spacing:0.5px;">Venue</small>
                                <div class="fw-semibold text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($tiket['nama_venue']) ?></div>
                                <div class="text-muted" style="font-size:0.8rem;"><?= htmlspecialchars($tiket['alamat']) ?></div>
                            </div>
                            <hr class="my-3 border-secondary" style="opacity:0.1;">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem; letter-spacing:0.5px;">Tipe Tiket</small>
                                <div class="fw-bold text-primary fs-5"><?= htmlspecialchars($tiket['nama_tiket']) ?></div>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem; letter-spacing:0.5px;">Harga per Tiket</small>
                                <div class="fw-bold text-dark fs-4" id="display-harga" data-harga="<?= $tiket['harga'] ?>">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Pemesanan -->
                <div class="col-md-7">
                    <div class="card h-100" style="border:none; border-radius:var(--r-lg); box-shadow:0 4px 20px rgba(0,0,0,0.05);">
                        <div class="card-body p-4">
                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label for="qty" class="form-label fw-bold">Jumlah Tiket <span class="text-danger">*</span></label>
                                    <div class="input-group" style="width: 150px;">
                                        <button class="btn btn-outline-secondary" type="button" id="btn-min" style="border-radius: var(--r-md) 0 0 var(--r-md);"><i class="bi bi-dash"></i></button>
                                        <input type="number" class="form-control text-center fw-bold" id="qty" name="qty" value="1" min="1" max="<?= $sisa_kuota ?>" readonly style="background:#fff;">
                                        <button class="btn btn-outline-secondary" type="button" id="btn-plus" style="border-radius: 0 var(--r-md) var(--r-md) 0;"><i class="bi bi-plus"></i></button>
                                    </div>
                                    <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Maksimal pesanan: <?= $sisa_kuota ?> tiket (sisa kuota)</div>
                                </div>

                                <div class="mb-4">
                                    <label for="kode_voucher" class="form-label fw-bold">Punya Kode Voucher?</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: var(--r-md) 0 0 var(--r-md);"><i class="bi bi-ticket-detailed"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="kode_voucher" name="kode_voucher" placeholder="Masukkan kode voucher (opsional)" style="border-radius: 0 var(--r-md) var(--r-md) 0; box-shadow:none;">
                                    </div>
                                    <div class="form-text">Gunakan voucher untuk mendapatkan diskon!</div>
                                </div>

                                <div class="p-3 mb-4 rounded" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal (<span id="summary-qty">1</span> tiket)</span>
                                        <span class="fw-semibold text-dark" id="summary-subtotal">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between pt-2 mt-2 border-top" style="border-color:#e2e8f0!important;">
                                        <span class="fw-bold fs-5">Total Bayar</span>
                                        <span class="fw-bold fs-5 text-primary" id="summary-total">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></span>
                                    </div>
                                </div>

                                <button type="submit" name="pesan" class="btn btn-primary w-100 py-3 fw-bold" style="border-radius:50px; font-size:1.1rem; box-shadow:0 8px 15px rgba(99,102,241,0.3);">
                                    <i class="bi bi-check2-circle me-2"></i>Konfirmasi Pesanan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyInput = document.getElementById('qty');
    const btnMin = document.getElementById('btn-min');
    const btnPlus = document.getElementById('btn-plus');
    const hargaPerTiket = parseInt(document.getElementById('display-harga').dataset.harga);
    
    const summaryQty = document.getElementById('summary-qty');
    const summarySubtotal = document.getElementById('summary-subtotal');
    const summaryTotal = document.getElementById('summary-total');
    
    const maxQty = <?= $sisa_kuota ?>;

    function formatRupiah(number) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
    }

    function updateSummary() {
        let qty = parseInt(qtyInput.value);
        if(isNaN(qty) || qty < 1) { qty = 1; qtyInput.value = 1; }
        if(qty > maxQty) { qty = maxQty; qtyInput.value = maxQty; }
        
        let subtotal = qty * hargaPerTiket;
        
        summaryQty.innerText = qty;
        summarySubtotal.innerText = formatRupiah(subtotal);
        // Note: we don't calculate voucher discount dynamically via JS here for simplicity
        // as it requires AJAX to validate voucher. The user will see final total after submit.
        summaryTotal.innerText = formatRupiah(subtotal);
    }

    btnMin.addEventListener('click', () => {
        let currentVal = parseInt(qtyInput.value);
        if (currentVal > 1) {
            qtyInput.value = currentVal - 1;
            updateSummary();
        }
    });

    btnPlus.addEventListener('click', () => {
        let currentVal = parseInt(qtyInput.value);
        if (currentVal < maxQty) {
            qtyInput.value = currentVal + 1;
            updateSummary();
        }
    });

    qtyInput.addEventListener('change', updateSummary);
});
</script>
