<?php
require_once 'config/database.php';
require 'views/layouts/header.php';

// Fetch all services for marquee preview
$stmt = $conn->query("SELECT * FROM services ORDER BY RAND()");
$services = $stmt->fetchAll();
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php" style="color: var(--secondary-color);">
            <i class="fas fa-rocket me-2"></i>D'AKSARA TECH
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-3 mb-lg-0 align-items-center text-center">
                    
                </ul>
                <div class="d-flex flex-column flex-lg-row ms-lg-3 align-items-center gap-3">
                    <div class="theme-switch shadow-sm" id="theme-toggle" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </div>
                    <?php if(is_logged_in()): ?>
                        <a href="index.php?page=<?= $_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'customer_dashboard' ?>" class="btn btn-primary px-4 rounded-pill fw-medium shadow-sm w-100 w-lg-auto"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                    <?php else: ?>
                        <div class="d-flex gap-2 w-100 justify-content-center">
                            <a href="index.php?page=login" class="btn btn-outline-primary px-4 rounded-pill fw-medium flex-grow-1 flex-lg-grow-0">Masuk</a>
                            <a href="index.php?page=register" class="btn btn-primary px-4 rounded-pill fw-medium shadow-sm flex-grow-1 flex-lg-grow-0">Daftar</a>
                        </div>
                    <?php endif; ?>
                </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="min-vh-100 d-flex align-items-center position-relative overflow-hidden" style="padding-top: 80px; background: linear-gradient(135deg, var(--bg-color) 0%, var(--hover-bg) 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 text-center text-lg-start">
                <span class="badge rounded-pill mb-3 px-3 py-2 bg-primary bg-opacity-10 border border-primary border-opacity-25" style="color: var(--secondary-color);"> Platform Jasa Akademik & Teknologi </span>
                <h1 class="display-4 fw-bolder mb-4" style="line-height: 1.15; letter-spacing: -1px; color: var(--text-color);"> Solusi Profesional untuk Setiap <span class="position-relative" style="color: var(--secondary-color);">Kebutuhan Tugas <span class="position-relative" style="color: var(--text-color);"> dan <span class="position-relative" style="color: var(--secondary-color);">Project Anda<svg class="position-absolute w-100" style="bottom: -5px; left: 0; height: 12px; z-index: -1;" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0,10 Q50,20 100,5" fill="none" stroke="var(--accent-color)" stroke-width="8" stroke-linecap="round"/></svg></span> </h1>
                <p class="lead mb-4 text-muted pe-lg-5" style="font-size: 1.15rem;"> Kami menyediakan layanan bantuan dalam bidang Tugas Akademik Umum, Presentasi & Desain, Pengembangan Website, Pengembangan Aplikasi, serta Perancangan Sistem & Database dengan standar berkualitas. </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start mt-4">
                    <a href="index.php?page=register" class="btn btn-primary shadow-lg btn-lg rounded-pill px-4 py-3 fs-6 d-flex align-items-center justify-content-center"> <i class="fas fa-rocket me-2"></i> Mulai Sekarang </a>
                    <a href="#services-preview" class="btn btn-outline-primary btn-lg rounded-pill px-4 py-3 fs-6 d-flex align-items-center justify-content-center"> Lihat Layanan <i class="fas fa-arrow-down ms-2"></i> </a>
                </div>
            </div>
            <div class="col-lg-5 offset-lg-1 text-center position-relative">
                <div class="position-absolute rounded-circle bg-accent bg-opacity-10" style="width: 400px; height: 400px; top: -50px; right: -50px; filter: blur(60px); z-index: 0; background-color: var(--accent-color);"></div>
                <div class="position-absolute rounded-circle bg-primary bg-opacity-10" style="width: 300px; height: 300px; bottom: -50px; left: -50px; filter: blur(50px); z-index: 0; background-color: var(--primary-color);"></div>
                
                <div class="p-2 rounded-5 shadow-lg d-inline-block position-relative" style="background-color: var(--card-bg); border: 1px solid var(--border-color); z-index: 1;">
                    <img src="assets/hero_logo.png" alt="D'AKSARA TECH" class="img-fluid" style="border-radius: 2rem; max-height: 400px; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container my-5">
        <div class="text-center mb-5 pb-3">
            <span class="fw-semibold text-uppercase tracking-wider" style="letter-spacing: 1px;" style="color: var(--secondary-color);">Keunggulan Kami</span>
            <h2 class="fw-bolder display-6 mt-2">Mengapa Memilih <span style="color: var(--secondary-color);">D'AKSARA TECH?</span></h2>
        </div>
        <div class="row g-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 text-center p-5 border-0 hover-lift bg-transparent shadow-none">
                    <div class="icon-box mx-auto mb-4 d-flex align-items-center justify-content-center bg-secondary bg-opacity-10" style="width: 80px; height: 80px; border-radius: 20px;">
                        <i class="fas fa-bolt fa-2x" style="color: var(--secondary-color);"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Pengerjaan Cepat</h5>
                    <p class="text-muted mb-0" style="line-height: 1.6;">Selesai sesuai deadline yang Anda tentukan bahkan sebelum batas waktu tiba.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 text-center p-5 border-0 hover-lift bg-transparent shadow-none">
                    <div class="icon-box mx-auto mb-4 text-secondary d-flex align-items-center justify-content-center bg-secondary bg-opacity-10" style="width: 80px; height: 80px; border-radius: 20px;">
                        <i class="fas fa-shield-alt fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Aman & Rahasia</h5>
                    <p class="text-muted mb-0" style="line-height: 1.6;">Privasi dan data diri Anda dijamin kerahasiaannya. Bebas dari masalah akademik.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 text-center p-5 border-0 hover-lift bg-transparent shadow-none">
                    <div class="icon-box mx-auto mb-4 text-success d-flex align-items-center justify-content-center bg-secondary bg-opacity-10" style="width: 80px; height: 80px; border-radius: 20px;">
                        <i class="fas fa-medal fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Kualitas Premium</h5>
                    <p class="text-muted mb-0" style="line-height: 1.6;">Dikerjakan oleh profesional dengan hasil berstandar tinggi yang lulus uji kualitas.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card h-100 text-center p-5 border-0 hover-lift bg-transparent shadow-none">
                    <div class="icon-box mx-auto mb-4 text-danger d-flex align-items-center justify-content-center bg-secondary bg-opacity-10" style="width: 80px; height: 80px; border-radius: 20px;">
                        <i class="fas fa-file-invoice-dollar fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Harga Terjangkau</h5>
                    <p class="text-muted mb-0" style="line-height: 1.6;">Sistem harga transparan dan terbuka, disesuaikan dengan tingkat kesulitan serta kebutuhan project.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section id="services-preview" class="py-5 my-5">
    <div class="container">
        <div class="text-center mb-5 pb-3">
            <span class="fw-semibold text-uppercase tracking-wider" style="letter-spacing: 1px;" style="color: var(--secondary-color);">Layanan Kami</span>
            <h2 class="fw-bolder display-6 mt-2">Solusi Untuk <span style="color: var(--secondary-color);">Kebutuhan Anda</span></h2>
        </div>
        
        <?php 
            $icons = ['fa-laptop-code', 'fa-pen-nib', 'fa-chart-pie', 'fa-globe', 'fa-file-alt', 'fa-book', 'fa-code', 'fa-palette', 'fa-database', 'fa-mobile-alt'];
            ob_start();
            foreach($services as $index => $s): 
                $icon = $icons[$index % count($icons)];
        ?>
            <div class="marquee-card">
                <div class="card h-100 border-0 p-4 hover-lift" style="background-color: var(--card-bg);">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 mb-4" style="width: 50px; height: 50px;">
                        <i class="fas <?= $icon ?>" style="color: var(--secondary-color);" fa-lg></i>
                    </div>
                    <h5 class="card-title fw-bold mb-3" style="color: var(--text-color);"><?= htmlspecialchars($s['name']) ?></h5>
                    <p class="card-text text-muted flex-grow-1" style="line-height: 1.6;"><?= htmlspecialchars($s['description']) ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top">
                        <div>
                            <small class="text-muted d-block mb-1">Mulai dari</small>
                            <span class="fw-bold fs-5" style="color: var(--secondary-color);">Rp <?= number_format($s['base_price'], 0, ',', '.') ?></span>
                        </div>
                        <a href="index.php?page=login" class="btn btn-sm btn-primary rounded-pill px-3 py-2" style="background-color: var(--text-color); border: none;"><i class="fas fa-cart-plus me-1"></i> Pesan</a>
                    </div>
                </div>
            </div>
        <?php 
            endforeach; 
            $services_html = ob_get_clean();
        ?>

        <div class="marquee-wrapper mx-n3 px-3 mx-md-0 px-md-0">
            <div class="marquee-group">
                <?= $services_html ?>
            </div>
            <div aria-hidden="true" class="marquee-group">
                <?= $services_html ?>
            </div>
        </div>

    </div>
