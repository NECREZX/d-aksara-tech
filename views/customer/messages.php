<?php
require_login();
if(is_admin()) redirect('index.php?page=admin_dashboard');

// Find admin user for generic communication
$stmt_admin = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$admin = $stmt_admin->fetch();
$admin_id = $admin['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $file_path = null;
    
    // Handle Attachment Upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $target_dir = "uploads/messages/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES["attachment"]["name"], PATHINFO_EXTENSION);
        $new_filename = "CHAT_" . time() . "_" . uniqid() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Allowed file types (Images, PDF)
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'zip'];
        if(in_array(strtolower($file_extension), $allowed)) {
            if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
                $file_path = $target_file;
            }
        } else {
            set_flash_message('danger', 'Format file lampiran tidak diizinkan.');
            redirect('index.php?page=customer_messages');
        }
    }
    
    if($message !== '' || $file_path !== null) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, attachment_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $admin_id, $message, $file_path]);
    }
    
    // Redirect to prevent form resubmission
    redirect('index.php?page=customer_messages');
}

// Mark admin messages as read
$stmt_read = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?");
$stmt_read->execute([$_SESSION['user_id'], $admin_id]);

// Fetch Chat History
$stmt_chat = $conn->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) 
       OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY created_at ASC
");
$stmt_chat->execute([$_SESSION['user_id'], $admin_id, $admin_id, $_SESSION['user_id']]);
$messages = $stmt_chat->fetchAll();

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
            // Notifications badge
            $notif_stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $notif_stmt->execute([$_SESSION['user_id']]);
            $unread_count = $notif_stmt->fetchColumn();
            
            // Messages badge
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
                <a href="index.php?page=customer_messages" class="active d-flex justify-content-between align-items-center">
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
    <div id="content" class="d-flex flex-column" style="height: 100vh; overflow: hidden;">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent mb-4 flex-shrink-0">
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

        <div class="container-fluid d-flex flex-column flex-grow-1 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-shrink-0">
                <h3 class="fw-bold m-0" style="color: var(--text-color);">Ruang Chat</h3>
            </div>

            <!-- Chat Container -->
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1 d-flex flex-column overflow-hidden mb-3">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 45px; height: 45px;">
                        <i class="fas fa-user-tie fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">D'AKSARA TECH</h6>
                        <small class="text-muted"><i class="fas fa-circle text-success" style="font-size: 8px;"> </i> Admin</small>
                    </div>
                </div>
                
                <!-- Chat History Area -->
                <div class="card-body overflow-auto p-4" id="chatContainer" style="background-color: var(--card-bg);">
                    <?php if(empty($messages)): ?>
                        <div class="text-center text-muted my-5">
                            <i class="fas fa-comments fa-3x mb-3 opacity-25"></i>
                            <p>Belum ada pesan. Silakan hubungi admin untuk konsultasi.</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach($messages as $msg): 
                        $is_me = ($msg['sender_id'] == $_SESSION['user_id']);
                    ?>
                        <div class="d-flex mb-3 <?= $is_me ? 'justify-content-end' : 'justify-content-start' ?>">
                            <div class="chat-bubble p-3 rounded-4 shadow-sm" style="max-width: 75%; <?= $is_me ? 'background-color: var(--secondary-color); color: white; border-bottom-right-radius: 0 !important;' : 'background-color: ' . (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark' ? '#334155' : '#f8f9fa') . '; color: var(--text-color); border-bottom-left-radius: 0 !important;' ?>">
                                
                                <?php if($msg['attachment_path']): 
                                    $ext = strtolower(pathinfo($msg['attachment_path'], PATHINFO_EXTENSION));
                                    if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])):
                                ?>
                                    <a href="<?= $msg['attachment_path'] ?>" target="_blank">
                                        <img src="<?= $msg['attachment_path'] ?>" class="img-fluid rounded border border-light mb-2" style="max-height: 200px; <?= $is_me ? 'opacity: 0.9;' : '' ?>" alt="Attachment">
                                    </a>
                                <?php else: ?>
                                    <div class="border rounded p-2 mb-2 bg-white bg-opacity-25 d-flex align-items-center gap-2">
                                        <i class="fas fa-file-alt fs-4"></i>
                                        <a href="<?= $msg['attachment_path'] ?>" target="_blank" class="<?= $is_me ? 'text-white' : 'text-primary' ?> text-decoration-none small">Lihat Lampiran File</a>
                                    </div>
                                <?php endif; endif; ?>
                                
                                <div class="message-text" style="white-space: pre-wrap; word-break: break-word; font-size: 0.95rem;"><?= htmlspecialchars($msg['message']) ?></div>
                                
                                <div class="text-end mt-1 <?= $is_me ? 'text-light text-opacity-75' : 'text-muted' ?>" style="font-size: 0.7rem;">
                                    <?= date('H:i', strtotime($msg['created_at'])) ?>
                                    <?php if($is_me): ?>
                                        <i class="fas fa-check-double ms-1 <?= $msg['is_read'] ? 'text-info' : '' ?>"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Chat Input Area -->
                <div class="card-footer bg-white border-top p-3">
                    <form action="index.php?page=customer_messages" method="POST" enctype="multipart/form-data" id="chatForm">
                        <div class="input-group">
                            <label class="input-group-text bg-transparent border-end-0 cursor-pointer text-muted hover-lift" for="attachmentInput" title="Lampirkan File/Gambar">
                                <i class="fas fa-paperclip px-2"></i>
                            </label>
                            <input type="file" class="d-none" id="attachmentInput" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.zip,.docx">
                            
                            <textarea class="form-control border-start-0 border-end-0 shadow-none m-0" name="message" id="messageInput" rows="1" placeholder="Ketik pesan ..." style="resize: none; padding-top: 10px;"></textarea>
                            
                            <button class="btn btn-primary px-4 fw-bold" type="submit" id="sendBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="attachmentPreview" class="small text-primary mt-2 fw-medium" style="display: none;">
                            <i class="fas fa-file-image me-1"></i> <span id="fileName"></span> 
                            <i class="fas fa-times text-danger ms-2 cursor-pointer" onclick="clearAttachment()" title="Batal Lampirkan"></i>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
/* Scrollbar styling for chat area */
#chatContainer::-webkit-scrollbar { width: 6px; }
#chatContainer::-webkit-scrollbar-track { background: transparent; }
#chatContainer::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.1); border-radius: 10px; }
[data-theme="dark"] #chatContainer::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.1); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll chat to bottom initially
    const chatContainer = document.getElementById('chatContainer');
    chatContainer.scrollTop = chatContainer.scrollHeight;

    // Handle Attachment Preview
    const attachmentInput = document.getElementById('attachmentInput');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const fileName = document.getElementById('fileName');

    attachmentInput.addEventListener('change', function() {
        if(this.files && this.files[0]) {
            fileName.innerText = this.files[0].name;
            attachmentPreview.style.display = 'block';
        }
    });

    // Auto-resize textarea
    const messageInput = document.getElementById('messageInput');
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto'; // Reset height
        let newHeight = Math.min(this.scrollHeight, 120); // Max 120px
        this.style.height = (this.value ? newHeight : 38) + 'px';
    });
    
    // Submit on Enter (Shift+Enter for new line)
    messageInput.addEventListener('keydown', function(e) {
        if(e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if(this.value.trim() !== '' || attachmentInput.files.length > 0) {
                document.getElementById('chatForm').submit();
            }
        }
    });
});

function clearAttachment() {
    document.getElementById('attachmentInput').value = '';
    document.getElementById('attachmentPreview').style.display = 'none';
}
</script>

<?php require 'views/layouts/footer.php'; ?>
