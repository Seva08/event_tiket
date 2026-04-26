<?php
// SweetAlert2 will handle errors now
?>
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5 bg-white">
    <div class="container px-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5 col-xl-4">
                <!-- Brand Logo (Dark on White BG) -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-4 mb-3 p-3 border border-primary border-opacity-10" style="width: 64px; height: 64px;">
                        <i class="bi bi-ticket-perforated-fill text-primary fs-2"></i>
                    </div>
                    <h2 class="fw-bold mb-1 tracking-tight text-dark">YuiPass</h2>
                    <p class="text-muted small">Sistem Pemesanan Tiket Online</p>
                </div>

                <!-- Login Card (Primary BG) -->
                <div class="card border-0 rounded-4 shadow-lg bg-primary text-white overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4">
                            <h4 class="fw-bold mb-1">Selamat Datang 👋</h4>
                            <p class="text-white opacity-75 small mb-0">Silakan masuk ke akun Anda.</p>
                        </div>

                        <form method="POST" action="index.php?p=login">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-uppercase opacity-75 ls-1">Alamat Email</label>
                                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-white border-0 text-primary"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control border-0 py-2 ps-0" placeholder="nama@email.com" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-uppercase opacity-75 ls-1">Kata Sandi</label>
                                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-white border-0 text-primary"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control border-0 py-2 ps-0" placeholder="••••••••" required>
                                </div>
                            </div>
                            <button type="submit" name="login" class="btn btn-white w-100 py-2 fw-bold rounded-pill mb-4 shadow-sm text-primary" style="background: white;">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk Sekarang
                            </button>
                            <div class="text-center">
                                <span class="opacity-75 small">Belum punya akun? </span>
                                <a href="?p=register" class="fw-bold text-white small text-decoration-none border-bottom">Daftar Akun Baru</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-5 text-muted small opacity-50">
                    &copy; <?= date('Y') ?> YuiPass. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</div>
