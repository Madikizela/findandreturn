<?php
/**
 * Super Admin Sidebar Component
 */
?>
<!-- Side Navbar -->
<nav class="side-navbar z-index-40">
    <span class="text-uppercase text-gray-400 text-xs letter-spacing-0 mx-3 px-2 heading">Main</span>
    <ul class="list-unstyled py-4">
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 's_dashboard.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="s_dashboard">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'user_list.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="user_list">
                <i class="fas fa-users"></i>List of Users
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'parent_list.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="parent_list">
                <i class="fas fa-user-friends"></i>List of Parents
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'children_list.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="children_list">
                <i class="fas fa-child"></i>List of Children
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'update_user.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="update_user">
                <i class="fas fa-user-edit"></i>Update Admin Details
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'change_role.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="change_role">
                <i class="fas fa-user-cog"></i>Change User Role
            </a>
        </li>
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_schools.php' ? 'active' : ''; ?>">
            <a class="sidebar-link" href="manage_schools">
                <i class="fas fa-school"></i>Manage Schools
            </a>
        </li>
    </ul>
</nav>

