<?php
// SweetAlert2 will handle errors now
?>
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5"
     style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);">
    <div style="position:fixed;top:-120px;left:-120px;width:400px;height:400px;background:rgba(255,255,255,.06);border-radius:50%;pointer-events:none;"></div>
    <div style="position:fixed;bottom:-100px;right:-80px;width:320px;height:320px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>

    <div class="container" style="max-width:440px; position:relative; z-index:1;">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:62px;height:62px;background:rgba(255,255,255,.18);backdrop-filter:blur(8px);border-radius:18px;border:1px solid rgba(255,255,255,.3);">
                <i class="bi bi-ticket-perforated-fill text-white fs-3"></i>
            </div>
            <h3 class="text-white fw-800 mb-0" style="font-weight:800;letter-spacing:-.5px;">YuiPass</h3>
            <p class="text-white mb-0" style="opacity:.75;font-size:.85rem;">Sistem Pemesanan Tiket Online</p>
        </div>

        <div class="card border-0 p-1" style="border-radius:24px;box-shadow:0 25px 60px rgba(0,0,0,.25);background:rgba(255,255,255,.97);">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1" style="color:var(--txt);">Buat Akun Baru 🚀</h4>
                <p class="text-muted mb-4" style="font-size:.85rem;">Daftar untuk mulai memesan tiket event</p>


                <form method="POST" action="index.php?p=register">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius:var(--r-md) 0 0 var(--r-md);border-right:none;">
                                <i class="bi bi-person" style="color:var(--c-primary)"></i>
                            </span>
                            <input type="text" name="nama" class="form-control"
                                   style="border-left:none;border-radius:0 var(--r-md) var(--r-md) 0;"
                                   placeholder="Nama Lengkap" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius:var(--r-md) 0 0 var(--r-md);border-right:none;">
                                <i class="bi bi-envelope" style="color:var(--c-primary)"></i>
                            </span>
                            <input type="email" name="email" class="form-control"
                                   style="border-left:none;border-radius:0 var(--r-md) var(--r-md) 0;"
                                   placeholder="email@gmail.com" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius:var(--r-md) 0 0 var(--r-md);border-right:none;">
                                <i class="bi bi-lock" style="color:var(--c-primary)"></i>
                            </span>
                            <input type="password" name="password" class="form-control"
                                   style="border-left:none;border-radius:0 var(--r-md) var(--r-md) 0;"
                                   placeholder="Minimal 6 karakter" required minlength="6">
                        </div>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary w-100 btn-lg mb-3"
                            style="border-radius:50px;letter-spacing:.3px;">
                        <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                    </button>
                    
                    <div class="text-center">
                        <span class="text-muted" style="font-size:.85rem;">Sudah punya akun? </span>
                        <a href="?p=login" class="fw-bold" style="color:var(--c-primary);font-size:.85rem;text-decoration:none;">Masuk di sini</a>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center mt-3" style="color:rgba(255,255,255,.55);font-size:.78rem;">
            &copy; <?= date('Y') ?> YuiPass. All rights reserved.
        </p>
    </div>
</div>
