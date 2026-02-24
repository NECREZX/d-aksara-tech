<?php
require_admin();

// Get System Stats
$stmt = $conn->query("SELECT COUNT(*) FROM orders");
$total_orders = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$total_users = $stmt->fetchColumn();

$stmt = $conn->query("SELECT SUM(price) FROM orders WHERE status = 'Selesai'");
$total_revenue = $stmt->fetchColumn() ?: 0;

$stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Menunggu'");
$pending_orders = $stmt->fetchColumn();

// Chart Data (Orders by Status)
$chart_labels = ['Menunggu', 'Diproses', 'Selesai'];
$chart_counts = [0, 0, 0];

$stmt = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$status_data = $stmt->fetchAll();
foreach($status_data as $row) {
    $index = array_search($row['status'], $chart_labels);
    if($index !== false) {
        $chart_counts[$index] = (int)$row['count'];
    }
}

// Chart Data (Revenue last 7 days)
$rev_labels = [];
$rev_amounts_map = [];

// Pre-fill last 7 days with 0
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $rev_labels[] = date('d M', strtotime($date));
    $rev_amounts_map[$date] = 0;
}

$stmt = $conn->query("
    SELECT DATE(created_at) as date, SUM(price) as daily_revenue 
    FROM orders 
    WHERE status = 'Selesai' AND created_at >= DATE(NOW()) - INTERVAL 6 DAY
    GROUP BY DATE(created_at)
");
$revenue_data = $stmt->fetchAll();
foreach($revenue_data as $row) {
    $date = $row['date'];
    if(isset($rev_amounts_map[$date])) {
        $rev_amounts_map[$date] = (float)$row['daily_revenue'];
    }
}

$rev_amounts = array_values($rev_amounts_map);

require 'views/layouts/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="shadow-sm">
        <div class="sidebar-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold" style="color: var(--secondary-color);"><i class="fas fa-user-shield me-2"></i>Admin Panel</h4>
            <button class="btn btn-sm d-md-none" id="sidebarCollapseBtn"><i class="fas fa-times"></i></button>
        </div>
        <ul class="list-unstyled sidebar-menu mt-3">
            <li><a href="index.php?page=admin_dashboard" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="index.php?page=admin_services"><i class="fas fa-list-alt"></i> Kelola Layanan</a></li>
            <?php 
                $new_order_stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Menunggu'");
                $new_order_count = $new_order_stmt->fetchColumn();
                
                $msg_stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
                $msg_stmt->execute([$_SESSION['user_id']]);
                $unread_msg_count = $msg_stmt->fetchColumn();
            ?>
            <li>
                <a href="index.php?page=admin_orders" class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-shopping-cart"></i> Kelola Order</span>
                    <?php if($new_order_count > 0): ?>
                        <span class="badge bg-danger rounded-pill shadow-sm"><?= $new_order_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="index.php?page=admin_messages" class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-comments"></i> Chat</span>
                    <?php if($unread_msg_count > 0): ?>
                        <span class="badge bg-danger rounded-pill shadow-sm"><?= $unread_msg_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="index.php?page=admin_users"><i class="fas fa-users"></i> Kelola Pengguna</a></li>
            <li><a href="index.php?page=customer_profile"><i class="fas fa-user-circle"></i> Profil Saya</a></li>
            <li class="mt-5"><a href="index.php?page=logout" class="text-danger"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent mb-4">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-primary d-md-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex align-items-center ms-auto">
                    <div class="theme-switch me-3" id="theme-toggle">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </div>
                    <div class="dropdown">
                        <a class="text-decoration-none dropdown-toggle text-dark" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text-color) !important;">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=random" class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                            <span class="fw-medium">Admin <?= htmlspecialchars(explode(' ', trim($_SESSION['name']))[0]) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="index.php?page=customer_profile"><i class="fas fa-user me-2"></i>Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="index.php?page=logout"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <h3 class="fw-bold mb-4">Dashboard</h3>
            
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card p-4 border-0 shadow-sm h-100 hover-lift">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.85rem;">Total Pendapatan</h6>
                                <h4 class="fw-bolder mb-0" style="color: var(--secondary-color);">Rp <?= number_format($total_revenue, 0, ',', '.') ?></h4>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="fas fa-wallet fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-4 border-0 shadow-sm h-100 hover-lift">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.85rem;">Total Order</h6>
                                <h4 class="fw-bolder text-secondary mb-0"><?= $total_orders ?></h4>
                            </div>
                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="fas fa-shopping-bag fa-2x text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-4 border-0 shadow-sm h-100 hover-lift">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.85rem;">Order Menunggu</h6>
                                <h4 class="fw-bolder text-warning mb-0"><?= $pending_orders ?></h4>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-4 border-0 shadow-sm h-100 hover-lift">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.85rem;">Total Pelanggan</h6>
                                <h4 class="fw-bolder text-success mb-0"><?= $total_users ?></h4>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="fas fa-users fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4 h-100" style="background-color: var(--card-bg);">
                        <h5 class="fw-bold mb-4 text-color"><i class="fas fa-chart-line me-2"></i>Pendapatan 7 Hari Terakhir</h5>
                        <canvas id="revenueChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100" style="background-color: var(--card-bg);">
                        <h5 class="fw-bold mb-4 text-center text-color"><i class="fas fa-chart-pie me-2"></i>Distribusi Status Order</h5>
                        <canvas id="statusChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof Chart === 'undefined') {
            console.error("Chart.js is not loaded.");
            return;
        }

        // Shared formatting function for Rupiah
        const formatRupiah = (value) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        };

        // Revenue Chart
        const revCanvas = document.getElementById('revenueChart');
        if(revCanvas) {
            const revCtx = revCanvas.getContext('2d');
            
            // Create gradient line chart background
            const gradient = revCtx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(217, 119, 6, 0.4)'); // var(--secondary-color) with opacity
            gradient.addColorStop(1, 'rgba(217, 119, 6, 0.0)');

            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($rev_labels) ?>,
                    datasets: [{
                        label: 'Pendapatan',
                        data: <?= json_encode($rev_amounts) ?>,
                        borderColor: '#D97706',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#D97706',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)', // var(--primary-color)
                            titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13 },
                            bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 14, weight: 'bold' },
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return formatRupiah(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: 'rgba(0,0,0,0.05)' },
                            ticks: {
                                font: { family: "'Plus Jakarta Sans', sans-serif" },
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                                    if (value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                                    return formatRupiah(value);
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: "'Plus Jakarta Sans', sans-serif" } }
                        }
                    }
                }
            });
        }

        // Status Chart
        const statusCanvas = document.getElementById('statusChart');
        if(statusCanvas) {
            const statusCtx = statusCanvas.getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [{
                        data: <?= json_encode($chart_counts) ?>,
                        backgroundColor: ['#F59E0B', '#3B82F6', '#10B981'], // Warning, Info, Success
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'bottom',
                            labels: {
                                font: { family: "'Plus Jakarta Sans', sans-serif" },
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13 },
                            bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 14, weight: 'bold' },
                            padding: 12,
                            cornerRadius: 8
                        }
                    },
                    cutout: '75%'
                }
            });
        }

        const sidebarBtn = document.getElementById('sidebarCollapseBtn');
        if(sidebarBtn) {
            sidebarBtn.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                if(sidebar) sidebar.classList.remove('active');
            });
        }
    });
</script>

<?php require 'views/layouts/footer.php'; ?>
