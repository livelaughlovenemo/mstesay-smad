<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SMAD - Sales & Inventory Management Dashboard</title>
  
  <!-- Your existing CSS links -->
  <link rel="stylesheet" href="../assets/css/login-styles.css">
  <link rel="stylesheet" href="assets/landing-styles.css">
  <link rel="icon" type="image/png" href="../assets/img/mainlogo.png">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Castoro:ital@0;1&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Urbanist:ital,wght@0,100..900;1,100..900&family=Varela+Round&display=swap" rel="stylesheet">
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <style>
    /* Reset and base styles only - remove conflicting dashboard styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Montserrat', sans-serif;
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding: 0 20px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .logo-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .logo-img {
        height: 40px;
        width: 40px;
        border-radius: 50%;
        object-fit: contain;
        background: white;
        padding: 5px;
    }
    
    .brand-text h1 {
        font-family: 'Pacifico', cursive;
        color: #F5A200;
        font-size: 22px;
        font-weight: normal;
        margin: 0;
        line-height: 1.2;
    }
    
    .brand-text p {
        font-size: 11px;
        color: #ccc;
        margin: 0;
        font-family: 'Montserrat', sans-serif;
    }
    
    .nav-menu {
        display: flex;
        gap: 20px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .nav-menu li {
        position: relative;
    }
    
    .nav-menu a {
        color: white;
        text-decoration: none;
        font-weight: 500;
        padding: 8px 0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    
    .nav-menu a:hover {
        color: #F5A200;
    }
    
    .nav-menu a.active {
        color: #F5A200;
        font-weight: 600;
    }
    
    .nav-menu a.active::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 100%;
        height: 3px;
        background: #F5A200;
        border-radius: 3px;
    }
    
    .header-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .user-profile {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 12px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    
    .user-profile:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .user-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #F5A200;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 16px;
    }
    
    .user-info {
        display: flex;
        flex-direction: column;
    }
    
    .user-name {
        font-weight: 600;
        color: white;
        font-size: 14px;
    }
    
    .user-role {
        font-size: 11px;
        color: #ccc;
    }
    
    .logout-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 20px;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
        text-decoration: none;
        font-size: 14px;
    }
    
    .logout-btn:hover {
        background: #c82333;
    }
    
    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
    }
    
    /* Responsive Design */
    @media (max-width: 1024px) {
        .nav-menu {
            gap: 15px;
        }
        
        .nav-menu a {
            font-size: 13px;
        }
    }
    
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 0 15px;
            height: 60px;
        }
        
        .logo-img {
            height: 35px;
            width: 35px;
        }
        
        .brand-text h1 {
            font-size: 18px;
        }
        
        .mobile-menu-btn {
            display: block;
        }
        
        .nav-menu {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            background: #2c3e50;
            flex-direction: column;
            padding: 15px;
            gap: 0;
            transform: translateY(-100%);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            z-index: 999;
        }
        
        .nav-menu.active {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        
        .nav-menu li {
            width: 100%;
        }
        
        .nav-menu a {
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
        }
        
        .nav-menu a:last-child {
            border-bottom: none;
        }
        
        .nav-menu a.active::after {
            display: none;
        }
        
        .nav-menu a.active {
            background: rgba(245, 162, 0, 0.1);
            padding-left: 15px;
            border-radius: 5px;
        }
        
        .user-name {
            display: none;
        }
        
        .user-info {
            display: none;
        }
        
        .user-profile {
            padding: 6px;
        }
    }
    
    @media (max-width: 480px) {
        .brand-text h1 {
            font-size: 16px;
        }
        
        .brand-text p {
            display: none;
        }
        
        .logout-btn span {
            display: none;
        }
        
        .logout-btn {
            padding: 8px;
            width: 35px;
            height: 35px;
            justify-content: center;
        }
    }
  </style>
</head>
<body>

<header class="dashboard-header">
    <div class="header-left">
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="logo-container">
            <img src="../assets/img/mainlogo.png" alt="Ms. Tesay Chicken Logo" class="logo-img">
            <div class="brand-text">
                <h1>Ms. Tesay Chicken</h1>
                <p>Dashboard</p>
            </div>
        </div>
    </div>
    
    <nav>
        <ul class="nav-menu" id="navMenu">
            <li>
                <a href="dashboard.php?tab=sales" class="<?= (isset($_GET['tab']) && $_GET['tab'] === 'sales') || !isset($_GET['tab']) ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Sales</span>
                </a>
            </li>
            <li>
                <a href="dashboard.php?tab=inventory_dashboard" class="<?= isset($_GET['tab']) && $_GET['tab'] === 'inventory_dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <?php if(isset($_SESSION['role']) && ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Manager')): ?>
            <li>
                <a href="dashboard.php?tab=inventory" class="<?= isset($_GET['tab']) && $_GET['tab'] === 'inventory' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Records</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'Admin'): ?>
            <li>
                <a href="dashboard.php?tab=products" class="<?= isset($_GET['tab']) && $_GET['tab'] === 'products' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Products</span>
                </a>
            </li>
            <li>
                <a href="dashboard.php?tab=users" class="<?= isset($_GET['tab']) && $_GET['tab'] === 'users' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <div class="header-right">
        <div class="user-profile">
            <div class="user-avatar">
                <?php 
                $initial = isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'G';
                echo $initial;
                ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></span>
                <span class="user-role"><?= isset($_SESSION['role']) ? $_SESSION['role'] : 'Visitor' ?></span>
            </div>
        </div>
        
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</header>

<main class="dashboard-wrapper">
<script>
// Mobile menu toggle
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const navMenu = document.getElementById('navMenu');

if (mobileMenuBtn && navMenu) {
    mobileMenuBtn.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        mobileMenuBtn.innerHTML = navMenu.classList.contains('active') 
            ? '<i class="fas fa-times"></i>' 
            : '<i class="fas fa-bars"></i>';
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (event) => {
        if (!mobileMenuBtn.contains(event.target) && !navMenu.contains(event.target)) {
            navMenu.classList.remove('active');
            mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
}

// Update active nav link
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentTab = urlParams.get('tab') || 'sales';
    const navLinks = document.querySelectorAll('.nav-menu a');
    
    navLinks.forEach(link => {
        const linkTab = new URLSearchParams(link.href.split('?')[1]).get('tab');
        if (linkTab === currentTab) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});
</script>