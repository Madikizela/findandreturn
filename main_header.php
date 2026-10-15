<?php
/**
 * Main Dynamic Header Switcher
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';

switch ($role) {
    case 'superUser':
        include 'superadmin_header.php';
        break;
    case 'user':
        include 'admin_header.php';
        break;
    case 'parent':
        include 'parent_header.php';
        break;
    case 'teacher':
        include 'teacher_header.php';
        break;
    default:
        // Default header for guests or unknown roles
        include 'parent_header.php'; // Fallback
        break;
}
?>

