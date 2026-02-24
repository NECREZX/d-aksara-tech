<?php
require_login();
if(is_admin()) redirect('index.php?page=admin_dashboard');

$stmt = $conn->query("SELECT * FROM services ORDER BY name ASC");
$services = $stmt->fetchAll();

$selected_service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    
    // Generate order code (JOKI-YYMMDD-RANDOM)
    $order_code = 'JOKI-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
    
    // Get Service Price
    $stmt = $conn->prepare("SELECT base_price FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $price = $stmt->fetchColumn();

    // Calculate urgency modifier (simplified)
    $today = strtotime(date('Y-m-d'));
$deadline_ts = strtotime($deadline);
$days_to_deadline = ceil(($deadline_ts - $today) / (60 * 60 * 24));
    if($days_to_deadline <= 2) {
        $price += $price * 0.5; // 50% urgency fee
    } elseif($days_to_deadline <= 5) {
        $price += $price * 0.1; // 10% urgency fee
    }

    $file_path = null;
    if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
        $target_dir = "uploads/materials/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES["material_file"]["name"], PATHINFO_EXTENSION);
        $new_filename = $order_code . "_MATERIAL." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["material_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        }
    }

    $stmt = $conn->prepare("INSERT INTO orders (order_code, customer_id, service_id, description, deadline, file_path, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$order_code, $_SESSION['user_id'], $service_id, $description, $deadline, $file_path, $price])) {
        // Notification for User
        $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
        $stmt_notif->execute([$_SESSION['user_id'], "Order baru berhasil dibuat dengan Kode: $order_code. Silakan lakukan pembayaran.", "info"]);
        
        set_flash_message('success', 'Order berhasil dibuat! Silakan upload bukti pembayaran.');
        redirect('index.php?page=customer_history');
    } else {
        set_flash_message('danger', 'Gagal membuat order. Silakan coba lagi.');
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
            <li><a href="index.php?page=customer_order" class="active"><i class="fas fa-cart-plus"></i> Pesan Layanan</a></li>
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
        <!-- Minimal Topbar Navbar -->
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
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold m-0" style="color: var(--text-color);">Form Pemesanan</h3>
                    
                </div>
            </div>

            <form action="index.php?page=customer_order" method="POST" enctype="multipart/form-data" id="orderForm">
                <div class="row g-4">
                    <!-- Left Column: Order Details -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4 p-md-5">
                                <h5 class="fw-bold mb-4 border-bottom pb-3"><i class="fas fa-list-alt me-2" style="color: var(--text-color);"></i>Informasi Layanan</h5>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Pilih Layanan</label>
                                    <select class="form-select" name="service_id" id="service_id" required>
                                        <option value="" disabled selected>-- Pilih Layanan yang Anda Butuhkan --</option>
                                        <?php foreach($services as $s): ?>
                                            <option value="<?= $s['id'] ?>" data-price="<?= $s['base_price'] ?>" <?= ($s['id'] == $selected_service_id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['name']) ?> (Mulai dari: Rp <?= number_format($s['base_price'], 0, ',', '.') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Detail Tugas / Instruksi Khusus</label>
                                    <textarea class="form-control" name="description" rows="6" required placeholder="Jelaskan secara detail, format yang diinginkan, kriteria penilaian, atau catatan khusus lainnya..."></textarea>
                                </div>

                                <h5 class="fw-bold mb-4 mt-5 border-bottom pb-3"><i class="fas fa-paperclip text-color me-2"></i>Lampiran & Tenggat Waktu</h5>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tenggat Waktu (Deadline)</label>
                                        <input type="date" class="form-control" name="deadline" id="deadline" required min="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">File Materi/Referensi <small class="text-muted">(Opsional)</small></label>
                                        <input type="file" class="form-control" name="material_file">
                                        <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i>Maksimal 10MB (PDF, DOCX, ZIP, JPG)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 2rem; z-index: 10;">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4 border-bottom pb-3">Ringkasan Tagihan</h5>
                                
                                <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark p-3 rounded-4 mb-3 shadow-none" id="urgencyWarning" style="display:none;">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-bolt text-warning fs-4 me-3 mt-1"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1">Layanan Prioritas</h6>
                                            <p class="mb-0 small" id="urgencyText">Biaya tambahan berlaku untuk deadline singkat.</p>
                                        </div>
                                    </div>
                                </div>

                                <ul class="list-unstyled mb-3">
                                    <li class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted"><i class="fas fa-tag fa-fw me-1"></i>Harga Dasar</span>
                                        <span class="fw-medium" id="basePriceDisplay">Rp 0</span>
                                    </li>
                                    <li class="justify-content-between align-items-center mb-2" id="urgencyFeeRow" style="display:none !important;">
                                        <span class="text-warning fw-medium"><i class="fas fa-bolt fa-fw me-1"></i>Biaya Tambahan</span>
                                        <span class="fw-bold text-warning" id="urgencyFeeDisplay">+ Rp 0</span>
                                    </li>
                                </ul>

                                <hr class="border-secondary opacity-25 mb-3">

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="fw-bold text-dark">Total Pembayaran</span>
                                    <span class="fs-4 fw-bolder text-primary" id="estimatedPrice" style="letter-spacing: -1px;">Rp 0</span>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2">
                                    <i class="fas fa-check-circle"></i> Konfirmasi Pesanan
                                </button>
                                
                                <p class="text-center text-muted small mt-3 mb-0">
                                    <i class="fas fa-shield-alt text-success me-1"></i> Keamanan privasi dijamin 100%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceSelect = document.getElementById('service_id');
        const deadlineInput = document.getElementById('deadline');
        const priceDisplay = document.getElementById('estimatedPrice');
        const warningBox = document.getElementById('urgencyWarning');

        function calculatePrice() {
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            if(!selectedOption || !selectedOption.dataset.price) return;

            let basePrice = parseFloat(selectedOption.dataset.price);
            let finalPrice = basePrice;
            let urgencyFee = 0;
            
            document.getElementById('basePriceDisplay').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(basePrice);
            
            if(deadlineInput.value) {
                const today = new Date();
                today.setHours(0,0,0,0);
                const deadlineDate = new Date(deadlineInput.value);
                
                const diffTime = deadlineDate.getTime() - today.getTime();
const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                const urgencyFeeRow = document.getElementById('urgencyFeeRow');
                
                if(diffDays <= 2) {
                    urgencyFee = basePrice * 0.5; // +50%
                    finalPrice += urgencyFee; 
                    warningBox.innerText = "Biaya Tambahan 50% untuk layanan Kilat (1-2 hari).";
                    warningBox.style.display = "block";
                    urgencyFeeRow.style.setProperty('display', 'flex', 'important');
                    document.getElementById('urgencyFeeRow').firstElementChild.innerHTML = '<i class="fas fa-bolt fa-fw me-1"></i>Biaya Prioritas Deadline<small>(+50% Kilat)</small>';
                } else if(diffDays <= 5) {
                    urgencyFee = basePrice * 0.1; // +10%
                    finalPrice += urgencyFee;
                    warningBox.innerText = "Biaya Tambahan 10% untuk layanan Cepat (3-5 hari).";
                    warningBox.style.display = "block";
                    urgencyFeeRow.style.setProperty('display', 'flex', 'important');
                    document.getElementById('urgencyFeeRow').firstElementChild.innerHTML = '<i class="fas fa-bolt fa-fw me-1"></i>Biaya Prioritas Deadline<small>(+10% Cepat)</small>';
                } else {
                    warningBox.style.display = "none";
                    urgencyFeeRow.style.setProperty('display', 'none', 'important');
                }
                
                if(urgencyFee > 0) {
                    document.getElementById('urgencyFeeDisplay').innerText = '+ ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(urgencyFee);
                }
            }

            priceDisplay.innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(finalPrice);
        }

        serviceSelect.addEventListener('change', calculatePrice);
        deadlineInput.addEventListener('change', calculatePrice);
        
        // Initial calc if loaded via GET parameters
        if(serviceSelect.value) calculatePrice();
    });
</script>

<?php require 'views/layouts/footer.php'; ?>
