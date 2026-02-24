<?php
require_login();

$user_id = $_SESSION['user_id'];
$is_admin = is_admin();

// Fetch current user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Update session name if changed
    if ($name !== $_SESSION['name']) {
        $_SESSION['name'] = $name;
    }

    if (!empty($password)) {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            if($stmt->execute([$name, $email, $hashed_password, $user_id])) {
                // Add notification
                $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
                $stmt_notif->execute([$user_id, "Profil akun Anda berhasil diperbarui.", "info"]);
                
                $_SESSION['name'] = $name;
                set_flash_message('success', 'Profil dan Password berhasil diperbarui.');
            } else {
                set_flash_message('danger', 'Gagal memperbarui profil.');
            }
        } else {
            set_flash_message('danger', 'Konfirmasi password tidak cocok.');
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        if($stmt->execute([$name, $email, $user_id])) {
            // Add notification
            $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
            $stmt_notif->execute([$user_id, "Profil akun Anda berhasil diperbarui.", "info"]);
            
            $_SESSION['name'] = $name;
            set_flash_message('success', 'Profil berhasil diperbarui.');
        } else {
            set_flash_message('danger', 'Gagal memperbarui profil.');
        }
    }
    
    // Redirect to self to refresh data
    redirect('index.php?page=customer_profile');
}

require 'views/layouts/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="shadow-sm">
        <div class="sidebar-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold" style="color: var(--secondary-color);">
                <i class="fas <?= $is_admin ? 'fa-user-shield' : 'fa-rocket' ?> me-2"></i><?= $is_admin ? 'Admin Panel' : 'Customer Panel' ?>
            </h4>
            <button class="btn btn-sm d-md-none" id="sidebarCollapseBtn"><i class="fas fa-times"></i></button>
        </div>
        <ul class="list-unstyled sidebar-menu mt-3">
            <?php if($is_admin): ?>
                <li><a href="index.php?page=admin_dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="index.php?page=admin_services"><i class="fas fa-list-alt"></i> Kelola Layanan</a></li>
                <li><a href="index.php?page=admin_orders"><i class="fas fa-shopping-cart"></i> Kelola Order</a></li>
                <li><a href="index.php?page=admin_messages"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="index.php?page=admin_users"><i class="fas fa-users"></i> Kelola Pengguna</a></li>
                <li><a href="index.php?page=customer_profile" class="active"><i class="fas fa-user-circle"></i> Profil Saya</a></li>
            <?php else: ?>
                <li><a href="index.php?page=customer_dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="index.php?page=customer_order"><i class="fas fa-cart-plus"></i> Pesan Layanan</a></li>
                <li><a href="index.php?page=customer_history"><i class="fas fa-history"></i> Riwayat Order</a></li>
                
                <?php 
                $notif_stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                $notif_stmt->execute([$_SESSION['user_id']]);
                $unread_count = $notif_stmt->fetchColumn();
                ?>
                <li>
                    <a href="index.php?page=customer_notifications" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-bell"></i> Notifikasi</span>
                        <?php if($unread_count > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <li><a href="index.php?page=customer_messages"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="index.php?page=customer_profile" class="active"><i class="fas fa-user-circle"></i> Profil Saya</a></li>
            <?php endif; ?>
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
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 p-4 shadow-sm" style="border-radius: 20px;">
                        
                        <div class="text-center mb-5 position-relative">
                            <div class="d-inline-block position-relative">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=random&size=120" class="rounded-circle shadow" alt="Avatar">
                                <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-<?= $is_admin ? 'danger' : 'primary' ?> border border-white border-2" style="transform: translate(-10%, -10%);"><i class="fas <?= $is_admin ? 'fa-shield-alt' : 'fa-user' ?>"></i></span>
                            </div>
                            <h3 class="fw-bold mt-3 mb-1"><?= htmlspecialchars($user['name']) ?></h3>
                            <p class="text-muted"><?= htmlspecialchars($user['email']) ?> &bull; <span class="text-<?= $is_admin ? 'danger' : 'primary' ?> text-capitalize"><?= $user['role'] ?></span></p>
                        </div>

                        <form action="index.php?page=customer_profile" method="POST">
                            <h5 class="fw-bold border-bottom pb-2 mb-4 d-flex align-items-center"><i class="fas fa-info-circle text-primary me-2"></i> Detail Informasi</h5>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label fw-semibold">Nama Lengkap</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light"><i class="fas fa-user-edit text-muted"></i></span>
                                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Alamat Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly title="Email tidak dapat diubah">
                                    </div>
                                    <small class="text-muted"><i class="fas fa-lock pe-1 text-danger"></i> Email digunakan untuk login.</small>
                                </div>
                            </div>

                            <h5 class="fw-bold border-bottom pb-2 mb-4 mt-5 d-flex align-items-center"><i class="fas fa-key text-warning me-2"></i> Keamanan</h5>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label fw-semibold">Password Baru <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                                    <input type="password" class="form-control" name="password" minlength="6" placeholder="*******">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Konfirmasi Password</label>
                                    <input type="password" class="form-control" name="confirm_password" minlength="6" placeholder="*******">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold shadow-sm"><i class="fas fa-save me-2"></i> Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
