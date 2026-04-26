<?php
// Validasi login
if (!isset($_SESSION['id_user'])) {
    header("Location: ?p=login");
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data user terbaru
$q_user = mysqli_query($conn, "SELECT * FROM users WHERE id_user = $id_user");
$user = mysqli_fetch_assoc($q_user);

// Logika Update Profile
if (isset($_POST['update_profile'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $update = mysqli_query($conn, "UPDATE users SET nama = '$nama', email = '$email' WHERE id_user = $id_user");
    
    if ($update) {
        $_SESSION['nama'] = $nama;
        $_SESSION['alert'] = ['type' => 'success', 'title' => 'Berhasil!', 'text' => 'Profil Anda telah diperbarui.'];
        header("Location: ?p=profile");
        exit;
    }
}

// Logika Ganti Password
if (isset($_POST['update_password'])) {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    // Verifikasi password lama
    $is_valid = false;
    if ($user['password_hash']) {
        if (password_verify($old, $user['password_hash'])) $is_valid = true;
    } else {
        if (md5($old) == $user['password']) $is_valid = true;
    }
    
    if (!$is_valid) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Password lama salah.'];
    } elseif ($new !== $confirm) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal!', 'text' => 'Konfirmasi password baru tidak cocok.'];
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        mysqli_query($conn, "UPDATE users SET password_hash = '$hash', password = '' WHERE id_user = $id_user");
        $_SESSION['alert'] = ['type' => 'success', 'title' => 'Berhasil!', 'text' => 'Password Anda telah diubah.'];
    }
    header("Location: ?p=profile");
    exit;
}

// Stats
$total_tiket = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $id_user AND status = 'paid'"))['c'];
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE id_user = $id_user AND status = 'pending'"))['c'];
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Profile Header Card -->
            <div class="card border-0 shadow-sm overflow-hidden mb-4 rounded-4">
                <div class="bg-primary ratio ratio-21x9 min-vh-25"></div>
                <div class="card-body p-4 pt-0">
                    <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end gap-4 mt-n5">
                        <div class="position-relative">
                            <div class="bg-white p-1 rounded-4 shadow position-relative p-4 ratio ratio-1x1 w-25 min-vw-15">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama']) ?>&background=random&size=120&bold=true" 
                                     class="w-100 h-100 rounded-4 object-fit-cover">
                            </div>
                            <span class="position-absolute bottom-0 end-0 bg-success border border-white border-3 rounded-circle p-2" title="Online"></span>
                        </div>
                        <div class="text-center text-md-start flex-grow-1">
                            <h3 class="fw-bold mb-1"><?= htmlspecialchars($user['nama']) ?></h3>
                            <p class="text-muted mb-0"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user['email']) ?></p>
                            <span class="badge bg-primary bg-opacity-10 text-primary mt-2 px-3 py-2 rounded-pill fw-bold">
                                <i class="bi bi-shield-check me-1"></i><?= strtoupper($user['role']) ?>
                            </span>
                        </div>
                        <div class="d-flex gap-3 mb-2">
                            <div class="text-center px-3 border-end">
                                <div class="fw-bold fs-4"><?= $total_tiket ?></div>
                                <small class="text-muted text-uppercase fw-bold small">Tiket Lunas</small>
                            </div>
                            <div class="text-center px-3">
                                <div class="fw-bold fs-4"><?= $total_pending ?></div>
                                <small class="text-muted text-uppercase fw-bold small">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Edit Profile -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="mb-0"><i class="bi bi-person-gear me-2 text-primary"></i>Pengaturan Profil</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Alamat Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary w-100 py-2 shadow-sm">
                                    <i class="bi bi-check2-circle me-2"></i>Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h5 class="mb-0"><i class="bi bi-key me-2 text-warning"></i>Ganti Password</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Password Lama</label>
                                    <input type="password" name="old_password" class="form-control" placeholder="••••••••" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" name="new_password" class="form-control" placeholder="Min. 6 karakter" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
                                </div>
                                <button type="submit" name="update_password" class="btn btn-outline-warning w-100 py-2">
                                    <i class="bi bi-shield-lock me-2"></i>Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>        
        </div>
    </div>
</div>
