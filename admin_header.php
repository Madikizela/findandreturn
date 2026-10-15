<?php
/**
 * Admin Header Component
 */
require_once 'security.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$name = $_SESSION['user_name'] ?? 'Admin';
$school = $_SESSION['school'] ?? 'School';
$themeColor = $themeColor ?? "#007bff";
$logoPath = $logoPath ?? "img/Find&R.png";
?>
<header class="header z-index-50">
    <nav class="navbar py-3 px-0 shadow-sm text-white position-relative" style="background-color: <?php echo $themeColor; ?> !important;">
        <div class="container-fluid w-100">
            <div class="navbar-holder d-flex align-items-center justify-content-between w-100">
                <!-- Navbar Header-->
                <div class="navbar-header">
                    <a class="navbar-brand d-none d-sm-inline-block" href="user_dashboard">
                        <div class="brand-text d-none d-lg-inline-block" style="display: flex; align-items: center;">
                            <img src="img/f-icon.ico" alt="Find and Return Logo" style="width: 35px; height: 35px; margin-right: 12px; border-radius: 8px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                            <span style="color: white; font-weight: 700; font-size: 1.2rem; letter-spacing: 0.5px;">Find & Return F&R</span>
                        </div>
                        <div class="brand-text d-none d-sm-inline-block d-lg-none" style="display: flex; align-items: center;">
                            <img src="img/f-icon.ico" alt="Find and Return Logo" style="width: 30px; height: 30px; margin-right: 10px; border-radius: 6px; display: inline-block;">
                            <strong style="color: white; font-weight: 700;">F&R</strong>
                        </div>
                    </a>
                    <!-- Toggle Button-->
                    <a class="menu-btn active" id="toggle-btn" href="#"><span></span><span></span><span></span></a>
                </div>
                
                <div class="center-logo d-flex align-items-center justify-content-center">
                    <img src='<?php echo $logoPath; ?>' alt="Logo" style="height: 60px; width: auto; max-width: 150px; border-radius: 8px; padding: 2px; object-fit: contain;">
                </div>

                <!-- Navbar Menu -->
                <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center mb-0">
                    <li class="nav-item">
                        <div class="user-info d-flex align-items-center">
                            <div class="user-avatar me-2" style="width: 35px; height: 35px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 1px solid rgba(255,255,255,0.3);">
                                <?php echo h(strtoupper(substr($name, 0, 1))); ?>
                            </div>
                            <span class="text-white me-3 d-none d-md-inline"><?php echo h($name); ?></span>
                            <a class="nav-link text-white btn btn-sm btn-outline-light px-3" href="Logout" style="border-radius: 20px; border: 1.5px solid rgba(255,255,255,0.5); font-weight: 600; transition: all 0.3s ease;">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

