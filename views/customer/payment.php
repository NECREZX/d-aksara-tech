<?php
require_login();
if(is_admin()) redirect('index.php?page=admin_dashboard');

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT o.*, s.name as service_name, s.base_price FROM orders o JOIN services s ON o.service_id = s.id WHERE o.id = ? AND o.customer_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

$basePrice = $order['base_price'] ?? 0;
$finalPrice = $order['price'] ?? 0;
$urgencyFee = $finalPrice - $basePrice;
if ($urgencyFee < 0) $urgencyFee = 0;

if(!$order) {
    set_flash_message('danger', 'Order tidak ditemukan atau Anda tidak berhak mengaksesnya.');
    redirect('index.php?page=customer_history');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['payment_struct']) && $_FILES['payment_struct']['error'] == 0) {
        $target_dir = "uploads/payments/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES["payment_struct"]["name"], PATHINFO_EXTENSION);
        $new_filename = $order['order_code'] . "_PAYMENT." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["payment_struct"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE orders SET payment_proof = ? WHERE id = ?");
            if ($stmt->execute([$target_file, $order_id])) {
                // Notif Admin
                $stmt_admin = $conn->query("SELECT id FROM users WHERE role = 'admin'");
                $admins = $stmt_admin->fetchAll();
                foreach($admins as $admin) {
                    $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
                    $stmt_notif->execute([$admin['id'], "Bukti pembayaran diupload untuk order: " . $order['order_code'], "success"]);
                }
                
                // Notif Customer
                $stmt_notif_cust = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
                $stmt_notif_cust->execute([$_SESSION['user_id'], "Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.", "success"]);
                
                set_flash_message('success', 'Bukti pembayaran berhasil diupload! Menunggu konfirmasi admin.');
                redirect('index.php?page=customer_history');
            }
        } else {
            set_flash_message('danger', 'Gagal mengupload file gambar.');
        }
    } else {
        set_flash_message('danger', 'Pilih file terlebih dahulu.');
    }
}

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
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card border-0 p-4 shadow-sm text-center">
                        <div class="mb-4">
                            <i class="fas fa-file-invoice-dollar fa-4x text-color mb-3"></i>
                            <h4 class="fw-bold text-dark">Pembayaran Order</h4>
                            <span class="badge bg-light text-dark fs-6 border border-2 px-3 py-2 rounded-pill"><?= $order['order_code'] ?></span>
                        </div>

                        <div class="card bg-light border-0 p-3 mb-4 rounded-3 text-start">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><span class="text-muted"><i class="fas fa-tags fa-fw"></i> Layanan:</span> <span class="fw-semibold float-end"><?= htmlspecialchars($order['service_name']) ?></span></li>
                                <li class="mb-2"><span class="text-muted"><i class="fas fa-calendar-times fa-fw"></i> Deadline:</span> <span class="fw-semibold text-danger float-end"><?= date('d M Y', strtotime($order['deadline'])) ?></span></li>
                                
                                <li class="mb-2 border-top border-secondary border-opacity-25 pt-2 mt-2"><span class="text-muted"><i class="fas fa-tag fa-fw"></i> Harga Dasar:</span> <span class="fw-medium float-end">Rp <?= number_format($basePrice, 0, ',', '.') ?></span></li>
                                <?php if($urgencyFee > 0): 
                                $pct = round(($urgencyFee / max(1, $basePrice)) * 100);
                                $type_text = ($pct > 20) ? "Kilat" : "Cepat";
                                ?>
                                <li class="mb-2"><span class="text-warning fw-medium"><i class="fas fa-bolt fa-fw"></i> Biaya Prioritas Deadline<small class="text-muted"><i class="ms-1">(+<?= $pct ?>% <?= $type_text ?>)</i></small>:</span> <span class="fw-bold text-warning float-end">+ Rp <?= number_format($urgencyFee, 0, ',', '.') ?></span></li>
                                <?php endif; ?>
                                
                                <li class="border-top border-2 border-secondary border-opacity-25 pt-2 mt-2"><span class="text-dark fw-bold"><i class="fas fa-money-bill-wave fa-fw"></i> Total Tagihan:</span> <span class="fw-bold text-success fs-5 float-end">Rp <?= number_format($finalPrice, 0, ',', '.') ?></span></li>
                            </ul>
                        </div>

                        <?php if (empty($order['payment_proof'])): ?>
                            <div class="mb-4 text-center">
                                
                                <img src="assets/img/qr.jpeg" alt="QRIS D'AKSARA TECH" class="img-fluid rounded shadow-sm mb-3" style="max-width: 200px;">
                            </div>

                            <div class="alert px-3 py-2 text-start shadow-sm mb-4" style="background: var(--hover-bg); border-left: 4px solid var(--accent-color);">
                                <small class="text-muted"><i class="fas fa-info-circle"></i> Silakan scan QRIS di atas, atau transfer ke E-Wallet <strong>Gopay, Dana, ShopeePay 081279164076</strong> lalu upload buktinya di bawah ini.</small>
                            </div>

                            <form action="index.php?page=customer_payment&id=<?= $order['id'] ?>" method="POST" enctype="multipart/form-data">
                                <div class="mb-4 text-start">
                                    <label class="form-label fw-semibold">Upload Bukti Transfer</label>
                                    <input type="file" class="form-control" name="payment_struct" accept="image/*,.pdf" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary rounded-pill fw-bold"><i class="fas fa-upload me-2"></i>Kirim Bukti Pembayaran</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-success text-center">
                                <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                                <strong>Bukti pembayaran sudah kami terima!</strong><br>
                                <small>Tim kami akan segera memproses order Anda. Terima kasih!</small>
                            </div>
                            <!-- Show payment image if valid extension -->
                            <?php 
                            $ext = strtolower(pathinfo($order['payment_proof'], PATHINFO_EXTENSION));
                            if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): 
                            ?>
                                <img src="<?= $order['payment_proof'] ?>" alt="Bukti Pembayaran" class="img-fluid rounded mt-3 shadow-sm border" style="max-height: 250px; object-fit: contain;">
                            <?php else: ?>
                                <a href="<?= $order['payment_proof'] ?>" target="_blank" class="btn btn-outline-success btn-sm mt-3"><i class="fas fa-eye me-1"></i>Lihat Bukti Upload</a>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                <a href="index.php?page=customer_history" class="btn btn-outline-secondary rounded-pill">Kembali ke Riwayat</a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
