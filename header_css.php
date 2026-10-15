<?php
/**
 * Common Header CSS
 */
?>
<style>
    :root {
        --primary-color: <?php echo $themeColor; ?>;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --info-color: #17a2b8;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
    }

    .navbar {
        background-color: var(--primary-color) !important;
        transition: all 0.3s ease;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #555;
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 8px;
        margin: 2px 10px;
        font-weight: 500;
    }

    .sidebar-link i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
        color: var(--primary-color);
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .sidebar-link:hover,
    .sidebar-item.active .sidebar-link {
        background-color: rgba(0, 0, 0, 0.05);
        color: var(--primary-color) !important;
        text-decoration: none;
        transform: translateX(5px);
    }

    .sidebar-item.active .sidebar-link {
        background-color: var(--primary-color);
        color: white !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .sidebar-item.active .sidebar-link i {
        color: white !important;
        opacity: 1;
    }

    .dashboard-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    .table-container {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .user-avatar {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-modern {
        border-radius: 20px;
        padding: 5px 15px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Dashboard Icon Colors */
    .card-icon.primary { background: rgba(0, 123, 255, 0.1); color: #007bff; }
    .card-icon.success { background: rgba(40, 167, 69, 0.1); color: #28a745; }
    .card-icon.warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .card-icon.danger { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .card-icon.info { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }

    /* Theme-aware primary icon */
    .card-icon.theme-primary { 
        background-color: color-mix(in srgb, var(--primary-color), transparent 90%); 
        color: var(--primary-color); 
    }
    
    .dashboard-card .card-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    /* Quick Actions */
    .quick-actions {
        margin: 30px 0;
    }

    .quick-actions h4 {
        margin-bottom: 20px;
        color: #333;
        font-weight: 600;
    }

    .action-btn {
        display: inline-block;
        padding: 12px 25px;
        background-color: white;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
        border-radius: 10px;
        text-decoration: none;
        margin-right: 15px;
        margin-bottom: 15px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .action-btn:hover {
        background-color: var(--primary-color);
        color: white !important;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-decoration: none;
    }
</style>
<?php include 'meta_pixel.php'; ?>

