<?php
/**
 * Main Dynamic Sidebar Switcher
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';

switch ($role) {
    case 'superUser':
        include 'superadmin_sidebar.php';
        break;
    case 'user':
        include 'admin_sidebar.php';
        break;
    case 'parent':
        include 'parent_sidebar.php';
        break;
    case 'teacher':
        include 'teacher_sidebar.php';
        break;
    default:
        // Default sidebar fallback
        include 'parent_sidebar.php';
        break;
}
?>

