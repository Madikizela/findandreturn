<?php
/**
 * Sidebar component for School Context Pages
 * Adapts based on user role (Parent vs School Admin)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'parent';
$current_page = basename($_SERVER['PHP_SELF']);

// Determine dashboard link based on role
$dashboard_link = 'parent_dashboard';
if ($role == 'user') {
    $dashboard_link = 'user_dashboard';
} elseif ($role == 'superUser') {
    $dashboard_link = 's_dashboard';
}

// System theme (already defined in parent page, but ensuring fallback)
$themeColor = $themeColor ?? "#007bff";
?>
<!-- Side Navbar -->
<nav class="side-navbar z-index-40" style="background-color: <?php echo $themeColor; ?>; color: #fff; min-width: 250px;">
    <div class="py-3 px-3">
        <span class="text-uppercase text-gray-500 text-xs letter-spacing-0 heading" style="color: rgba(255,255,255,0.7) !important; font-weight: bold;">MAIN</span>
    </div>
    <ul class="list-unstyled">
        <li class="sidebar-item mb-1">
            <a class="sidebar-link <?php echo in_array($current_page, ['view_lost_item.php', 'school_list.php', 'view_lost_items.php']) ? 'active' : ''; ?>" href="school_list" style="padding: 15px 20px; color: #fff; transition: all 0.3s;">
                <i class="fas fa-search me-3"></i>List of lost Items
            </a>
        </li>
        <li class="sidebar-item mb-1">
            <a class="sidebar-link <?php echo $current_page == 'list_of_found_item.php' ? 'active' : ''; ?>" href="list_of_found_item" style="padding: 15px 20px; color: #fff; transition: all 0.3s;">
                <i class="fas fa-list me-3"></i>List of found Item
            </a>
        </li>
        <li class="sidebar-item mb-1">
            <a class="sidebar-link <?php echo in_array($current_page, ['view_announcement1.php', 'view_announcement.php', 'announcement.php']) ? 'active' : ''; ?>" href="view_announcement1" style="padding: 15px 20px; color: #fff; transition: all 0.3s;">
                <i class="fas fa-bullhorn me-3"></i>Announcements
            </a>
        </li>

        <?php if ($role == 'user'): // Full Admin Menu for School Admins ?>
        <li class="sidebar-item mb-1 border-top pt-2" style="border-color: rgba(255,255,255,0.1) !important;">
            <span class="text-uppercase text-gray-500 text-xs letter-spacing-0 heading mx-3" style="color: rgba(255,255,255,0.7) !important; font-weight: bold;">ADMIN TOOLS</span>
        </li>
        <li class="sidebar-item mb-1">
            <a class="sidebar-link <?php echo $current_page == 'children_list.php' ? 'active' : ''; ?>" href="children_list" style="padding: 12px 20px; color: #fff; transition: all 0.3s;">
                <i class="fas fa-child me-3"></i>Manage Children
            </a>
        </li>
        <li class="sidebar-item mb-1">
            <a class="sidebar-link <?php echo $current_page == 'index2.php' ? 'active' : ''; ?>" href="index2" style="padding: 12px 20px; color: #fff; transition: all 0.3s;">
                <i class="fas fa-qrcode me-3"></i>Scan Item
            </a>
        </li>
        <li class="sidebar-item mb-1">
            <a class="sidebar-link <?php echo $current_page == 'accepted_by_admin.php' ? 'active' : ''; ?>" href="accepted_by_admin" style="padding: 12px 20px; color: #fff; transition: all 0.3s;">
                <i class="fas fa-check-circle me-3"></i>Accepted Items
            </a>
        </li>
        <li class="sidebar-item mb-1">
            <a class="sidebar-link <?php echo $current_page == 'view_orders.php' ? 'active' : ''; ?>" href="view_orders" style="padding: 12px 20px; color: #fff; transition: all 0.3s;">
                <i class="fas fa-shopping-cart me-3"></i>School Orders
            </a>
        </li>
        <li class="sidebar-item mb-1">
            <a class="sidebar-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>" href="settings" style="padding: 12px 20px; color: #fff; transition: all 0.3s;">
                <i class="fas fa-cog me-3"></i>School Settings
            </a>
        </li>
        <li class="sidebar-item mb-1">
            <a class="sidebar-link <?php echo $current_page == 'school_agents.php' ? 'active' : ''; ?>" href="school_agents" style="padding: 12px 20px; color: #fff; transition: all 0.3s;">
                <i class="fab fa-whatsapp me-3"></i>WhatsApp Agents
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="mt-4 py-3 px-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
        <span class="text-uppercase text-gray-500 text-xs letter-spacing-0 heading" style="color: rgba(255,255,255,0.7) !important; font-weight: bold;">NAVIGATION</span>
        <ul class="list-unstyled mt-2">
            <li class="sidebar-item">
                <a class="sidebar-link" href="<?php echo $dashboard_link; ?>" style="padding: 10px 20px; color: #fff; transition: all 0.3s;">
                    <i class="fas fa-arrow-left me-3"></i>Back to Dashboard
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
    .side-navbar .sidebar-link:hover {
        background-color: rgba(255, 255, 255, 0.2);
        color: #fff !important;
        text-decoration: none;
    }
    .side-navbar .sidebar-link.active {
        background-color: rgba(255, 255, 255, 0.3) !important;
        color: #fff !important;
        font-weight: bold;
    }
    .side-navbar .sidebar-link i {
        width: 20px;
        text-align: center;
    }
</style>
