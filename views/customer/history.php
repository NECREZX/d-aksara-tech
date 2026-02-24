<?php
require_login();
if(is_admin()) redirect('index.php?page=admin_dashboard');

// Handle Export Receipt (Cetak Bukti)
if (isset($_GET['export_receipt'])) {
    $receipt_id = $_GET['export_receipt'];
    
    // Fetch specific order data
    $stmt_receipt = $conn->prepare("
        SELECT o.*, s.name as service_name, s.base_price, u.name as customer_name 
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        JOIN users u ON o.customer_id = u.id
        WHERE o.id = ? AND o.customer_id = ?
    ");
    $stmt_receipt->execute([$receipt_id, $_SESSION['user_id']]);
    $receipt_data = $stmt_receipt->fetch();
    
    if ($receipt_data && $receipt_data['status'] == 'Selesai') {
        echo '
        <html>
        <head>
            <title>Bukti Pesanan - D\'AKSARA TECH</title>
            <style>
                body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 20px; background-color: #f8f9fa; }
                .receipt-container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-top: 5px solid #D97706; }
                .header { text-align: center; border-bottom: 2px dashed #ccc; padding-bottom: 20px; margin-bottom: 20px; }
                .header h1 { margin: 0; color: #0F172A; font-size: 24px; letter-spacing: 1px; }
                .header p { margin: 5px 0 0; color: #64748B; font-size: 13px; }
                .receipt-title { text-align: center; margin-bottom: 25px; }
                .receipt-title h2 { margin: 0; color: #1E293B; font-size: 18px; text-transform: uppercase; letter-spacing: 2px; }
                .info-table { width: 100%; margin-bottom: 20px; font-size: 14px; }
                .info-table td { padding: 5px 0; }
                .info-table .label { font-weight: bold; color: #64748B; width: 150px; }
                .item-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px; }
                .item-table th, .item-table td { border-bottom: 1px solid #E2E8F0; padding: 12px 5px; text-align: left; }
                .item-table th { color: #0F172A; font-weight: bold; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
                .total-row td { font-weight: bold; font-size: 16px; color: #0F172A; border-top: 2px solid #0F172A; }
                .badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; background: #d1fae5; color: #065f46; border: 1px solid #10B981; }
                .footer { display: flex; justify-content: space-between; margin-top: 40px; font-size: 13px; color: #333; }
                .footer-left { text-align: left; color: #64748B; font-size: 12px; font-style: italic; }
                .footer-right { text-align: center; }
                .footer-right .sign-line { margin-top: 50px; border-bottom: 1px solid #333; display: block; width: 180px; }
                @media print { body { background: none; padding: 0; } .receipt-container { box-shadow: none; border: none; padding: 10px; max-width: 100%; border-top: 5px solid #0F172A; } .badge { -webkit-print-color-adjust: exact; } }
            </style>
        </head>
        <body onload="window.print()">
            <div class="receipt-container">
                <div class="header">
                    <h1>D\'AKSARA TECH</h1>
                    <p>Platform Solusi Digital & Profesional</p>
                </div>
                
                <div class="receipt-title">
                    <h2>Bukti Pesanan (Invoice)</h2>
                </div>

                <table class="info-table">
                    <tr>
                        <td class="label">No. Tagihan</td>
                        <td style="font-family: monospace; font-weight: bold; font-size: 15px;">: '.$receipt_data['order_code'].'</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Diterbitkan</td>
                        <td>: ' . date('d F Y, H:i') . '</td>
                    </tr>
                    <tr>
                        <td class="label">Nama Pemesan</td>
                        <td style="font-weight: bold; color: #0F172A;">: '.htmlspecialchars($receipt_data['customer_name']).'</td>
                    </tr>
                    <tr>
                        <td class="label">Tenggat Waktu</td>
                        <td style="color: #EF4444; font-weight: 500;">: '.date('d F Y', strtotime($receipt_data['deadline'])).'</td>
                    </tr>
                    <tr>
                        <td class="label">Status</td>
                        <td>: <span class="badge">'.$receipt_data['status'].'</span></td>
                    </tr>
                </table>

                <table class="item-table">
                    <thead>
                        <tr>
                            <th width="70%">Deskripsi Item Tagihan</th>
                            <th width="30%" style="text-align: right;">Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>'.htmlspecialchars($receipt_data['service_name']).' (Tarif Dasar)</strong><br>
                                <span style="font-size: 12px; color: #64748B;">Catatan: '.htmlspecialchars($receipt_data['description']).'</span>
                            </td>
                            <td style="text-align: right;">Rp '.number_format(($receipt_data['base_price'] ?? 0),0,',','.').'</td>
                        </tr>';
                        
                        $urgencyFee = max(0, ($receipt_data['price'] ?? 0) - ($receipt_data['base_price'] ?? 0));
                        if($urgencyFee > 0) {
                            $pct = round(($urgencyFee / max(1, $receipt_data['base_price'])) * 100);
                            $type_text = ($pct > 20) ? "Kilat (<3 hari)" : "Cepat (3-5 hari)";

                            echo '<tr>
                                    <td>
                                        <strong style="color: #D97706;">Biaya Tambahan / Prioritas Layanan</strong><br>
                                        <span style="font-size: 11px; color: #64748B;">Biaya Tambahan deadline lebih cepat (+'.$pct.'% '.$type_text.')</span>
                                    </td>
                                    <td style="text-align: right; color: #D97706;">+ Rp '.number_format($urgencyFee,0,',','.').'</td>
                                  </tr>';
                        }
                        
        echo '          <tr class="total-row">
                            <td style="text-align: right; padding-right: 15px;">TOTAL PEMBAYARAN:</td>
                            <td style="text-align: right; color: #0F172A; font-size: 18px;">Rp '.number_format($receipt_data['price'],0,',','.').'</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="footer">
                    <div class="footer-left">
                        <p>Catatan:<br>Bukti ini sah dicetak oleh sistem.<br>Terima kasih atas kepercayaan Anda.</p>
                    </div>
                    <div class="footer-right">
                        <p>Hormat Kami,</p>
                        <p style="margin: 0; color: #64748B; font-size: 11px;">Admin D\'AKSARA TECH</p>
                        <div class="sign-line"></div>
                        <p style="margin-top: 5px; font-weight: bold;">Muhammad Rifqi Thoohaa Anas</p>
                    </div>
                </div>
            </div>
        </body>
        </html>';
        exit;
    } else {
        set_flash_message('danger', 'Bukti pesanan tidak valid atau order belum selesai.');
        redirect('index.php?page=customer_history');
    }
}

// Handle Filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = "o.customer_id = ?";
$params = [$_SESSION['user_id']];

if ($status_filter) {
    $where_clause .= " AND o.status = ?";
    $params[] = $status_filter;
}

$query = "
    SELECT o.*, s.name as service_name, s.base_price 
    FROM orders o 
    JOIN services s ON o.service_id = s.id 
    WHERE $where_clause
    ORDER BY o.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

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
            <li><a href="index.php?page=customer_history" class="active"><i class="fas fa-history"></i> Riwayat Order</a></li>
            
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0" style="color: var(--text-color);">Riwayat Transaksi</h3>
                <div>
                    <!-- Filter Setup -->
                    <form action="index.php" method="GET" class="d-inline-flex gap-2">
                        <input type="hidden" name="page" value="customer_history">
                        <select name="status" class="form-select form-select-sm shadow-sm border-0" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="Menunggu" <?= $status_filter == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="Diproses" <?= $status_filter == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="Selesai" <?= $status_filter == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm p-4 rounded-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Kode Order</th>
                                <th>Layanan</th>
                                <th>Tanggal Pesan</th>
                                <th>Deadline</th>
                                <th>Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $index => $o): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $o['order_code'] ?></span></td>
                                    <td><?= htmlspecialchars($o['service_name']) ?></td>
                                    <td><?= date('d M Y H:i', strtotime($o['created_at'])) ?></td>
                                    <td><span class="text-danger fw-semibold"><?= date('d M Y', strtotime($o['deadline'])) ?></span></td>
                                    <td class="fw-bold" style="color: var(--secondary-color);">Rp <?= number_format($o['price'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if($o['status'] == 'Menunggu'): ?>
                                            <span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-clock fs-6 pe-1"></i> Menunggu</span>
                                        <?php elseif($o['status'] == 'Diproses'): ?>
                                            <span class="badge bg-info text-dark rounded-pill"><i class="fas fa-spinner fa-spin fs-6 pe-1"></i> Diproses</span>
                                        <?php else: ?>
                                            <span class="badge bg-success rounded-pill"><i class="fas fa-check-circle fs-6 pe-1"></i> Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#detailModal<?= $o['id'] ?>" data-bs-toggle="tooltip" title="Lihat Detail Pesanan & Tagihan">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="index.php?page=customer_payment&id=<?= $o['id'] ?>" class="btn btn-sm <?= empty($o['payment_proof']) ? 'btn-danger' : 'btn-success' ?>" data-bs-toggle="tooltip" title="<?= empty($o['payment_proof']) ? 'Upload Pembayaran' : 'Lihat Pembayaran' ?>">
                                                    <i class="fas <?= empty($o['payment_proof']) ? 'fa-file-upload' : 'fa-receipt' ?>"></i>
                                                </a>
                                                <?php if($o['status'] == 'Selesai' && !empty($o['result_file'])): ?>
                                                    <a href="<?= $o['result_file'] ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Download Hasil Kerja" download>
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <a href="index.php?page=customer_history&export_receipt=<?= $o['id'] ?>" class="btn btn-sm text-white" style="background-color: var(--secondary-color);" target="_blank" data-bs-toggle="tooltip" title="Cetak Bukti (Invoice)">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                <?php elseif($o['status'] != 'Selesai'): ?>
                                                    <button class="btn btn-sm btn-secondary disabled" data-bs-toggle="tooltip" title="Hasil Belum Tersedia">
                                                        <i class="fas fa-hourglass-half"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal Detail Pesanan -->
                                <?php 
                                $baseP = $o['base_price'] ?? 0;
                                $finalP = $o['price'] ?? 0;
                                $urgentF = max(0, $finalP - $baseP);
                                ?>
                                <div class="modal fade" id="detailModal<?= $o['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <div class="modal-header border-bottom-0">
                                                <h5 class="modal-title fw-bold" style="color: var(--text-color);">Detail Order - <?= $o['order_code'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body px-4 pt-0">
                                                <div class="mb-3">
                                                    <label class="text-muted small fw-bold">Layanan Dipilih</label>
                                                    <div class="fw-medium text-dark"><?= htmlspecialchars($o['service_name']) ?></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small fw-bold">Penjelasan Khusus Pengguna</label>
                                                    <div class="p-3 mt-1 rounded text-dark" style="background-color: var(--hover-bg); font-size: 0.95rem; line-height: 1.5; border-left: 3px solid var(--accent-color);"><?= nl2br(htmlspecialchars($o['description'])) ?></div>
                                                </div>
                                                
                                                <h6 class="fw-bold mt-4 mb-3 border-bottom border-secondary border-opacity-10 pb-2">Rincian Tagihan Pembayaran</h6>
                                                <ul class="list-unstyled mb-0 text-dark">
                                                    <li class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">Harga Dasar Layanan</span>
                                                        <span class="fw-medium">Rp <?= number_format($baseP, 0, ',', '.') ?></span>
                                                    </li>
                                                    <?php if($urgentF > 0): 
                                                    $pct = round(($urgentF / max(1, $baseP)) * 100);
                                                    $type_text = ($pct > 20) ? "Kilat" : "Cepat";
                                                    ?>
                                                    <li class="d-flex justify-content-between align-items-center mb-2 text-warning fw-medium">
                                                        <span><i class="fas fa-bolt me-1"></i> Biaya Prioritas Deadline<small>(+<?= $pct ?>% <?= $type_text ?>)</small></span>
                                                        <span class="fw-bold">+ Rp <?= number_format($urgentF, 0, ',', '.') ?></span>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li class="d-flex justify-content-between mt-3 pt-3 border-top border-secondary border-opacity-25">
                                                        <span class="fw-bold text-dark">Total Yang Harus Dibayar</span>
                                                        <span class="fw-bold fs-5 text-primary">Rp <?= number_format($finalP, 0, ',', '.') ?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="button" class="btn border shadow-sm rounded-pill fw-medium px-4 w-100 mt-2" data-bs-dismiss="modal">Tutup Detail</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Modal -->
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>

<?php require 'views/layouts/footer.php'; ?>
