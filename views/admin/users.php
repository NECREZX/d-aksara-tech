<?php
require_admin();

// Handle Export
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
    $data = $stmt->fetchAll();

    if ($type === 'excel') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Laporan_User_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Nama', 'Email', 'Role', 'Tanggal Daftar']);
        foreach ($data as $row) {
            fputcsv($output, [$row['id'], $row['name'], $row['email'], $row['role'], $row['created_at']]);
        }
        fclose($output);
        exit;
    } elseif ($type === 'pdf') {
        echo '
        <html>
        <head>
            <title>Laporan Pengguna - D\'AKSARA TECH</title>
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
                .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
                .bg-admin { background: #fee2e2; color: #991b1b; }
                .bg-cust { background: #fef3c7; color: #92400e; }
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
                <h2>Laporan Data Pengguna Sistem</h2>
                <p>Dicetak pada: ' . date('d F Y, H:i') . '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="30%">Nama Lengkap</th>
                        <th width="35%">Alamat Email</th>
                        <th width="15%">Hak Akses</th>
                        <th width="15%">Tanggal Daftar</th>
                    </tr>
                </thead>
                <tbody>';
        
        $no = 1;
        foreach($data as $row) {
            $role_badge = $row['role'] == 'admin' ? '<span class="badge bg-admin">Admin</span>' : '<span class="badge bg-cust">Customer</span>';
            echo '<tr>
                    <td style="text-align: center;">'.$no++.'</td>
                    <td style="font-weight: bold;">'.htmlspecialchars($row['name']).'</td>
                    <td>'.htmlspecialchars($row['email']).'</td>
                    <td style="text-align: center;">'.$role_badge.'</td>
                    <td>'.date('d/m/Y', strtotime($row['created_at'])).'</td>
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
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if($stmt->fetch()) {
             set_flash_message('danger', 'Email sudah terdaftar!');
        } else {
            $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$_POST['name'], $_POST['email'], $hashed, $_POST['role']])) {
                set_flash_message('success', 'Pengguna berhasil ditambahkan.');
            }
        }
    } elseif ($action === 'edit') {
        if(!empty($_POST['password'])) {
            $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=?, role=? WHERE id=?");
            $stmt->execute([$_POST['name'], $_POST['email'], $hashed, $_POST['role'], $_POST['id']]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
            $stmt->execute([$_POST['name'], $_POST['email'], $_POST['role'], $_POST['id']]);
        }
        set_flash_message('success', 'Pengguna berhasil diperbarui.');
    } elseif ($action === 'delete') {
        if($_POST['id'] == $_SESSION['user_id']) {
            set_flash_message('danger', 'Tidak dapat menghapus akun Anda sendiri.');
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            if ($stmt->execute([$_POST['id']])) {
                set_flash_message('success', 'Pengguna berhasil dihapus.');
            }
        }
    }
    redirect('index.php?page=admin_users');
}

$stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

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
            <li><a href="index.php?page=admin_users" class="active"><i class="fas fa-users"></i> Kelola Pengguna</a></li>
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
                <h3 class="fw-bold m-0">Kelola Pengguna</h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary rounded-pill shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus me-1"></i> Tambah Pengguna
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary rounded-pill shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="index.php?page=admin_users&export=excel" target="_blank"><i class="fas fa-file-csv text-success me-2"></i>Export CSV/Excel</a></li>
                            <li><a class="dropdown-item" href="index.php?page=admin_users&export=pdf" target="_blank"><i class="fas fa-file-pdf text-danger me-2"></i>Print PDF</a></li>
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
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $i => $u): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-semibold text-color">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['name']) ?>&background=random" class="rounded-circle me-2" width="28">
                                        <?= htmlspecialchars($u['name']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <?php if($u['role'] == 'admin'): ?>
                                            <span class="badge bg-danger rounded-pill px-3">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill px-3">Customer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info text-white rounded-circle shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $u['id'] ?>"><i class="fas fa-edit"></i></button>
                                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                                            <button class="btn btn-sm btn-danger rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $u['id'] ?>"><i class="fas fa-trash"></i></button>
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

<?php foreach($users as $u): ?>
<!-- Edit Modal -->
<div class="modal fade" id="editModal<?= $u['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="index.php?page=admin_users" method="POST">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-user-edit me-2"></i>Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($u['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($u['email']) ?>" required <?= $u['id'] == $_SESSION['user_id'] ? 'readonly' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                        <input type="password" class="form-control" name="password" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <select class="form-select" name="role" required <?= $u['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                            <option value="customer" <?= $u['role'] == 'customer' ? 'selected' : '' ?>>Customer</option>
                            <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <?php if($u['id'] == $_SESSION['user_id']): ?>
                            <input type="hidden" name="role" value="<?= $u['role'] ?>">
                        <?php endif; ?>
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

<?php if($u['id'] != $_SESSION['user_id']): ?>
<!-- Delete Modal -->
<div class="modal fade" id="deleteModal<?= $u['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow text-center p-3">
            <div class="modal-body">
                <i class="fas fa-user-times fa-3x text-danger mb-3 d-block"></i>
                <h5 class="fw-bold">Hapus Akun?</h5>
                <p class="text-muted text-sm pb-2">User <strong><?= htmlspecialchars($u['name']) ?></strong> akan dihapus permanen.</p>
                <form action="index.php?page=admin_users" method="POST" class="d-flex justify-content-center gap-2">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-3">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="index.php?page=admin_users" method="POST">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-color"><i class="fas fa-user-plus me-2"></i>Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" required placeholder="John Doe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" name="email" required placeholder="email@contoh.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" name="password" required minlength="6" placeholder="******">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                        </select>
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
