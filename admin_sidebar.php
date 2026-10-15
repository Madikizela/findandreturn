<?php
/**
 * Admin Sidebar Component
 */
?>
<!-- Side Navbar -->
<nav class="side-navbar z-index-40">
    <span class="text-uppercase text-gray-400 text-xs letter-spacing-0 mx-3 px-2 heading">Main</span>
    <ul class="list-unstyled py-4">
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'user_dashboard.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="user_dashboard">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'children_list.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="children_list">
                <i class="fas fa-child"></i>List of children's
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'accepted_by_admin.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="accepted_by_admin">
                <i class="fas fa-check-circle"></i>Accepted by Admin Items
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'collected_items.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="collected_items">
                <i class="fas fa-hand-holding"></i>Collected by Parent items
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'index2.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="index2">
                <i class="fas fa-qrcode"></i>Scan item to confirm
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'colecting_item.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="colecting_item">
                <i class="fas fa-users"></i>Parent Collection of Item
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="settings">
                <i class="fas fa-cog"></i>School Profile Settings
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'view_lost_items.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="view_lost_items">
                <i class="fas fa-search"></i>List of lost items
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'list_of_found_item.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="list_of_found_item">
                <i class="fas fa-hand-holding-heart"></i>List of found item
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'add_product.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="add_product">
                <i class="fas fa-plus-circle"></i>Add Selling item
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'school_marketplace.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="school_marketplace">
                <i class="fas fa-store"></i>School Marketplace
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'view_orders.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="view_orders">
                <i class="fas fa-shopping-cart"></i>View Orders
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'inventory_status.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="inventory_status">
                <i class="fas fa-boxes"></i>Inventory Status
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'announcement.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="announcement">
                <i class="fas fa-bullhorn"></i>Make Announcement
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'view_announcement.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="view_announcement">
                <i class="fas fa-list-alt"></i>View Announcements
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'school_agents.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="school_agents">
                <i class="fab fa-whatsapp" style="color:#25D366;"></i>WhatsApp Agents
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'wa_inbox.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="wa_inbox">
                <i class="fab fa-whatsapp" style="color:#25D366;"></i>WhatsApp Inbox
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_enquiries.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="manage_enquiries">
                <i class="fas fa-headset"></i>Manage Enquiries
            </a>
        </li>
    </ul>
</nav>

