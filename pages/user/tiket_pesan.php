<?php
// Validasi login & role
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    $_SESSION['alert'] = [
        'type' => 'warning',
        'title' => 'Akses Ditolak',
        'text' => 'Hanya user yang dapat memesan tiket!'
    ];
    header("Location: ?p=login");
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
    "SELECT t.*, e.nama_event, e.tanggal, e.gambar, v.nama_venue, v.alamat 
     FROM tiket t 
     JOIN event e ON t.id_event = e.id_event 
     JOIN venue v ON e.id_venue = v.id_venue 
     WHERE t.id_tiket = $id_tiket");
$tiket = mysqli_fetch_assoc($q_tiket);

if (!$tiket) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Tidak Ditemukan',
        'text' => 'Tiket tidak ditemukan!'
    ];
    header("Location: ?p=home");
    exit;
}

// Cek jika event sudah lewat
if (strtotime($tiket['tanggal']) < strtotime('today')) {
    $_SESSION['alert'] = [
        'type' => 'info',
        'title' => 'Event Berakhir',
        'text' => 'Event ini telah berlalu dan tiket tidak dapat dipesan lagi!'
    ];
    header("Location: ?p=event_detail&id=".$tiket['id_event']);
    exit;
}

// Cek kuota sisa (langsung ambil dari kolom kuota karena sekarang logic-nya seperti voucher)
$sisa_kuota = $tiket['kuota'];

if ($sisa_kuota <= 0) {
    $_SESSION['alert'] = [
        'type' => 'warning',
        'title' => 'Tiket Habis',
        'text' => 'Maaf, tiket ini sudah habis!'
    ];
    header("Location: ?p=event_detail&id=".$tiket['id_event']);
    exit;
}

