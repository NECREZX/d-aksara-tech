<?php
require_once 'config/database.php';
require 'views/layouts/header.php';

// Fetch all services
$stmt = $conn->query("SELECT * FROM services ORDER BY name ASC");
$services = $stmt->fetchAll();
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php" style="color: var(--secondary-color);">
            <i class="fas fa-rocket me-2"></i>Jasa Joki
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-medium" href="index.php?page=home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-medium active" href="index.php?page=services">Layanan</a>
                    </li>
                </ul>
                <div class="d-flex ms-lg-3 align-items-center gap-3 mt-3 mt-lg-0">
                    <div class="theme-switch shadow-sm" id="theme-toggle" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </div>
                    <?php if(is_logged_in()): ?>
                        <a href="index.php?page=<?= $_SESSION['role'] === 'admin' ? 'admin_dashboard' : 'customer_dashboard' ?>" class="btn btn-primary px-4 rounded-pill fw-medium shadow-sm"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                    <?php else: ?>
                        <div class="d-flex gap-2">
                            <a href="index.php?page=login" class="btn btn-outline-primary px-4 rounded-pill fw-medium">Masuk</a>
                            <a href="index.php?page=register" class="btn btn-primary px-4 rounded-pill fw-medium shadow-sm">Daftar</a>
                        </div>
                    <?php endif; ?>
                </div>
        </div>
    </div>
</nav>

<section class="min-vh-100 py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5 pb-3">
            <span class="badge rounded-pill mb-3 px-3 py-2 bg-primary bg-opacity-10 border border-primary border-opacity-25 text-uppercase letter-spacing: 1px;" style="color: var(--secondary-color);">Katalog</span>
            <h1 class="fw-bolder display-5" style="letter-spacing: -1px; color: var(--text-color);">Daftar Layanan Tersedia</h1>
            <p class="text-muted lead mt-2">Pilih layanan yang Anda butuhkan dan biarkan profesional kami menyelesaikannya.</p>
        </div>

        <!-- Filter Input (Simple JS) -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-6">
                <div class="input-group input-group-lg shadow-sm" style="border-radius: 50px; overflow: hidden; border: 1px solid var(--border-color);">
                    <span class="input-group-text border-0 text-muted" id="search-addon" style="background-color: var(--card-bg);"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control border-0" id="searchInput" placeholder="Cari layanan apa?" aria-label="Search" aria-describedby="search-addon" style="background-color: var(--card-bg); color: var(--text-color);">
                </div>
            </div>
        </div>

        <div class="row g-4" id="serviceList">
            <?php 
                $icons = ['fa-laptop-code', 'fa-pen-nib', 'fa-chart-pie', 'fa-globe', 'fa-file-alt', 'fa-book', 'fa-code', 'fa-palette', 'fa-database', 'fa-mobile-alt'];
                foreach($services as $index => $s): 
                    $icon = $icons[$index % count($icons)];
            ?>
                <div class="col-md-6 col-lg-4 service-item">
                    <div class="card h-100 border-0 p-4 hover-lift" style="background-color: var(--card-bg);">
                        <div class="d-flex align-items-center mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 me-3" style="width: 50px; height: 50px;">
                                <i class="fas <?= $icon ?> fa-lg" style="color: var(--secondary-color);"></i>
                            </div>
                            <h5 class="card-title fw-bold mb-0 service-name" style="color: var(--text-color);"><?= htmlspecialchars($s['name']) ?></h5>
                        </div>
                        <p class="card-text text-muted flex-grow-1 service-desc" style="line-height: 1.6;"><?= htmlspecialchars($s['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top">
                            <div>
                                <small class="text-muted d-block mb-1">Mulai dari</small>
                                <span class="fw-bold fs-5" style="color: var(--secondary-color);">Rp <?= number_format($s['base_price'], 0, ',', '.') ?></span>
                            </div>
                            <?php if(is_logged_in() && $_SESSION['role'] === 'customer'): ?>
                                <a href="index.php?page=customer_order&service_id=<?= $s['id'] ?>" class="btn btn-primary rounded-pill px-3 py-2"><i class="fas fa-cart-plus me-1"></i> Pesan</a>
                            <?php else: ?>
                                <a href="index.php?page=login" class="btn btn-outline-primary rounded-pill px-3 py-2"><i class="fas fa-cart-plus me-1"></i> Pesan</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div id="noResult" class="text-center py-5 d-none">
            <div class="mb-3">
                <i class="fas fa-box-open fa-4x text-muted opacity-50"></i>
            </div>
            <h4 class="text-muted">Layanan tidak ditemukan</h4>
            <p class="text-muted">Coba gunakan kata kunci pencarian yang lain.</p>
        </div>
    </div>
</section>

<!-- Simple Footer -->
<footer class="py-4 text-center mt-auto" style="border-top: 1px solid var(--border-color);">
    <div class="container">
        <p class="mb-0 text-muted">&copy; <?= date('Y') ?> Jasa Joki. Crafted with <i class="fas fa-heart" style="color: var(--primary-color);"></i> for Students.</p>
    </div>
</footer>

<style>
    .hover-lift { transition: transform 0.25s ease, box-shadow 0.25s ease; }
    .hover-lift:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg) !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const serviceItems = document.querySelectorAll('.service-item');
        const noResult = document.getElementById('noResult');

        searchInput.addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            let visibleCount = 0;

            serviceItems.forEach(item => {
                const name = item.querySelector('.service-name').innerText.toLowerCase();
                const desc = item.querySelector('.service-desc').innerText.toLowerCase();
                
                if (name.includes(term) || desc.includes(term)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (visibleCount === 0) {
                noResult.classList.remove('d-none');
            } else {
                noResult.classList.add('d-none');
            }
        });
    });
</script>

<?php require 'views/layouts/footer.php'; ?>
