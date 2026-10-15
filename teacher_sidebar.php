<?php
/**
 * Teacher Sidebar Component
 */
?>
<!-- Side Navbar -->
<nav class="side-navbar z-index-40">
    <span class="text-uppercase text-gray-400 text-xs letter-spacing-0 mx-3 px-2 heading">Main</span>
    <ul class="list-unstyled py-4">
        <li class="sidebar-item active">
            <a class="sidebar-link" href="#">
                <i class="fas fa-home"></i>Dashboard
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="children_list">
                <i class="fas fa-child"></i>Class List
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="view_lost_items">
                <i class="fas fa-search"></i>Lost Items
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="list_of_found_item">
                <i class="fas fa-hand-holding-heart"></i>Found Items
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="announcement">
                <i class="fas fa-bullhorn"></i>Make Announcement
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'school_marketplace.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="school_marketplace">
                <i class="fas fa-store"></i>School Marketplace
            </a>
        </li>
    </ul>
</nav>

