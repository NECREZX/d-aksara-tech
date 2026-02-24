<?php
require_admin();

// Handle Export
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $stmt = $conn->query("
        SELECT o.id, o.order_code, u.name as customer_name, s.name as service_name, 
               o.deadline, o.price, o.status, o.created_at 
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        JOIN services s ON o.service_id = s.id 
        ORDER BY o.created_at DESC
    ");
    $data = $stmt->fetchAll();

    if ($type === 'excel') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Laporan_Order_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Kode Order', 'Pelanggan', 'Layanan', 'Deadline', 'Total Harga', 'Status', 'Tanggal Order']);
        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'], $row['order_code'], $row['customer_name'], $row['service_name'], 
                $row['deadline'], $row['price'], $row['status'], $row['created_at']
            ]);
        }
        fclose($output);
        exit;
    } elseif ($type === 'pdf') {
        echo '
        <html>
        <head>
            <title>Laporan Transaksi Order - D\'AKSARA TECH</title>
            <style>
                body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #D97706; padding-bottom: 15px; margin-bottom: 30px; }
                .header h1 { margin: 0; color: #0F172A; font-size: 28px; letter-spacing: 1px; }
                .header p { margin: 5px 0 0; color: #64748B; font-size: 14px; }
                .report-title { text-align: center; margin-bottom: 25px; }
                .report-title h2 { margin: 0; color: #1E293B; font-size: 20px; text-transform: uppercase; }
                .report-title p { margin: 5px 0 0; color: #64748B; font-size: 13px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px; }
                th, td { border: 1px solid #E2E8F0; padding: 10px 12px; text-align: left; }
                th { background-color: #F8FAFC; color: #0F172A; font-weight: bold; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
                tr:nth-child(even) { background-color: #F8FAFC; }
                .badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
                .bg-success { background: #d1fae5; color: #065f46; }
                .bg-warning { background: #fef3c7; color: #92400e; }
                .bg-info { background: #dbeafe; color: #1e40af; }
                .footer { text-align: right; margin-top: 50px; font-size: 14px; color: #333; }
                .footer .sign-line { margin-top: 60px; border-bottom: 1px solid #333; display: inline-block; width: 200px; }
                @media print { body { padding: 0; } .header, th, .badge { -webkit-print-color-adjust: exact; } }
            </style>
        </head>
        <body onload="window.print()">
            <div class="header">
                <h1>D\'AKSARA TECH</h1>
                <p>Platform Solusi Digital & Profesional</p>
            </div>
            
            <div class="report-title">
                <h2>Laporan Rekapitulasi Order</h2>
                <p>Dicetak pada: ' . date('d F Y, H:i') . '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="15%">Kode Order</th>
                        <th width="20%">Nama Pelanggan</th>
                        <th width="25%">Layanan</th>
                        <th width="10%">Deadline</th>
                        <th width="15%" style="text-align:right;">Total Nilai</th>
                        <th width="15%">Status</th>
                    </tr>
                </thead>
                <tbody>';
        
        $total_revenue = 0;
        foreach($data as $row) {
            $badge_class = 'bg-warning';
            if($row['status'] == 'Selesai') { $badge_class = 'bg-success'; $total_revenue += $row['price']; }
            elseif($row['status'] == 'Diproses') { $badge_class = 'bg-info'; $total_revenue += $row['price']; }
            
            echo '<tr>
                    <td style="font-family: monospace; font-weight: bold;">'.$row['order_code'].'</td>
                    <td style="font-weight: bold;">'.htmlspecialchars($row['customer_name']).'</td>
                    <td>'.htmlspecialchars($row['service_name']).'</td>
                    <td>'.date('d/m/Y', strtotime($row['deadline'])).'</td>
                    <td style="text-align: right;">Rp '.number_format($row['price'],0,',','.').'</td>
                    <td style="text-align: center;"><span class="badge '.$badge_class.'">'.$row['status'].'</span></td>
                  </tr>';
        }
        
        echo '  </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align: right; background-color: #fff;">Total Estimasi Pendapatan (Order Aktif & Selesai):</th>
                        <th colspan="2" style="text-align: left; font-size: 16px; background-color: #fef3c7; color: #92400e;">Rp '.number_format($total_revenue,0,',','.').'</th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="footer">
                <p>Admin D\'AKSARA TECH,</p>
                <div class="sign-line"></div>
                <p style="margin-top: 5px; font-weight: bold;">Muhammad Rifqi Thoohaa Anas</p>
            </div>
        </body>
        </html>';
        exit;
    }
}

// Handle Update Status & Upload Result
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $customer_id = $_POST['customer_id'];
    $order_code = $_POST['order_code'];
    
    $result_file_path = null;

    if (isset($_FILES['result_file']) && $_FILES['result_file']['error'] == 0) {
        $target_dir = "uploads/results/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES["result_file"]["name"], PATHINFO_EXTENSION);
        $new_filename = $order_code . "_RESULT." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["result_file"]["tmp_name"], $target_file)) {
            $result_file_path = $target_file;
        }
    }

    if ($result_file_path) {
        $stmt = $conn->prepare("UPDATE orders SET status = ?, result_file = ? WHERE id = ?");
        $stmt->execute([$status, $result_file_path, $order_id]);
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
    }

    // Insert Notification
    $msg = "Status order Anda ($order_code) diperbarui menjadi: $status.";
    $type = 'info';
    
    if($status == 'Selesai') {
        $msg .= " Silakan download hasil pengerjaannya.";
        $type = 'success';
    }
    
    $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $stmt_notif->execute([$customer_id, $msg, $type]);

    set_flash_message('success', 'Status order berhasil diperbarui.');
    redirect('index.php?page=admin_orders');
}

$stmt = $conn->query("
    SELECT o.*, u.name as customer_name, s.name as service_name 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    JOIN services s ON o.service_id = s.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

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
            <li><a href="index.php?page=admin_dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="index.php?page=admin_services"><i class="fas fa-list-alt"></i> Kelola Layanan</a></li>
            <?php 
                $new_order_stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Menunggu'");
                $new_order_count = $new_order_stmt->fetchColumn();
                
                $msg_stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
                $msg_stmt->execute([$_SESSION['user_id']]);
                $unread_msg_count = $msg_stmt->fetchColumn();
            ?>
            <li>
                <a href="index.php?page=admin_orders" class="active d-flex justify-content-between align-items-center">
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
        <!-- Topbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent mb-4">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-primary d-md-none"><i class="fas fa-bars"></i></button>
                <div class="d-flex align-items-center ms-auto">
                    <div class="theme-switch me-3" id="theme-toggle"><i class="fas fa-moon" id="theme-icon"></i></div>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0"></i>Kelola Order</h3>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary rounded-pill shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-file-export me-1"></i> Export Data
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="index.php?page=admin_orders&export=excel" target="_blank"><i class="fas fa-file-csv text-success me-2"></i>Export CSV/Excel</a></li>
                        <li><a class="dropdown-item" href="index.php?page=admin_orders&export=pdf" target="_blank"><i class="fas fa-file-pdf text-danger me-2"></i>Print PDF</a></li>
                    </ul>
                </div>
            </div>

            <?php if($new_order_count > 0): ?>
            <div class="alert shadow-sm border-0 border-start border-4 border-warning mb-4 d-flex align-items-center" style="background-color: var(--card-bg);">
                <i class="fas fa-bell fa-2x me-3 text-warning"></i>
                <div>
                    <h6 class="fw-bold mb-1" style="color: var(--text-color);">Pesanan Baru Menunggu!</h6>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">Terdapat <strong><?= $new_order_count ?></strong> pesanan dengan status 'Menunggu' yang perlu segera diverifikasi dan diproses.</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm p-4 rounded-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Order</th>
                                <th>Pelanggan</th>
                                <th>Layanan</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Aksi / Kelola</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $o): ?>
                                <tr>
                                    <td><span class="badge bg-light text-secondary border"><?= $o['order_code'] ?></span></td>
                                    <td><span class="fw-semibold"><?= htmlspecialchars($o['customer_name']) ?></span></td>
                                    <td><?= htmlspecialchars($o['service_name']) ?></td>
                                    <td><?= date('d M Y', strtotime($o['deadline'])) ?></td>
                                    <td>
                                        <?php if($o['status'] == 'Menunggu'): ?>
                                            <span class="badge bg-warning text-color"><i class="fas fa-clock fs-6 pe-1"></i> Menunggu</span>
                                        <?php elseif($o['status'] == 'Diproses'): ?>
                                            <span class="badge bg-info text-color"><i class="fas fa-spinner fa-spin fs-6 pe-1"></i> Diproses</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle fs-6 pe-1"></i> Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#manageModal<?= $o['id'] ?>">Kelola</button>
                                        <?php if($o['payment_proof']): ?>
                                            <a href="<?= $o['payment_proof'] ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-circle shadow-sm" title="Lihat Bukti Bayar"><i class="fas fa-receipt"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>


                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php foreach($orders as $o): ?>
<!-- Manage Modal -->
<div class="modal fade" id="manageModal<?= $o['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="index.php?page=admin_orders" method="POST" enctype="multipart/form-data">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-color"><i class="fas fa-tasks me-2"></i>Kelola Order <?= $o['order_code'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <input type="hidden" name="customer_id" value="<?= $o['customer_id'] ?>">
                    <input type="hidden" name="order_code" value="<?= $o['order_code'] ?>">
                    
                    <div class="mb-3 bg-light p-3 rounded text-muted small">
                        <strong>Deskripsi:</strong><br>
                        <?= nl2br(htmlspecialchars($o['description'])) ?>
                    </div>

                    <?php if($o['file_path']): ?>
                        <div class="mb-3">
                            <a href="<?= $o['file_path'] ?>" class="btn btn-sm btn-outline-info" download><i class="fas fa-download me-1"></i> Download Materi Pelanggan</a>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Update Status</label>
                        <select class="form-select" name="status" id="statusSelect<?= $o['id'] ?>" onchange="toggleUploadBox(<?= $o['id'] ?>)">
                            <option value="Menunggu" <?= $o['status'] == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="Diproses" <?= $o['status'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="Selesai" <?= $o['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </div>

                    <div class="mb-3" id="uploadBox<?= $o['id'] ?>" style="<?= $o['status'] == 'Selesai' ? 'display:block;' : 'display:none;' ?>">
                        <label class="form-label fw-semibold">Upload Hasil Pekerjaan <small class="text-muted">(Jika Selesai)</small></label>
                        <input type="file" class="form-control" name="result_file">
                        <?php if($o['result_file']): ?>
                            <small class="text-success d-block mt-2"><i class="fas fa-check-circle"></i> File hasil sudah terupload.</small>
                        <?php endif; ?>
                    </div>

                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan & Notifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
    function toggleUploadBox(id) {
        var status = document.getElementById('statusSelect' + id).value;
        var box = document.getElementById('uploadBox' + id);
        if(status === 'Selesai') {
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }
</script>

<?php require 'views/layouts/footer.php'; ?>
