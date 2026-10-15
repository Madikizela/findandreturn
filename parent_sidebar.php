<?php
/**
 * Sidebar component for Parent Dashboard
 */
?>
<!-- Side Navbar -->
<nav class="side-navbar z-index-40">
    <span class="text-uppercase text-gray-400 text-xs letter-spacing-0 mx-3 px-2 heading">Main</span>
    <ul class="list-unstyled py-4">
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'parent_dashboard.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="parent_dashboard">
                <i class="fas fa-home"></i>Dashboard
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'add_child.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="add_child">
                <i class="fas fa-child"></i>Add a Child
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'update_child.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="update_child">
                <i class="fas fa-edit"></i>Update Child Details
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'update_user.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="update_user">
                <i class="fas fa-user-edit"></i>Update Parent Details
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'request_item.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="request_item">
                <i class="fas fa-exclamation-triangle"></i>Report Lost Item
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'lost_item_per_school.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="lost_item_per_school">
                <i class="fas fa-hand-holding-heart"></i>Report Found Item
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'school_page.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="school_page">
                <i class="fas fa-school"></i>School Page
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'school_marketplace.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="school_marketplace">
                <i class="fas fa-store"></i>School Marketplace
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'parent_orders.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="parent_orders">
                <i class="fas fa-receipt"></i>My Orders
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'index4.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="index4">
                <i class="fas fa-qrcode"></i>Scan Item
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'contact_support.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="contact_support">
                <i class="fab fa-whatsapp" style="color:#25D366;"></i>Contact Support
            </a>
        </li>
    </ul>
</nav>

