<?php
$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5"
     style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);">
    <!-- Decorative blobs -->
    <div style="position:fixed;top:-120px;left:-120px;width:400px;height:400px;background:rgba(255,255,255,.06);border-radius:50%;pointer-events:none;"></div>
    <div style="position:fixed;bottom:-100px;right:-80px;width:320px;height:320px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>

    <div class="container" style="max-width:440px; position:relative; z-index:1;">
        <!-- Logo -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:62px;height:62px;background:rgba(255,255,255,.18);backdrop-filter:blur(8px);border-radius:18px;border:1px solid rgba(255,255,255,.3);">
                <i class="bi bi-ticket-perforated-fill text-white fs-3"></i>
            </div>
            <h3 class="text-white fw-800 mb-0" style="font-weight:800;letter-spacing:-.5px;">EventTiket</h3>
            <p class="text-white mb-0" style="opacity:.75;font-size:.85rem;">Sistem Pemesanan Tiket Online</p>
        </div>

        <!-- Card -->
        <div class="card border-0 p-1" style="border-radius:24px;box-shadow:0 25px 60px rgba(0,0,0,.25);background:rgba(255,255,255,.97);">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1" style="color:var(--txt);">Selamat Datang 👋</h4>
                <p class="text-muted mb-4" style="font-size:.85rem;">Masuk ke akun Anda untuk melanjutkan</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3">
                        <i class="bi bi-exclamation-circle-fill fs-5"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?p=login">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius:var(--r-md) 0 0 var(--r-md);border-right:none;">
                                <i class="bi bi-envelope" style="color:var(--c-primary)"></i>
                            </span>
                            <input type="email" name="email" class="form-control"
                                   style="border-left:none;border-radius:0 var(--r-md) var(--r-md) 0;"
                                   placeholder="email@example.com" required>
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
                                   placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 btn-lg"
                            style="border-radius:50px;letter-spacing:.3px;">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                    </button>
                </form>

                <!-- Demo accounts -->
                <div class="mt-4 p-3" style="background:#f8fafc;border-radius:var(--r-md);border:1px solid var(--border);">
                    <p class="fw-600 mb-2 text-center" style="font-size:.75rem;color:var(--txt-muted);letter-spacing:.5px;text-transform:uppercase;font-weight:600;">Demo Account</p>
                    <div class="row g-1 text-center" style="font-size:.78rem;">
                        <div class="col-4">
                            <div style="background:#fff;border-radius:8px;padding:.4rem .2rem;border:1px solid var(--border);">
                                <i class="bi bi-shield-fill text-primary d-block mb-1"></i>
                                <div class="fw-600" style="color:var(--txt);font-size:.72rem;font-weight:600;">Admin</div>
                                <div style="color:var(--txt-muted);font-size:.68rem;">admin@event.com</div>
                                <div style="color:var(--txt-muted);font-size:.68rem;">123456</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="background:#fff;border-radius:8px;padding:.4rem .2rem;border:1px solid var(--border);">
                                <i class="bi bi-person-badge-fill text-success d-block mb-1"></i>
                                <div class="fw-600" style="color:var(--txt);font-size:.72rem;font-weight:600;">Petugas</div>
                                <div style="color:var(--txt-muted);font-size:.68rem;">petugas@event.com</div>
                                <div style="color:var(--txt-muted);font-size:.68rem;">123456</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="background:#fff;border-radius:8px;padding:.4rem .2rem;border:1px solid var(--border);">
                                <i class="bi bi-person-fill text-info d-block mb-1"></i>
                                <div class="fw-600" style="color:var(--txt);font-size:.72rem;font-weight:600;">User</div>
                                <div style="color:var(--txt-muted);font-size:.68rem;">user@event.com</div>
                                <div style="color:var(--txt-muted);font-size:.68rem;">123456</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center mt-3" style="color:rgba(255,255,255,.55);font-size:.78rem;">
            &copy; <?= date('Y') ?> EventTiket. All rights reserved.
        </p>
    </div>
</div>
