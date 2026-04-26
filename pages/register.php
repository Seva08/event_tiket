<?php
// SweetAlert2 will handle errors now
?>
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5 bg-primary">
    <div class="container px-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5 col-xl-4">
                <!-- Brand Logo (White Circle on Primary BG) -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-4 mb-3 p-3 shadow-sm" style="width: 64px; height: 64px;">
                        <i class="bi bi-person-plus-fill text-primary fs-2"></i>
                    </div>
                    <h2 class="fw-bold mb-1 tracking-tight text-white">YuiPass</h2>
                    <p class="text-white opacity-75 small">Gabung & Temukan Event Favoritmu</p>
                </div>

                <!-- Register Card (White Card) -->
                <div class="card border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4">
                            <h4 class="fw-bold mb-1 text-dark">Buat Akun Baru 🚀</h4>
                            <p class="text-muted small mb-0">Daftar sekarang untuk mulai memesan tiket.</p>
                        </div>

                        <form method="POST" action="index.php?p=register">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-uppercase text-muted ls-1">Nama Lengkap</label>
                                <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                    <span class="input-group-text bg-light border-0 text-primary"><i class="bi bi-person"></i></span>
                                    <input type="text" name="nama" class="form-control bg-light border-0 py-2 ps-0" placeholder="Nama Lengkap Anda" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-uppercase text-muted ls-1">Alamat Email</label>
                                <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                    <span class="input-group-text bg-light border-0 text-primary"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control bg-light border-0 py-2 ps-0" placeholder="nama@email.com" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-uppercase text-muted ls-1">Kata Sandi</label>
                                <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                                    <span class="input-group-text bg-light border-0 text-primary"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control bg-light border-0 py-2 ps-0" placeholder="Minimal 6 karakter" required minlength="6">
                                </div>
                            </div>
                            <button type="submit" name="register" class="btn btn-primary w-100 py-2 fw-bold rounded-pill mb-4 shadow-sm">
                                <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                            </button>
                            
                            <div class="text-center">
                                <span class="text-muted small">Sudah punya akun? </span>
                                <a href="?p=login" class="fw-bold text-primary small text-decoration-none border-bottom">Masuk ke Akun</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-5 text-white opacity-50 small">
                    &copy; <?= date('Y') ?> YuiPass. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</div>
