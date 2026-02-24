<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D'AKSARA TECH - Platform Layanan Jasa</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Light Theme Palette - Premium Elegant (Navy & Gold inspired) */
            --bg-color: #F8FAFC; /* Slate 50 */
            --text-color: #0F172A; /* Slate 900 */
            --text-muted: #64748B; /* Slate 500 */
            --card-bg: #FFFFFF;
            
            --primary-color: #0F172A; /* Deep Navy/Slate */
            --bs-primary-rgb: 15, 23, 42;
            --primary-hover: #1E293B;
            
            --secondary-color: #D97706; /* Amber/Gold */
            --bs-secondary-rgb: 217, 119, 6;
            
            --accent-color: #F59E0B;
            
            --success-color: #10B981; /* Emerald */
            --bs-success-rgb: 16, 185, 129;
            
            --danger-color: #EF4444; /* Red */
            --bs-danger-rgb: 239, 68, 68;
            
            --bs-info-rgb: 59, 130, 246; /* Blue 500 */
            --bs-warning-rgb: 245, 158, 11; /* Amber 500 */
            
            --navbar-bg: rgba(255, 255, 255, 0.95);
            --border-color: #E2E8F0; /* Slate 200 */
            --hover-bg: #F1F5F9; /* Slate 100 */
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-lg: 0 10px 25px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        [data-bs-theme="dark"] {
            /* Dark Theme Palette - Deep & Sleek */
            --bg-color: #1e293bb9; /* Very deep slate */
            --text-color: #F8FAFC;
            --text-muted: #94A3B8;
            --card-bg: #1E293B; /* Slate 800 */
            
            --primary-color: #F8FAFC;
            --bs-primary-rgb: 248, 250, 252;
            --primary-hover: #E2E8F0;
            
            --secondary-color: #F59E0B;
            --bs-secondary-rgb: 245, 158, 11;
            
            --accent-color: #FBBF24;
            
            --success-color: #10B981;
            --bs-success-rgb: 16, 185, 129;
            
            --danger-color: #EF4444;
            --bs-danger-rgb: 239, 68, 68;
            
            --bs-info-rgb: 96, 165, 250;
            --bs-warning-rgb: 251, 191, 36;
            
            --navbar-bg: rgba(30, 41, 59, 0.95);
            --border-color: #334155; /* Slate 700 */
            --hover-bg: #334155;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 25px -3px rgba(0, 0, 0, 0.5);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow-x: hidden;
            letter-spacing: -0.01em;
        }

        /* Typography Refinements */
        h1, h2, h3, h4, h5, h6 {
            color: var(--text-color);
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        
        .text-muted {
            color: var(--text-muted) !important;
        }

        /* Navbar */
        .navbar {
            background-color: var(--navbar-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
        }

        /* Premium Cards */
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }
        
        .hover-lift {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        /* Buttons */
        .btn {
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--bg-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        [data-bs-theme="dark"] .btn-primary {
            color: #0F172A;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: #fff;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: var(--bg-color);
        }


        /* Sidebar Styling */
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            min-height: 100vh;
        }

        #sidebar {
            min-width: 260px;
            max-width: 260px;
            background-color: var(--card-bg);
            border-right: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            top: 0;
            left: 0;
            overflow-y: auto;
        }
        
        #sidebar.active {
            margin-left: -260px;
        }

        #content {
            flex: 1;
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-left: 260px;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        #sidebar.active + #content {
            margin-left: 0;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu li {
            padding: 0.2rem 1rem;
        }

        .sidebar-menu a {
            padding: 0.8rem 1rem;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all 0.2s;
        }

        .sidebar-menu a:hover {
            background-color: var(--hover-bg);
            color: var(--text-color);
        }
        
        .sidebar-menu a.active {
            background-color: var(--primary-color);
            color: var(--bg-color);
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }
        
        [data-bs-theme="dark"] .sidebar-menu a.active {
            color: #0F172A;
        }

        .sidebar-menu i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 0.5rem;
            text-align: center;
        }

        .theme-switch {
            cursor: pointer;
            font-size: 1.25rem;
            color: var(--text-muted);
            transition: color 0.2s;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--hover-bg);
        }
        
        .theme-switch:hover {
            color: var(--text-color);
        }

        /* Special Badges */
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 600;
            border-radius: 6px;
        }

        /* DataTables Customization */
        .table {
            color: var(--text-color);
        }
        .table-light {
            background-color: var(--hover-bg);
            color: var(--text-color);
        }
        .table-hover tbody tr:hover {
            background-color: var(--hover-bg) !important;
            color: var(--text-color);
        }
        .table td, .table th {
            border-bottom-color: var(--border-color);
            padding: 1rem;
        }

        /* Responsive */
        @media (max-width: 991px) {
            #sidebar {
                margin-left: -260px;
            }
            #sidebar.active {
                margin-left: 0;
            }
            #content {
                margin-left: 0;
                padding: 1rem;
            }
            #sidebar.active + #content {
                margin-left: 0;
            }
            
            /* Add overlay when sidebar is active on mobile */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(3px);
                z-index: 998;
            }
            #sidebar.active ~ .sidebar-overlay {
                display: block;
            }
        }
    </style>
</head>
<body data-bs-theme="light">
<?php display_flash_message(); ?>
