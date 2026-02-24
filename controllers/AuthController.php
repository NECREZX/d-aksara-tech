<?php
require_once 'config/database.php';

$action = isset($_GET['page']) ? $_GET['page'] : '';

if ($action === 'do_login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            set_flash_message('success', 'Selamat datang kembali, ' . $user['name']);
            
            if ($user['role'] === 'admin') {
                redirect('index.php?page=admin_dashboard');
            } else {
                redirect('index.php?page=customer_dashboard');
            }
        } else {
            set_flash_message('danger', 'Email atau password salah!');
            redirect('index.php?page=login');
        }
    }
} elseif ($action === 'do_register') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            set_flash_message('danger', 'Password dan Konfirmasi Password tidak cocok!');
            redirect('index.php?page=register');
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            set_flash_message('danger', 'Email sudah terdaftar!');
            redirect('index.php?page=register');
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'customer')");
        if ($stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashed_password
        ])) {
            $user_id = $conn->lastInsertId();
            
            // Add welcome notification
            $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
            $stmt_notif->execute([$user_id, "Selamat datang di D'AKSARA TECH! Akun Anda berhasil dibuat.", "success"]);
            
            set_flash_message('success', 'Registrasi berhasil! Silakan login.');
            redirect('index.php?page=login');
        } else {
            set_flash_message('danger', 'Terjadi kesalahan sistem.');
            redirect('index.php?page=register');
        }
    }
} elseif ($action === 'logout') {
    session_destroy();
    session_start();
    set_flash_message('success', 'Anda telah berhasil logout.');
    redirect('index.php?page=login');
} else {
    redirect('index.php');
}
?>
