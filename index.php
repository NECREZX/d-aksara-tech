<?php
require_once 'config/database.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Basic Routing
switch ($page) {
    case 'home':
        require 'views/public/index.php';
        break;
    case 'services':
        require 'views/public/services.php';
        break;
    case 'login':
        require 'views/auth/login.php';
        break;
    case 'register':
        require 'views/auth/register.php';
        break;
    case 'logout':
        require 'controllers/AuthController.php';
        break;
    
    // Auth actions
    case 'do_login':
    case 'do_register':
        require 'controllers/AuthController.php';
        break;

    // Customer
    case 'customer_dashboard':
        require 'views/customer/dashboard.php';
        break;
    case 'customer_order':
        require 'views/customer/order.php';
        break;
    case 'customer_history':
        require 'views/customer/history.php';
        break;
    case 'customer_payment':
        require 'views/customer/payment.php';
        break;
    case 'customer_profile':
        require 'views/customer/profile.php';
        break;
    case 'customer_notifications':
        require 'views/customer/notifications.php';
        break;
    case 'customer_messages':
        require 'views/customer/messages.php';
        break;

    // Admin
    case 'admin_dashboard':
        require 'views/admin/dashboard.php';
        break;
    case 'admin_services':
        require 'views/admin/services.php';
        break;
    case 'admin_orders':
        require 'views/admin/orders.php';
        break;
    case 'admin_users':
        require 'views/admin/users.php';
        break;
    case 'admin_messages':
        require 'views/admin/messages.php';
        break;

    // Default
    default:
        require 'views/public/index.php';
        break;
}
?>
