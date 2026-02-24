<?php
require_once 'config/database.php';
$stmt = $conn->query("SELECT * FROM users");
$users = $stmt->fetchAll();
foreach ($users as $user) {
    if(password_verify('password', $user['password'])) {
        echo "User {$user['email']} valid password.\n";
    } else {
        echo "User {$user['email']} invalid password.\n";
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $conn->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user['id']]);
        echo "Updated password for {$user['email']}.\n";
    }
}
?>
