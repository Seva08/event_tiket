<!-- ════════════ FOOTER ════════════ -->
<footer class="mt-5 py-5 border-top bg-white d-print-none">
    <div class="container">
        <div class="row g-4 text-start">
            <div class="col-lg-4">
                <a class="navbar-brand mb-3 d-inline-flex align-items-center gap-2" href="?p=home" style="color:var(--c-primary); font-weight:800; font-size:1.4rem;">
                    <span class="brand-icon" style="background:var(--g-primary); width:35px; height:35px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff;">
                        <i class="bi bi-ticket-perforated-fill fs-5"></i>
                    </span>
                    YuiPass
                </a>
                <p class="text-muted small pe-lg-5">YuiPass adalah platform manajemen event dan ticketing terlengkap yang memudahkan penyelenggara dan pengunjung untuk bertransaksi dengan aman dan nyaman.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-muted fs-5 hover-primary"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-muted fs-5 hover-primary"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-muted fs-5 hover-primary"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-muted fs-5 hover-primary"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="fw-bold mb-3">Layanan</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="?p=home" class="text-muted text-decoration-none">Beli Tiket</a></li>
                    <li class="mb-2"><a href="?p=riwayat" class="text-muted text-decoration-none">Cek Pesanan</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Pusat Bantuan</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Merchant Portal</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="fw-bold mb-3">Tentang</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Tentang Kami</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Kebijakan Privasi</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Syarat & Ketentuan</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Karir</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="fw-bold mb-3">Hubungi Kami</h6>
                <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i> Jl. Event Sejahtera No. 8, Jakarta Selatan</p>
                <p class="text-muted small mb-2"><i class="bi bi-envelope me-2 text-primary"></i> support@yuipass.id</p>
                <p class="text-muted small mb-4"><i class="bi bi-telephone me-2 text-primary"></i> (021) 1234-5678</p>
                <div class="d-flex gap-3">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/2560px-Visa_Inc._logo.svg.png" height="15" alt="Visa" class="opacity-50 grayscale">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png" height="15" alt="Mastercard" class="opacity-50 grayscale">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Logo_danareksa.png/800px-Logo_danareksa.png" height="15" alt="QRIS" class="opacity-50 grayscale">
                </div>
            </div>
        </div>
        <hr class="my-4 opacity-50">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <span class="text-muted small">&copy; <?= date('Y') ?> YuiPass. Built with ❤️ for your best experience.</span>
            <div class="d-flex gap-4">
                <a href="#" class="text-muted small text-decoration-none">Keamanan</a>
                <a href="#" class="text-muted small text-decoration-none">Cookies</a>
            </div>
        </div>
    </div>
</footer>

<style>
.grayscale { filter: grayscale(100%); }
.hover-primary:hover { color: var(--c-primary) !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
