<?php
// SweetAlert2 will handle errors now
?>
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5 bg-primary">
    <div class="container col-12 col-md-8 col-lg-5 col-xl-4">
        <!-- Logo -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded-3 mb-3 p-3">
                <i class="bi bi-ticket-perforated-fill text-white fs-3"></i>
            </div>
            <h3 class="text-white fw-bold mb-0">YuiPass</h3>
            <p class="text-white opacity-75 small mb-0">Sistem Pemesanan Tiket Online</p>
        </div>

        <!-- Card -->
        <div class="card border-0 rounded-4 shadow-lg p-1">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1">Selamat Datang 👋</h4>
                <p class="text-muted mb-4 small">Masuk ke akun Anda untuk melanjutkan</p>

                <form method="POST" action="index.php?p=login">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope text-primary"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock text-primary"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 btn-lg rounded-pill mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                    </button>
                    <div class="text-center">
                        <span class="text-muted small">Belum punya akun? </span>
                        <a href="?p=register" class="fw-bold text-primary small text-decoration-none">Daftar di sini</a>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center mt-3 text-white opacity-50 small">
            &copy; <?= date('Y') ?> YuiPass. All rights reserved.
        </p>
    </div>
</div>
