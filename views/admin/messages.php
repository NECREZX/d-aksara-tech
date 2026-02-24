<?php
require_login();
if(!is_admin()) redirect('index.php?page=customer_dashboard');

// Default target customer ID based on active chat
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

// Handle Sending Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']);
    $file_path = null;
    
    // Validate target user role (must not be admin sending to admin)
    $stmt_check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt_check->execute([$receiver_id]);
    $target_user = $stmt_check->fetch();
    if(!$target_user || $target_user['role'] === 'admin') {
        redirect('index.php?page=admin_messages');
    }

    // Handle Attachment Upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $target_dir = "uploads/messages/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES["attachment"]["name"], PATHINFO_EXTENSION);
        $new_filename = "CHAT_" . time() . "_" . uniqid() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Allowed file types
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'zip'];
        if(in_array(strtolower($file_extension), $allowed)) {
            if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
                $file_path = $target_file;
            }
        }
    }
    
    if($message !== '' || $file_path !== null) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, attachment_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $receiver_id, $message, $file_path]);
    }
    
    redirect('index.php?page=admin_messages&customer_id=' . $receiver_id);
}

// Mark messages from the active customer as read
if($customer_id > 0) {
    $stmt_read = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?");
    $stmt_read->execute([$_SESSION['user_id'], $customer_id]);
}

