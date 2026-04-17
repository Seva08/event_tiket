<?php
// Redirect jika sudah login
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: index.php?p=dashboard_admin");
    } elseif ($_SESSION['role'] == 'petugas') {
        header("Location: index.php?p=dashboard_petugas");
    } else {
        header("Location: index.php?p=dashboard_user");
    }
    exit;
}

// Baca error dari session flash (di-set oleh index.php)
$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>

<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="bi bi-ticket-perforated login-icon"></i>
            <h3 class="mt-3">EventTiket</h3>
            <p class="text-muted">Sistem Pemesanan Tiket Online</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?p=login">
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                <input type="email" name="email" class="form-control form-control-lg" placeholder="Masukkan email" required>
            </div>
            <div class="mb-4">
                <label class="form-label"><i class="bi bi-lock"></i> Password</label>
                <input type="password" name="password" class="form-control form-control-lg" placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="login" class="btn btn-login btn-lg text-white w-100">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>

        <div class="mt-4 text-center">
            <small class="text-muted">
                <strong>Demo Account:</strong><br>
                Admin: admin@event.com / 123456<br>
                Petugas: petugas@event.com / 123456<br>
                User: user@event.com / 123456
            </small>
        </div>
    </div>
</div>

<style>
.login-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    padding: 40px;
    width: 100%;
    max-width: 400px;
}

.login-icon {
    font-size: 64px;
    color: #667eea;
}

.btn-login {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-login:hover {
    opacity: 0.9;
}
</style>
