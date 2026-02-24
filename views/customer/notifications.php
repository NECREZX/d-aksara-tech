<?php
require_login();
if(is_admin()) redirect('index.php?page=admin_dashboard');

// Fetch notifications for the logged-in customer
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Mark all as read upon visiting this page
$update_stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$update_stmt->execute([$_SESSION['user_id']]);

require 'views/layouts/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="shadow-sm">
        <div class="sidebar-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold" style="color: var(--secondary-color);"><i class="fas fa-rocket me-2"></i>Customer Panel</h4>
            <button class="btn btn-sm d-md-none" id="sidebarCollapseBtn"><i class="fas fa-times"></i></button>
        </div>
        <ul class="list-unstyled sidebar-menu mt-3">
            <li><a href="index.php?page=customer_dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
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
                <a href="index.php?page=customer_notifications" class="active d-flex justify-content-between align-items-center">
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
        <!-- Topbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent mb-4">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-primary d-md-none">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex align-items-center ms-auto">
                    <span class="me-3 fw-semibold d-none d-md-inline" style="color: var(--text-color);">Halo, <?= htmlspecialchars($_SESSION['name']) ?>!</span>
                    <div class="theme-switch me-3" id="theme-toggle">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=random" class="rounded-circle shadow-sm" width="40" alt="Profile">
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0" style="color: var(--text-color);">Pusat Notifikasi</h3>
            </div>

            <div class="card border-0 shadow-sm rounded-4" style="background-color: var(--card-bg);">
                <div class="card-body p-4 p-md-5">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-4x text-muted mb-3 opacity-50"></i>
                            <h5 class="fw-bold mb-2" style="color: var(--text-color);">Belum Ada Notifikasi</h5>
                            <p class="text-muted">Aktivitas dan pembaruan order Anda akan muncul di sini.</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline-container relative" style="border-left: 3px solid var(--border-color); padding-left: 20px; margin-left: 10px;">
                            <?php foreach ($notifications as $notif): 
                                $icon = 'fa-info-circle';
                                $color = 'var(--text-color)';
                                if($notif['type'] == 'success') { $icon = 'fa-check-circle'; $color = 'var(--success-color)'; }
                                elseif($notif['type'] == 'warning') { $icon = 'fa-exclamation-triangle'; $color = 'var(--secondary-color)'; }
                            ?>
                                <div class="mb-4 position-relative">
                                    <div class="position-absolute bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                                         style="width: 30px; height: 30px; left: -36px; top: 0; background-color: var(--card-bg) !important; border: 2px solid <?= $color ?>;">
                                        <i class="fas <?= $icon ?>" style="color: <?= $color ?>; font-size: 14px;"></i>
                                    </div>
                                    <div class="p-3 rounded-4 shadow-sm" style="background-color: var(--hover-bg); border-left: 4px solid <?= $color ?>;">
                                        <p class="mb-1 fw-semibold text-wrap" style="color: var(--text-color); line-height: 1.5;"><?= htmlspecialchars($notif['message']) ?></p>
                                        <small class="text-muted"><i class="fas fa-clock me-1"></i><?= date('d M Y, H:i', strtotime($notif['created_at'])) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