</section>

<!-- Simple Footer -->
<footer class="py-4 text-center mt-auto" style="border-top: 1px solid var(--border-color);">
    <div class="container">
        <p class="mb-0 text-muted">&copy; <?= date('Y') ?> D'AKSARA TECH</p>
    </div>
</footer>

<style>
    .hover-lift {
        transition: transform 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-10px);
    }
    /* Marquee CSS */
    .marquee-wrapper {
        display: flex;
        overflow: hidden;
        user-select: none;
        gap: 1.5rem;
        padding: 1rem 0;
        mask-image: linear-gradient(to right, hsl(0 0% 0% / 0), hsl(0 0% 0% / 1) 10%, hsl(0 0% 0% / 1) 90%, hsl(0 0% 0% / 0));
        -webkit-mask-image: linear-gradient(to right, hsl(0 0% 0% / 0), hsl(0 0% 0% / 1) 10%, hsl(0 0% 0% / 1) 90%, hsl(0 0% 0% / 0));
    }
    .marquee-group {
        flex-shrink: 0;
        display: flex;
        align-items: stretch;
        gap: 1.5rem;
        min-width: 100%;
        animation: scroll-x 25s linear infinite;
    }
    .marquee-wrapper:hover .marquee-group {
        animation-play-state: paused;
    }
    .marquee-card {
        width: 350px;
        flex: 0 0 auto;
    }
    @keyframes scroll-x {
        from { transform: translateX(0); }
        to { transform: translateX(calc(-100% - 1.5rem)); }
    }
    @media (max-width: 768px) {
        .marquee-card {
            width: 300px;
        }
    }
</style>

<?php require 'views/layouts/footer.php'; ?>
