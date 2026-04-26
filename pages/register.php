<?php
// SweetAlert2 will handle errors now
?>
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5 bg-primary">
    <div class="container col-12 col-md-8 col-lg-5 col-xl-4">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded-3 mb-3 p-3">
                <i class="bi bi-ticket-perforated-fill text-white fs-3"></i>
            </div>
            <h3 class="text-white fw-bold mb-0">YuiPass</h3>
            <p class="text-white opacity-75 small mb-0">Sistem Pemesanan Tiket Online</p>
        </div>

        <div class="card border-0 rounded-4 shadow-lg p-1">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1">Buat Akun Baru 🚀</h4>
                <p class="text-muted mb-4 small">Daftar untuk mulai memesan tiket event</p>

                <form method="POST" action="index.php?p=register">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person text-primary"></i></span>
                            <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope text-primary"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="email@gmail.com" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock text-primary"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                        </div>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary w-100 btn-lg rounded-pill mb-3">
                        <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                    </button>
                    
                    <div class="text-center">
                        <span class="text-muted small">Sudah punya akun? </span>
                        <a href="?p=login" class="fw-bold text-primary small text-decoration-none">Masuk di sini</a>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center mt-3 text-white opacity-50 small">
            &copy; <?= date('Y') ?> YuiPass. All rights reserved.
        </p>
    </div>
</div>
