<?php
require_login();
if(is_admin()) redirect('index.php?page=admin_dashboard');
require 'views/layouts/header.php';

$user_id = $_SESSION['user_id'];

// Get Stats
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ? AND status = 'Menunggu'");
$stmt->execute([$user_id]);
$pending = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ? AND status = 'Diproses'");
$stmt->execute([$user_id]);
$process = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ? AND status = 'Selesai'");
$stmt->execute([$user_id]);
$done = $stmt->fetchColumn();

// Get Recent Orders
$stmt = $conn->prepare("SELECT o.*, s.name as service_name FROM orders o JOIN services s ON o.service_id = s.id WHERE o.customer_id = ? ORDER BY o.created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();
?>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="shadow-sm">
        <div class="sidebar-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold" style="color: var(--secondary-color);"><i class="fas fa-rocket me-2"></i>Customer Panel</h4>
            <button class="btn btn-sm d-md-none" id="sidebarCollapseBtn"><i class="fas fa-times"></i></button>
        </div>
        <ul class="list-unstyled sidebar-menu mt-3">
            <li><a href="index.php?page=customer_dashboard" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="index.php?page=customer_order"><i class="fas fa-cart-plus"></i> Pesan Layanan</a></li>
            <li><a href="index.php?page=customer_history"><i class="fas fa-history"></i> Riwayat Order</a></li>
            
            <?php 
            $notif_stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $notif_stmt->execute([$_SESSION['user_id']]);
            $unread_count = $notif_stmt->fetchColumn();
            
            $msg_stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
            $msg_stmt->execute([$_SESSION['user_id']]);
            $unread_msg_count = $msg_stmt->fetchColumn();
            ?>
            <li>
                <a href="index.php?page=customer_notifications" class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-bell"></i> Notifikasi</span>
                    <?php if($unread_count > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li>
                <a href="index.php?page=customer_messages" class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-comments"></i> Chat</span>
                    <?php if($unread_msg_count > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $unread_msg_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
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
                            <span class="fw-medium"><?= htmlspecialchars($_SESSION['name']) ?></span>
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
            <h3 class="fw-bold mb-4">Halo, <?= htmlspecialchars(explode(' ', trim($_SESSION['name']))[0]) ?>! 👋</h3>
            
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card p-4 border-0 h-100 hover-lift">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.85rem;">Total Order</h6>
                                <h2 class="fw-bolder text-primary mb-0"><?= $total_orders ?></h2>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="fas fa-shopping-bag fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-4 border-0 h-100 hover-lift">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.85rem;">Menunggu</h6>
                                <h2 class="fw-bolder text-warning mb-0"><?= $pending ?></h2>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-4 border-0 h-100 hover-lift">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.85rem;">Diproses</h6>
                                <h2 class="fw-bolder text-info mb-0"><?= $process ?></h2>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-4 border-0 h-100 hover-lift">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.85rem;">Selesai</h6>
                                <h2 class="fw-bolder text-success mb-0"><?= $done ?></h2>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 py-3 d-flex align-items-center justify-content-between rounded-top-4" style="background-color: var(--card-bg);">
                    <h5 class="mb-0 fw-bold" style="color: var(--text-color);"><i class="fas fa-clipboard-list me-2"></i>Pesanan Terbaru</h5>
                    <a href="index.php?page=customer_history" class="btn btn-sm btn-outline-primary rounded-pill">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Order</th>
                                <th>Layanan</th>
                                <th>Deadline</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recent_orders)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada order. Mulai pesan layanan sekarang!</td></tr>
                            <?php else: ?>
                                <?php foreach($recent_orders as $o): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark border"><?= $o['order_code'] ?></span></td>
                                        <td><?= htmlspecialchars($o['service_name']) ?></td>
                                        <td><?= date('d M Y', strtotime($o['deadline'])) ?></td>
                                        <td>Rp <?= number_format($o['price'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php if($o['status'] == 'Menunggu'): ?>
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fas fa-clock me-1"></i>Menunggu</span>
                                            <?php elseif($o['status'] == 'Diproses'): ?>
                                                <span class="badge bg-info text-dark px-3 py-2 rounded-pill"><i class="fas fa-spinner fa-spin me-1"></i>Diproses</span>
                                            <?php else: ?>
                                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-check me-1"></i>Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="index.php?page=customer_payment&id=<?= $o['id'] ?>" class="btn btn-sm <?= empty($o['payment_proof']) ? 'btn-danger' : 'btn-success' ?>" title="Pembayaran">
                                                <i class="fas <?= empty($o['payment_proof']) ? 'fa-file-invoice-dollar' : 'fa-receipt' ?>"></i>
                                            </a>
                                            <?php if($o['status'] == 'Selesai' && !empty($o['result_file'])): ?>
                                                <a href="<?= $o['result_file'] ?>" class="btn btn-sm btn-primary" title="Download Hasil" download><i class="fas fa-download"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('sidebarCollapseBtn')?.addEventListener('click', function() {
        document.getElementById('sidebar').classList.remove('active');
    });
</script>

<?php require 'views/layouts/footer.php'; ?>
