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
    "SELECT t.*, e.nama_event, e.tanggal, e.gambar, e.limit_tiket, v.nama_venue, v.alamat 
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

// Cek kuota sisa
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

// Logic: Limit tiket per event per user (Dinamis dari Database)
$id_event = $tiket['id_event'];
$max_beli_user = (int)$tiket['limit_tiket']; // <--- NILAI DINAMIS

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
    } elseif ($total_beli + $qty > $max_beli_user) {
        $error = "Batas maksimal pembelian tiket untuk event ini adalah $max_beli_user tiket. Anda sudah membeli $total_beli tiket.";
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
    <!-- Progress Stepper -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <div class="d-flex justify-content-between position-relative">
                <div class="position-absolute top-50 start-0 translate-middle-y w-100 bg-light" style="height: 2px; z-index: 0;"></div>
                <div class="position-absolute top-50 start-0 translate-middle-y bg-primary transition-all" id="progress-bar" style="height: 2px; z-index: 0; width: 33%;"></div>
                
                <div class="text-center position-relative z-1">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 shadow" style="width: 35px; height: 35px;">1</div>
                    <span class="small fw-bold text-dark">Pilih Tiket</span>
                </div>
                <div class="text-center position-relative z-1">
                    <div class="bg-white text-muted border rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 shadow-sm" style="width: 35px; height: 35px;">2</div>
                    <span class="small fw-bold text-muted">Pembayaran</span>
                </div>
                <div class="text-center position-relative z-1">
                    <div class="bg-white text-muted border rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 shadow-sm" style="width: 35px; height: 35px;">3</div>
                    <span class="small fw-bold text-muted">Selesai</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-11">
            <div class="row g-4">
                <!-- Left: Event Details -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-sticky" style="top: 2rem;">
                        <div class="ratio ratio-16x9 position-relative">
                            <img src="<?= $tiket['gambar'] ? 'uploads/'.$tiket['gambar'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&q=80' ?>" 
                                 class="object-fit-cover" alt="Event">
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-white text-primary rounded-pill shadow-sm px-3 py-2 small fw-bold">
                                    <i class="bi bi-calendar3 me-2"></i><?= date('d M Y', strtotime($tiket['tanggal'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h4 class="fw-bold text-dark mb-3"><?= htmlspecialchars($tiket['nama_event']) ?></h4>
                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-2">
                                    <i class="bi bi-geo-alt-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small text-dark"><?= htmlspecialchars($tiket['nama_venue']) ?></div>
                                    <div class="text-muted small lh-sm"><?= htmlspecialchars($tiket['alamat']) ?></div>
                                </div>
                            </div>

                            <div class="bg-light rounded-4 p-4 border border-dashed">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <small class="text-muted text-uppercase fw-bold ls-1 d-block mb-1" style="font-size: 0.6rem;">Kategori</small>
                                        <div class="h5 fw-bold text-primary mb-0"><?= htmlspecialchars($tiket['nama_tiket']) ?></div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted text-uppercase fw-bold ls-1 d-block mb-1" style="font-size: 0.6rem;">Harga</small>
                                        <div class="h5 fw-bold text-dark mb-0" id="display-harga" data-harga="<?= $tiket['harga'] ?>">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 pt-3 border-top mt-2">
                                    <i class="bi bi-info-circle text-muted"></i>
                                    <span class="small text-muted">Maksimal pembelian: <b><?= $max_beli_user ?> tiket</b></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Booking Form -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="bi bi-person-check me-2 text-primary"></i>Data Pemesanan
                            </h5>
                            
                            <form method="POST" id="form-pesan">
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Pilih Jumlah Tiket</label>
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="d-flex align-items-center bg-light rounded-pill p-1 border shadow-sm" style="width: 180px;">
                                            <button class="btn btn-white rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0" type="button" id="btn-min" style="width: 40px; height: 40px;" <?= $is_limit_reached ? 'disabled' : '' ?>><i class="bi bi-dash-lg"></i></button>
                                            <input type="number" class="form-control bg-transparent border-0 text-center fw-bold fs-5 shadow-none" id="qty" name="qty" value="<?= $is_limit_reached ? 0 : 1 ?>" readonly>
                                            <button class="btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0" type="button" id="btn-plus" style="width: 40px; height: 40px;" <?= $is_limit_reached ? 'disabled' : '' ?>><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                        <div class="text-muted small">
                                            <span class="fw-bold text-dark"><?= $sisa_kuota ?></span> tiket tersedia
                                        </div>
                                    </div>
                                    <?php if($is_limit_reached): ?>
                                        <div class="text-danger small mt-2 fw-bold"><i class="bi bi-exclamation-circle me-1"></i> Batas pembelian tercapai.</div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-5 pt-2">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Punya Kode Promo?</label>
                                    <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                                        <span class="input-group-text bg-white border-0 ps-3 text-primary"><i class="bi bi-tags-fill"></i></span>
                                        <input type="text" class="form-control border-0 shadow-none ps-0 py-3" id="kode_voucher" name="kode_voucher" placeholder="Masukkan kode di sini..." <?= $is_limit_reached ? 'disabled' : '' ?>>
                                        <button class="btn btn-primary fw-bold px-4" type="button" id="btn-cek-voucher" <?= $is_limit_reached ? 'disabled' : '' ?>>Gunakan</button>
                                    </div>
                                    <div id="voucher-status" class="form-text mt-2 ms-2 small text-muted">Gunakan voucher untuk potongan harga tambahan.</div>
                                </div>

                                <div class="p-4 mb-4 rounded-4 bg-light border border-dashed shadow-sm">
                                    <h6 class="fw-bold mb-3">Ringkasan Biaya</h6>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Subtotal (<span id="summary-qty">1</span>x)</span>
                                        <span class="fw-bold" id="summary-subtotal">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3 d-none text-success fw-bold" id="row-potongan">
                                        <span>Diskon Promo</span>
                                        <span id="summary-potongan">- Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between pt-3 border-top">
                                        <span class="fw-bold text-dark fs-5">Total Bayar</span>
                                        <span class="fw-bold text-primary fs-3" id="summary-total">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></span>
                                    </div>
                                </div>

                                <button type="submit" name="pesan" class="btn <?= $is_limit_reached ? 'btn-secondary opacity-50' : 'btn-primary' ?> w-100 py-3 rounded-pill fw-bold shadow-sm fs-5 transition-transform hover-scale" <?= $is_limit_reached ? 'disabled' : '' ?>>
                                    <i class="bi bi-shield-lock-fill me-2"></i>Konfirmasi & Bayar
                                </button>
                                
                                <p class="text-center text-muted small mt-4 mb-0">
                                    <i class="bi bi-lock-fill me-1"></i> Transaksi Anda aman dan terenkripsi.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.transition-all { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.ls-1 { letter-spacing: 1px; }
.hover-scale:hover { transform: scale(1.02); }
</style>

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
