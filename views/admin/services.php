<?php
require_admin();

// Handle Export
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $stmt = $conn->query("SELECT * FROM services ORDER BY id DESC");
    $data = $stmt->fetchAll();

    if ($type === 'excel') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Laporan_Layanan_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Nama Layanan', 'Deskripsi', 'Harga Dasar (Rp)', 'Tanggal Dibuat']);
        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'], 
                $row['name'], 
                $row['description'], 
                $row['base_price'], 
                $row['created_at']
            ]);
        }
        fclose($output);
        exit;
    } elseif ($type === 'pdf') {
        // Quick HTML to PDF using print dialog or basic structure for demonstration if TCPDF absent
        // We will output a simple HTML table and trigger print to PDF in browser as a fallback
        // Since no library is strictly requested to be installed via composer right now.
        echo '
        <html>
        <head>
            <title>Laporan Layanan - D\'AKSARA TECH</title>
            <style>
                body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #D97706; padding-bottom: 15px; margin-bottom: 30px; }
                .header h1 { margin: 0; color: #0F172A; font-size: 28px; letter-spacing: 1px; }
                .header p { margin: 5px 0 0; color: #64748B; font-size: 14px; }
                .report-title { text-align: center; margin-bottom: 25px; }
                .report-title h2 { margin: 0; color: #1E293B; font-size: 20px; text-transform: uppercase; }
                .report-title p { margin: 5px 0 0; color: #64748B; font-size: 13px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px; }
                th, td { border: 1px solid #E2E8F0; padding: 12px 15px; text-align: left; }
                th { background-color: #F8FAFC; color: #0F172A; font-weight: bold; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
                tr:nth-child(even) { background-color: #F8FAFC; }
                .footer { text-align: right; margin-top: 50px; font-size: 14px; color: #333; }
                .footer .sign-line { margin-top: 60px; border-bottom: 1px solid #333; display: inline-block; width: 200px; }
                @media print { body { padding: 0; } .header { -webkit-print-color-adjust: exact; } th { -webkit-print-color-adjust: exact; } }
            </style>
        </head>
        <body onload="window.print()">
            <div class="header">
                <h1>D\'AKSARA TECH</h1>
                <p>Platform Solusi Digital & Profesional</p>
            </div>
            
            <div class="report-title">
                <h2>Laporan Master Data Layanan</h2>
                <p>Dicetak pada: ' . date('d F Y, H:i') . '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">Nama Layanan</th>
                        <th width="50%">Deskripsi Layanan</th>
                        <th width="20%">Harga Dasar</th>
                    </tr>
                </thead>
                <tbody>';
        
        $no = 1;
        foreach($data as $row) {
            echo '<tr>
                    <td style="text-align: center;">'.$no++.'</td>
                    <td style="font-weight: bold;">'.htmlspecialchars($row['name']).'</td>
                    <td>'.htmlspecialchars($row['description']).'</td>
                    <td style="text-align: right;">Rp '.number_format($row['base_price'],0,',','.').'</td>
                  </tr>';
        }
        
        echo '  </tbody>
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

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO services (name, description, base_price) VALUES (?, ?, ?)");
        if ($stmt->execute([$_POST['name'], $_POST['description'], $_POST['base_price']])) {
            set_flash_message('success', 'Layanan berhasil ditambahkan.');
        }
    } elseif ($action === 'edit') {
        $stmt = $conn->prepare("UPDATE services SET name=?, description=?, base_price=? WHERE id=?");
        if ($stmt->execute([$_POST['name'], $_POST['description'], $_POST['base_price'], $_POST['id']])) {
            set_flash_message('success', 'Layanan berhasil diperbarui.');
        }
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM services WHERE id=?");
        if ($stmt->execute([$_POST['id']])) {
            set_flash_message('success', 'Layanan berhasil dihapus.');
        }
    }
    redirect('index.php?page=admin_services');
}

$stmt = $conn->query("SELECT * FROM services ORDER BY id DESC");
$services = $stmt->fetchAll();

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
            <li><a href="index.php?page=admin_services" class="active"><i class="fas fa-list-alt"></i> Kelola Layanan</a></li>
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
        <!-- Topbar omitted for brevity, same as dashboard -->
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
                <h3 class="fw-bold m-0">Kelola Layanan</h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary rounded-pill shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus me-1"></i> Tambah Baru
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary rounded-pill shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="index.php?page=admin_services&export=excel" target="_blank"><i class="fas fa-file-csv text-success me-2"></i>Export CSV/Excel</a></li>
                            <li><a class="dropdown-item" href="index.php?page=admin_services&export=pdf" target="_blank"><i class="fas fa-file-pdf text-danger me-2"></i>Print PDF</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm p-4 rounded-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Layanan</th>
                                <th>Harga Dasar</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($services as $i => $s): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-semibold text-color"><?= htmlspecialchars($s['name']) ?></td>
                                    <td class="fw-bold" style="color: var(--secondary-color);">Rp <?= number_format($s['base_price'], 0, ',', '.') ?></td>
                                    <td><span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= htmlspecialchars($s['description']) ?>"><?= htmlspecialchars($s['description']) ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info text-white rounded-circle shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $s['id'] ?>"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $s['id'] ?>"><i class="fas fa-trash"></i></button>
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

<?php foreach($services as $s): ?>
<!-- Edit Modal -->
<div class="modal fade" id="editModal<?= $s['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="index.php?page=admin_services" method="POST">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-edit me-2"></i>Edit Layanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Layanan</label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($s['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Harga Dasar (Rp)</label>
                        <input type="number" class="form-control" name="base_price" value="<?= $s['base_price'] ?>" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3" required><?= htmlspecialchars($s['description']) ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal<?= $s['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow text-center p-3">
            <div class="modal-body">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3 d-block"></i>
                <h5 class="fw-bold">Hapus Layanan?</h5>
                <p class="text-muted text-sm pb-2">Tindakan ini tidak dapat dibatalkan. Pastikan tidak ada order yang terhubung.</p>
                <form action="index.php?page=admin_services" method="POST" class="d-flex justify-content-center gap-2">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-3">Ya, Hapus!</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="index.php?page=admin_services" method="POST">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-color"><i class="fas fa-plus-circle me-2"></i>Tambah Layanan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Layanan</label>
                        <input type="text" class="form-control" name="name" required placeholder="Contoh: Pembuatan Tugas Akhir">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Harga Dasar (Rp)</label>
                        <input type="number" class="form-control" name="base_price" required min="0" placeholder="50000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3" required placeholder="Deskripsikan layanan ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'views/layouts/footer.php'; ?>
