<?php
if (is_logged_in()) {
    redirect('index.php?page=' . ($_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'customer_dashboard'));
}
require 'views/layouts/header.php';
?>
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg border-0 p-4" style="width: 100%; max-width: 450px; border-radius: 20px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold" style="color: var(--secondary-color);"><i class="fas fa-rocket me-2"></i>D'AKSARA TECH</h3>
            <p class="text-muted">Masuk ke akun Anda</p>
        </div>
        
        <form action="index.php?page=do_login" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="nama@email.com">
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="********">
                </div>
            </div>
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary py-2 fw-semibold" style="border-radius: 10px;">Login</button>
            </div>
            <div class="text-center">
                <p class="mb-0">Belum punya akun? <a href="index.php?page=register" class="fw-bold text-decoration-none" style="color: var(--accent-color);">Daftar Sekarang</a></p>
                <a href="index.php" class="text-muted text-decoration-none small mt-2 d-inline-block"><i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda</a>
            </div>
        </form>
    </div>
</div>
<?php require 'views/layouts/footer.php'; ?>
