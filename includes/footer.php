<!-- ════════════ FOOTER ════════════ -->
<footer class="mt-5 py-5 border-top bg-white d-print-none">
    <div class="container">
        <div class="row g-4 text-start">
            <div class="col-lg-4">
                <a class="navbar-brand mb-3 d-inline-flex align-items-center gap-2 fw-bold text-primary fs-5 text-decoration-none" href="?p=home">
                    <span class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center p-2">
                        <i class="bi bi-ticket-perforated-fill fs-5"></i>
                    </span>
                    YuiPass
                </a>
                <p class="text-muted small pe-lg-5">YuiPass adalah platform manajemen event dan ticketing terlengkap yang memudahkan penyelenggara dan pengunjung untuk bertransaksi dengan aman dan nyaman.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-muted fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="bi bi-youtube"></i></a>
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

<!-- Audio for Scanning -->
<audio id="sound-success" src="https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3" preload="auto"></audio>
<audio id="sound-error" src="https://assets.mixkit.co/active_storage/sfx/2571/2571-preview.mp3" preload="auto"></audio>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Global Alert Handler
<?php if (isset($_SESSION['alert'])): ?>
    Swal.fire({
        icon: '<?= $_SESSION['alert']['type'] ?>',
        title: '<?= $_SESSION['alert']['title'] ?>',
        text: '<?= $_SESSION['alert']['text'] ?>',
        timer: <?= $_SESSION['alert']['type'] == 'success' ? 2000 : 4000 ?>,
        showConfirmButton: <?= $_SESSION['alert']['type'] == 'success' ? 'false' : 'true' ?>,
        timerProgressBar: true
    });
    
    // Play sound based on alert type
    document.addEventListener('DOMContentLoaded', function() {
        const type = '<?= $_SESSION['alert']['type'] ?>';
        if (type === 'success') {
            document.getElementById('sound-success').play().catch(e => console.log('Audio play blocked'));
        } else if (type === 'error' || type === 'warning') {
            document.getElementById('sound-error').play().catch(e => console.log('Audio play blocked'));
        }
    });
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>

// Global: SweetAlert2 confirmation for delete buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-hapus').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            Swal.fire({
                title: 'Yakin hapus data ini?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'rounded-pill px-4',
                    cancelButton: 'rounded-pill px-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });

    // Initialize Bootstrap tooltips if any
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });
});
</script>
</body>
</html>