// Fetch Contact List (Customers who have chatted with the admin, or all customers)
$stmt_contacts = $conn->prepare("
    SELECT u.id, u.name, 
           (SELECT message FROM messages m WHERE (m.sender_id = u.id AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = u.id) ORDER BY m.created_at DESC LIMIT 1) as last_msg,
           (SELECT created_at FROM messages m WHERE (m.sender_id = u.id AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = u.id) ORDER BY m.created_at DESC LIMIT 1) as last_time,
           (SELECT COUNT(*) FROM messages m WHERE m.sender_id = u.id AND m.receiver_id = ? AND m.is_read = 0) as unread_count
    FROM users u 
    WHERE u.role = 'customer'
    ORDER BY last_time DESC, u.name ASC
");
$stmt_contacts->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$contacts = $stmt_contacts->fetchAll();

// Fetch Messages if a customer is selected
$messages = [];
$active_customer = null;
if($customer_id > 0) {
    $stmt_cust = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt_cust->execute([$customer_id]);
    $active_customer = $stmt_cust->fetch();

    if($active_customer) {
        $stmt_chat = $conn->prepare("
            SELECT * FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?) 
            ORDER BY created_at ASC
        ");
        $stmt_chat->execute([$_SESSION['user_id'], $customer_id, $customer_id, $_SESSION['user_id']]);
        $messages = $stmt_chat->fetchAll();
    }
}

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
                <a href="index.php?page=admin_messages" class="active d-flex justify-content-between align-items-center">
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
    <div id="content" class="d-flex flex-column" style="height: 100vh; overflow: hidden;">
        <!-- Topbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent mb-4 flex-shrink-0">
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

        <!-- Main Chat Area Split View -->
        <div class="container-fluid d-flex flex-column flex-grow-1 overflow-hidden">
            <h3 class="fw-bold mb-4 flex-shrink-0" style="color: var(--text-color);">Ruang Chat</h3>
            
            <div class="d-flex flex-grow-1 overflow-hidden gap-3 pb-3">
            
            <!-- Left: Contact List -->
            <div class="card border-0 shadow-sm rounded-4 d-none d-md-flex flex-column h-100" style="width: 350px;">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="fw-bold m-0"><i class="fas fa-address-book text-primary me-2"></i>Daftar Customer</h6>
                </div>
                <div class="card-body p-0 overflow-auto" id="contactList">
                    <div class="list-group list-group-flush rounded-bottom-4">
                        <?php foreach($contacts as $contact): ?>
                            <a href="index.php?page=admin_messages&customer_id=<?= $contact['id'] ?>" class="list-group-item list-group-item-action p-3 border-bottom <?= $customer_id == $contact['id'] ? 'bg-light border-start border-4 border-primary' : '' ?>">
                                <div class="d-flex align-items-center">
                                    <div class="position-relative me-3">
                                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 40px; height: 40px; font-weight: bold;">
                                            <?= strtoupper(substr(trim($contact['name']), 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold text-truncate" style="max-width: 150px; font-size: 0.95rem; color: var(--text-color);"><?= htmlspecialchars($contact['name']) ?></h6>
                                            <?php if($contact['last_time']): ?>
                                                <small class="text-muted" style="font-size: 0.7rem;"><?= date('H:i', strtotime($contact['last_time'])) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="mb-0 text-muted small text-truncate" style="max-width: 180px; font-size: 0.8rem;">
                                                <?= $contact['last_msg'] ? htmlspecialchars($contact['last_msg']) : '<i class="text-secondary opacity-50">Belum ada obrolan</i>' ?>
                                            </p>
                                            <?php if($contact['unread_count'] > 0): ?>
                                                <span class="badge bg-danger rounded-pill flex-shrink-0" style="font-size: 0.7rem;"><?= $contact['unread_count'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right: Chat Window -->
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1 d-flex flex-column h-100 overflow-hidden">
                <?php if(!$active_customer): ?>
                    <!-- Empty State -->
                    <div class="d-flex flex-column justify-content-center align-items-center h-100 text-muted">
                        <i class="fas fa-paper-plane fa-4x mb-3 text-primary opacity-25"></i>
                        <h5>Pilih Percakapan</h5>
                        <p class="small text-center px-4">Pilih customer di sebelah kiri untuk melihat detail diskusi <br>atau membalas lampiran progres tugas.</p>
                        
                        <!-- Mobile Contact List Overlay Trigger -->
                        <button class="btn btn-outline-primary rounded-pill d-md-none mt-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileContacts">
                            <i class="fas fa-list-ul me-2"></i>Buka Kontak
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Active Chat Header -->
                    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center flex-shrink-0">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-light d-md-none me-2 shadow-sm rounded-circle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileContacts">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm me-3" style="width: 45px; height: 45px; font-weight: bold; font-size: 1.2rem;">
                                <?= strtoupper(substr(trim($active_customer['name']), 0, 1)) ?>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="color: var(--text-color);"><?= htmlspecialchars($active_customer['name']) ?></h6>
                                <small class="text-muted"><i class="fas fa-circle text-success" style="font-size: 8px;"></i> Customer / Klien</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat History Area -->
                    <div class="card-body overflow-auto p-4" id="chatContainer" style="background-color: var(--card-bg);">
                        <?php if(empty($messages)): ?>
                            <div class="text-center text-muted my-5">
                                <p class="small bg-secondary bg-opacity-10 py-1 px-3 rounded-pill d-inline-block">Percakapan dimulai</p>
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
                                            <img src="<?= $msg['attachment_path'] ?>" class="img-fluid rounded border border-light mb-2 w-100" style="max-height: 250px; object-fit: contain; <?= $is_me ? 'opacity: 0.9;' : '' ?>" alt="Attachment">
                                        </a>
                                    <?php else: ?>
                                        <div class="border rounded p-2 mb-2 bg-white bg-opacity-25 d-flex align-items-center gap-2">
                                            <i class="fas fa-file-alt fs-4"></i>
                                            <a href="<?= $msg['attachment_path'] ?>" target="_blank" class="<?= $is_me ? 'text-white' : 'text-primary' ?> text-decoration-none fw-medium small text-truncate" style="max-width: 150px;">Buka Lampiran</a>
                                        </div>
                                    <?php endif; endif; ?>
                                    
                                    <div class="message-text" style="white-space: pre-wrap; word-break: break-word; font-size: 0.95rem; line-height: 1.4;"><?= htmlspecialchars($msg['message']) ?></div>
                                    
                                    <div class="text-end mt-1 <?= $is_me ? 'text-light text-opacity-75' : 'text-muted' ?>" style="font-size: 0.7rem;">
                                        <?= date('d M, H:i', strtotime($msg['created_at'])) ?>
                                        <?php if($is_me): ?>
                                            <i class="fas fa-check-double ms-1 <?= $msg['is_read'] ? 'text-info' : '' ?>"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Chat Input Area -->
                    <div class="card-footer bg-white border-top p-3 flex-shrink-0">
                        <form action="index.php?page=admin_messages" method="POST" enctype="multipart/form-data" id="chatForm">
                            <input type="hidden" name="receiver_id" value="<?= $customer_id ?>">
                            <div class="input-group">
                                <label class="input-group-text bg-transparent border-end-0 cursor-pointer text-muted hover-lift" for="attachmentInput" title="Kirim Berkas / Foto Progress">
                                    <i class="fas fa-paperclip px-2"></i>
                                </label>
                                <input type="file" class="d-none" id="attachmentInput" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.zip,.docx">
                                
                                <textarea class="form-control border-start-0 border-end-0 shadow-none m-0" name="message" id="messageInput" rows="1" placeholder="Balas <?= htmlspecialchars(explode(' ', trim($active_customer['name']))[0]) ?>..." style="resize: none; padding-top: 10px;"></textarea>
                                
                                <button class="btn btn-primary px-4 fw-bold" type="submit" id="sendBtn">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <div id="attachmentPreview" class="small text-primary mt-2 fw-medium" style="display: none;">
                                <i class="fas fa-file-image me-1"></i> <span id="fileName"></span> 
                                <i class="fas fa-times text-danger ms-2 cursor-pointer" onclick="clearAttachment()" title="Hapus Lampiran"></i>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Offcanvas Contact List -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileContacts" aria-labelledby="mobileContactsLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="mobileContactsLabel"><i class="fas fa-address-book text-primary me-2"></i>Kontak Klien</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="list-group list-group-flush rounded-0" id="contactListMobile">
            <!-- Will be populated via JS by cloning the desktop list to avoid duplication of DOM setup -->
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.min-w-0 { min-width: 0; }
#chatContainer::-webkit-scrollbar, #contactList::-webkit-scrollbar { width: 6px; }
#chatContainer::-webkit-scrollbar-track, #contactList::-webkit-scrollbar-track { background: transparent; }
#chatContainer::-webkit-scrollbar-thumb, #contactList::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.1); border-radius: 10px; }
[data-theme="dark"] #chatContainer::-webkit-scrollbar-thumb, [data-theme="dark"] #contactList::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.1); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Copy contact list for mobile view
    const desktopContacts = document.getElementById('contactList') ? document.getElementById('contactList').innerHTML : '';
    if (document.getElementById('contactListMobile')) {
        document.getElementById('contactListMobile').innerHTML = desktopContacts;
    }

    // Scroll chat to bottom
    const chatContainer = document.getElementById('chatContainer');
    if(chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Attachment Preview behavior
    const attachmentInput = document.getElementById('attachmentInput');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const fileName = document.getElementById('fileName');

    if(attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            if(this.files && this.files[0]) {
                fileName.innerText = this.files[0].name;
                attachmentPreview.style.display = 'block';
            }
        });
    }

    // Auto-resize message box
    const messageInput = document.getElementById('messageInput');
    if(messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto'; // Reset height
            let newHeight = Math.min(this.scrollHeight, 120); // Max 120px
            this.style.height = (this.value ? newHeight : 38) + 'px';
        });
        
        // Quick submit shortcut
        messageInput.addEventListener('keydown', function(e) {
            if(e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if(this.value.trim() !== '' || attachmentInput.files.length > 0) {
                    document.getElementById('chatForm').submit();
                }
            }
        });
    }
});

function clearAttachment() {
    document.getElementById('attachmentInput').value = '';
    document.getElementById('attachmentPreview').style.display = 'none';
}
</script>

<?php require 'views/layouts/footer.php'; ?>
