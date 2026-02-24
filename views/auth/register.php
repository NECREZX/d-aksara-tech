<?php
if (is_logged_in()) {
    redirect('index.php?page=' . ($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'customer_dashboard'));
}
require 'views/layouts/header.php';
?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-lg border-0 p-4" style="width: 100%; max-width: 500px; border-radius: 20px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold" style="color: var(--secondary-color);"><i class="fas fa-user-plus me-2"></i>Daftar Akun</h3>
            <p class="text-muted">Bergabung dengan platform kami</p>
        </div>
        
        <form action="index.php?page=do_register" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="name" name="name" required placeholder="Nama Anda">
                </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="nama@email.com">
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Minimal 6 Karakter" minlength="6">
                </div>
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Ulangi Password" minlength="6">
                </div>
            </div>
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary py-2 fw-semibold" style="border-radius: 10px;">Daftar</button>
            </div>
            <div class="text-center">
                <p class="mb-0">Sudah punya akun? <a href="index.php?page=login" class="fw-bold text-decoration-none" style="color: var(--secondary-color);">Masuk</a></p>
                <a href="index.php" class="text-muted text-decoration-none small mt-2 d-inline-block"><i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda</a>
            </div>
        </form>
    </div>
</div>
<?php require 'views/layouts/footer.php'; ?>