// Logic: Limit 5 tiket per event per user
$id_event = $tiket['id_event'];
$q_cek_limit = mysqli_query($conn, "
    SELECT SUM(od.qty) as total_beli 
    FROM order_detail od 
    JOIN orders o ON od.id_order = o.id_order 
    JOIN tiket t ON od.id_tiket = t.id_tiket
    WHERE o.id_user = $id_user 
    AND t.id_event = $id_event 
    AND o.status != 'cancel'
");
$row_limit = mysqli_fetch_assoc($q_cek_limit);
$total_beli = (int)($row_limit['total_beli'] ?? 0);
$max_beli_user = 5;
$sisa_jatah_user = $max_beli_user - $total_beli;

$is_limit_reached = $sisa_jatah_user <= 0;
$max_input = max(0, min($sisa_kuota, $sisa_jatah_user));

// Handle Form Submit
$error = '';
if (isset($_POST['pesan'])) {
    $qty = (int)$_POST['qty'];
    $kode_voucher = mysqli_real_escape_string($conn, trim($_POST['kode_voucher'] ?? ''));
    
    if ($qty <= 0) {
        $error = "Jumlah tiket minimal 1!";
    } elseif ($qty > $sisa_kuota) {
        $error = "Sisa kuota hanya $sisa_kuota tiket.";
    } elseif ($total_beli + $qty > 5) {
        $error = "Batas maksimal pembelian tiket untuk event ini adalah 5 tiket. Anda sudah membeli $total_beli tiket.";
    } else {
        $subtotal = $qty * $tiket['harga'];
        $total = $subtotal;
        $id_voucher = 'NULL';
        
        // Cek voucher jika diisi
        if (!empty($kode_voucher)) {
            $q_v = mysqli_query($conn, "SELECT * FROM voucher WHERE kode_voucher='$kode_voucher' AND status='aktif'");
            if (mysqli_num_rows($q_v) > 0) {
                $v = mysqli_fetch_assoc($q_v);
                
                // Cek apakah user sudah menggunakan voucher ini (1x per user)
                $q_used = mysqli_query($conn, "SELECT id_order FROM orders WHERE id_user = $id_user AND id_voucher = {$v['id_voucher']} AND status != 'cancel'");
                
                if (mysqli_num_rows($q_used) > 0) {
                    $error = "Anda sudah pernah menggunakan kode voucher ini.";
                } elseif ($v['kuota'] > 0 || $v['kuota'] == 0) {
                    // Voucher tidak bisa dipakai jika potongan lebih besar atau sama dengan harga tiket
                    if ($v['potongan'] >= $subtotal) {
                        $error = "Nominal voucher tidak boleh melebihi atau sama dengan total harga tiket.";
                    } else {
                        $total = $subtotal - $v['potongan'];
                        if ($total < 0) $total = 0;
                        $id_voucher = $v['id_voucher'];
                        
                        // Kurangi kuota HANYA jika tidak unlimited (kuota > 0)
                        if ($v['kuota'] > 0) {
                            $sisa_kuota_voucher = $v['kuota'] - 1;
                            mysqli_query($conn, "UPDATE voucher SET kuota = $sisa_kuota_voucher WHERE id_voucher = {$v['id_voucher']}");
                            
                            // Jika kuota habis, nonaktifkan
                            if ($sisa_kuota_voucher <= 0) {
                                mysqli_query($conn, "UPDATE voucher SET status = 'nonaktif' WHERE id_voucher = {$v['id_voucher']}");
                            }
                        }
                    }
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
                
                // Kurangi kuota tiket secara langsung (seperti logika voucher)
                mysqli_query($conn, "UPDATE tiket SET kuota = kuota - $qty WHERE id_tiket = $id_tiket");
                
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'title' => 'Pesanan Berhasil',
                    'text' => 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.'
                ];
                header("Location: ?p=riwayat");
                exit;
            } else {
                $error = "Terjadi kesalahan sistem saat membuat pesanan.";
            }
        }

        if (!empty($error)) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'title' => 'Pemesanan Gagal',
                'text' => $error
            ];
            header("Location: ?p=tiket_pesan&id=" . $id_tiket);
            exit;
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


            <div class="row g-4">
                <!-- Info Event & Tiket -->
                <div class="col-md-5">
                    <div class="card h-100" style="border:none; border-radius:var(--r-lg); box-shadow:0 4px 20px rgba(0,0,0,0.05); overflow:hidden;">
                        <?php if($tiket['gambar']): ?>
                            <div class="img-hd-container" style="position:relative; width:100%; padding-top:56.25%; overflow:hidden;">
                                <img src="uploads/<?= $tiket['gambar'] ?>" alt="<?= htmlspecialchars($tiket['nama_event']) ?>" style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; object-position:center; filter: contrast(1.05) brightness(1.02); transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);">
                                <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(to top, rgba(0,0,0,0.9), transparent); padding:2.5rem 1.2rem 1.2rem; backdrop-filter: blur(2px);">
                                    <h5 class="fw-bold mb-1 lh-sm text-white" style="text-shadow:0 2px 10px rgba(0,0,0,0.5); font-size: 1.25rem;"><?= htmlspecialchars($tiket['nama_event']) ?></h5>
                                    <div style="font-size:0.9rem; color:rgba(255,255,255,0.9); text-shadow:0 1px 4px rgba(0,0,0,0.5);">
                                        <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($tiket['tanggal'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="background:var(--g-primary); padding:1.5rem; color:white; position:relative;">
                                <div style="position:absolute; top:-20px; right:-20px; width:100px; height:100px; background:rgba(255,255,255,0.1); border-radius:50%;"></div>
                                <h5 class="fw-bold mb-1 lh-sm"><?= htmlspecialchars($tiket['nama_event']) ?></h5>
                                <div style="font-size:0.85rem; opacity:0.9;">
                                    <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($tiket['tanggal'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($is_limit_reached): ?>
                            <div class="p-3 bg-warning bg-opacity-10 border-start border-warning border-4">
                                <div class="d-flex align-items-center text-warning">
                                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                    <span class="fw-bold small">BATAS PEMBELIAN TERCAPAI</span>
                                </div>
                                <p class="mb-0 mt-1 text-muted" style="font-size:0.75rem;">Anda sudah membeli maksimal 5 tiket untuk event ini (kumulatif semua kategori).</p>
                            </div>
                        <?php endif; ?>

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
                                    <div class="input-group" style="max-width: 200px;">
                                        <button class="btn btn-outline-secondary" type="button" id="btn-min" style="border-radius: var(--r-md) 0 0 var(--r-md);" <?= $is_limit_reached ? 'disabled' : '' ?>><i class="bi bi-dash"></i></button>
                                        <input type="number" class="form-control text-center fw-bold" id="qty" name="qty" value="<?= $is_limit_reached ? 0 : 1 ?>" min="<?= $is_limit_reached ? 0 : 1 ?>" max="<?= $max_input ?>" style="background:#fff; color:#212529;" <?= $is_limit_reached ? 'disabled' : '' ?>>
                                        <button class="btn btn-outline-secondary" type="button" id="btn-plus" style="border-radius: 0 var(--r-md) var(--r-md) 0;" <?= $is_limit_reached ? 'disabled' : '' ?>><i class="bi bi-plus"></i></button>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Maksimal pesanan Anda: <?= $max_input ?> tiket 
                                        <?php if($total_beli > 0): ?>
                                            (Sudah beli: <?= $total_beli ?>)
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="kode_voucher" class="form-label fw-bold">Punya Kode Voucher?</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: var(--r-md) 0 0 var(--r-md);"><i class="bi bi-ticket-detailed"></i></span>
                                        <input type="text" class="form-control border-start-0 border-end-0 ps-0" id="kode_voucher" name="kode_voucher" placeholder="Masukkan kode voucher" style="box-shadow:none;" <?= $is_limit_reached ? 'disabled' : '' ?>>
                                        <button class="btn btn-outline-primary fw-bold" type="button" id="btn-cek-voucher" style="border-radius: 0 var(--r-md) var(--r-md) 0; border-width: 1.5px;" <?= $is_limit_reached ? 'disabled' : '' ?>>Cek Voucher</button>
                                    </div>
                                    <div id="voucher-status" class="form-text mt-2">Gunakan voucher untuk mendapatkan diskon!</div>
                                </div>

                                <div class="p-3 mb-4 rounded" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal (<span id="summary-qty">1</span> tiket)</span>
                                        <span class="fw-semibold text-dark" id="summary-subtotal">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 d-none" id="row-potongan">
                                        <span class="text-success">Diskon Voucher</span>
                                        <span class="fw-bold text-success" id="summary-potongan">- Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between pt-2 mt-2 border-top" style="border-color:#e2e8f0!important;">
                                        <span class="fw-bold fs-5">Total Bayar</span>
                                        <span class="fw-bold fs-5 text-primary" id="summary-total">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></span>
                                    </div>
                                </div>

                                <button type="submit" name="pesan" class="btn <?= $is_limit_reached ? 'btn-secondary' : 'btn-primary' ?> w-100 py-3 fw-bold" style="border-radius:50px; font-size:1.1rem; box-shadow:<?= $is_limit_reached ? 'none' : '0 8px 15px rgba(99,102,241,0.3)' ?>;" <?= $is_limit_reached ? 'disabled' : '' ?>>
                                    <i class="bi bi-check2-circle me-2"></i><?= $is_limit_reached ? 'Batas Pembelian Tercapai' : 'Konfirmasi Pesanan' ?>
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
    
    const maxQty = <?= $max_input ?>;

    const btnCekVoucher = document.getElementById('btn-cek-voucher');
    const voucherInput = document.getElementById('kode_voucher');
    const rowPotongan = document.getElementById('row-potongan');
    const summaryPotongan = document.getElementById('summary-potongan');
    const voucherStatus = document.getElementById('voucher-status');
    
    let currentPotongan = 0;

    function formatRupiah(number) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
    }

    function updateSummary() {
        let qty = parseInt(qtyInput.value);
        if(isNaN(qty) || qty < 1) { qty = 1; qtyInput.value = 1; }
        if(qty > maxQty) { qty = maxQty; qtyInput.value = maxQty; }
        
        let subtotal = qty * hargaPerTiket;
        let total = subtotal - currentPotongan;
        if(total < 0) total = 0;
        
        summaryQty.innerText = qty;
        summarySubtotal.innerText = formatRupiah(subtotal);
        summaryTotal.innerText = formatRupiah(total);
    }

    btnCekVoucher.addEventListener('click', function() {
        const kode = voucherInput.value.trim();
        const subtotal = parseInt(qtyInput.value) * hargaPerTiket;

        if(!kode) {
            Swal.fire({ icon: 'warning', title: 'Oops...', text: 'Masukkan kode voucher dulu!' });
            return;
        }

        btnCekVoucher.disabled = true;
        btnCekVoucher.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch(`pages/user/ajax_cek_voucher.php?kode=${kode}&subtotal=${subtotal}`)
            .then(res => res.json())
            .then(data => {
                btnCekVoucher.disabled = false;
                btnCekVoucher.innerText = 'Cek Voucher';

                if(data.status === 'success') {
                    Swal.fire({
                        title: 'Voucher Ditemukan!',
                        text: `${data.message} Anda akan mendapatkan potongan sebesar ${formatRupiah(data.potongan)}. Gunakan voucher ini?`,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonColor: '#6366f1',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Gunakan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            currentPotongan = data.potongan;
                            rowPotongan.classList.remove('d-none');
                            summaryPotongan.innerText = '- ' + formatRupiah(data.potongan);
                            voucherStatus.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Voucher Terpasang: ${kode}</span>`;
                            updateSummary();
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Terpasang!',
                                text: 'Potongan harga telah diterapkan.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else {
                            // Reset jika batal
                            voucherInput.value = '';
                            currentPotongan = 0;
                            rowPotongan.classList.add('d-none');
                            voucherStatus.innerHTML = `Gunakan voucher untuk mendapatkan diskon!`;
                            updateSummary();
                        }
                    });
                } else {
                    currentPotongan = 0;
                    rowPotongan.classList.add('d-none');
                    voucherStatus.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle-fill"></i> ${data.message}</span>`;
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                    updateSummary();
                }
            })
            .catch(err => {
                btnCekVoucher.disabled = false;
                btnCekVoucher.innerText = 'Cek Voucher';
                console.error(err);
            });
    });

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
