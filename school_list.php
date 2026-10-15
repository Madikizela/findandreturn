<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php

$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
$selectedSchool = $_SESSION['school'];
$role = $_SESSION['role'] ?? 'parent';

// Handle school selection from URL parameter
if (isset($_GET['school_id'])) {
    $selectedSchoolId = intval($_GET['school_id']);
    $_SESSION['school_id'] = $selectedSchoolId;
    
    // Fetch school name for session
    $stmt = $conn->prepare("SELECT school_name FROM schools WHERE id = ?");
    $stmt->bind_param("i", $selectedSchoolId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $_SESSION['school'] = $row['school_name'];
        $selectedSchool = $row['school_name'];
    }
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

// Get theme settings for the selected school
$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE school = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selectedSchool);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#007bff";
    $logoPath = "img/Find&R.png";
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo htmlspecialchars($selectedSchool); ?> - Find and Return</title>
    <meta name="description" content="View lost items and school information for <?php echo htmlspecialchars($selectedSchool); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <meta name="robots" content="all,follow">
    <!-- Google fonts - Poppins -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <!-- Choices CSS-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="css/custom.css">
    <!-- Favicon-->
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        nav:first-child {
            background-color: <?php echo $themeColor; ?> !important;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 2px 10px;
        }

        .sidebar-link:hover {
            background-color: rgba(0, 123, 255, 0.1);
            color: var(--primary-color);
            text-decoration: none;
            transform: translateX(5px);
        }

        .sidebar-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .sidebar-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px 0;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 15px 12px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-registered {
            background-color: var(--warning-color);
            color: #212529;
        }

        .status-found {
            background-color: var(--success-color);
            color: white;
        }

        .status-collected {
            background-color: var(--info-color);
            color: white;
        }

        .school-header {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .school-header h1 {
            margin-bottom: 10px;
            font-weight: 700;
        }

        .school-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        .navbar-brand {
            display: flex !important;
            align-items: center;
        }

        .navbar-brand .brand-text {
            display: flex !important;
            align-items: center;
            color: white !important;
        }

        .navbar-brand img {
            display: inline-block !important;
            vertical-align: middle;
        }

        .navbar-brand span,
        .navbar-brand strong {
            color: white !important;
            font-weight: 600;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }

        .stat-card i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .stat-card p {
            color: var(--secondary-color);
            margin: 0;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 8px 6px;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <?php include 'main_header.php'; ?>
        <div class="page-content d-flex align-items-stretch">
            <?php include 'school_sidebar.php'; ?>
            <div class="content-inner w-100">
                <!-- School Header-->
                <div class="school-header">
                    <h1><i class="fas fa-school me-3"></i><?php echo htmlspecialchars($selectedSchool); ?></h1>
                    <p>Lost and Found Items Management</p>
                </div>
                
                <!-- Breadcrumb-->
                <div class="bg-white">
                    <div class="container-fluid">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 py-3">
                                <?php if ($role == 'parent'): ?>
                                    <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                                    <li class="breadcrumb-item"><a class="fw-light" href="list_of_schools">All Schools</a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item"><a class="fw-light" href="user_dashboard">Dashboard</a></li>
                                <?php endif; ?>
                                <li class="breadcrumb-item active fw-light" aria-current="page"><?php echo htmlspecialchars($selectedSchool); ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <div class="container-fluid">
                    <?php
                    // Get school_id from session school name
                    $schoolNameForStats = $_SESSION['school'] ?? '';
                    $schoolIdRow = $conn->query("SELECT id FROM schools WHERE school_name = '" . $conn->real_escape_string($schoolNameForStats) . "' LIMIT 1");
                    $schoolIdForStats = $schoolIdRow && $schoolIdRow->num_rows > 0 ? $schoolIdRow->fetch_assoc()['id'] : 0;

                    // Get statistics from new_lost_items
                    $statsSql = "SELECT 
                        COUNT(*) as total_items,
                        SUM(CASE WHEN tracking_status = 'lost' THEN 1 ELSE 0 END) as registered_items,
                        SUM(CASE WHEN tracking_status = 'found' THEN 1 ELSE 0 END) as found_items,
                        SUM(CASE WHEN tracking_status = 'collected' THEN 1 ELSE 0 END) as collected_items
                        FROM new_lost_items WHERE school_id = ?";
                    $statsStmt = $conn->prepare($statsSql);
                    $statsStmt->bind_param("i", $schoolIdForStats);
                    $statsStmt->execute();
                    $statsResult = $statsStmt->get_result();
                    $stats = $statsResult->fetch_assoc();
                    $statsStmt->close();
                    ?>
                    
                    <!-- Statistics Cards -->
                    <div class="stats-cards">
                        <div class="stat-card">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3><?php echo $stats['total_items']; ?></h3>
                            <p>Total Lost Items</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-clock"></i>
                            <h3><?php echo $stats['registered_items']; ?></h3>
                            <p>Still Missing</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-hand-holding-heart"></i>
                            <h3><?php echo $stats['found_items']; ?></h3>
                            <p>Found Items</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-check-circle"></i>
                            <h3><?php echo $stats['collected_items']; ?></h3>
                            <p>Collected Items</p>
                        </div>
                    </div>
                    
                    <!-- Lost Items Table -->
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Child Name</th>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th>Lost Date</th>
                                        <th>Parent Email</th>
                                        <th>Unique ID</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $school = $_SESSION['school'] ?? '';
                                    $count = 1;

                                    // Get school_id from school name
                                    $schoolRow = $conn->query("SELECT id FROM schools WHERE school_name = '" . $conn->real_escape_string($school) . "' LIMIT 1");
                                    $schoolId = $schoolRow && $schoolRow->num_rows > 0 ? $schoolRow->fetch_assoc()['id'] : 0;

                                    $sql = "SELECT nli.*, CONCAT(c.first_name, ' ', c.surname) as child_name,
                                                   u.email as parent_email, nli.unique_report_id as unique_id,
                                                   nli.item_name as item
                                            FROM new_lost_items nli
                                            LEFT JOIN children c ON nli.child_id = c.id
                                            LEFT JOIN users u ON c.parent_id = u.id
                                            WHERE nli.school_id = ? 
                                            ORDER BY nli.date_lost DESC";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $schoolId);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $statusClass = '';
                                            switch ($row["tracking_status"]) {
                                                case 'lost':
                                                    $statusClass = 'status-registered';
                                                    break;
                                                case 'found':
                                                    $statusClass = 'status-found';
                                                    break;
                                                case 'collected':
                                                    $statusClass = 'status-collected';
                                                    break;
                                            }
                                    ?>
                                    <tr>
                                        <td><?php echo $count; ?></td>
                                        <td><?php echo htmlspecialchars($row["child_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["item"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["description"]); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row["date_lost"])); ?></td>
                                        <td><?php echo htmlspecialchars($row["parent_email"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["unique_id"]); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($row["tracking_status"]); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php 
                                            $count++;
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No lost items found for this school</p>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                    $stmt->close();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript files-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <!-- Main File-->
    <script src="js/front.js"></script>
    <script>
        function injectSvgSprite(path) {
            var ajax = new XMLHttpRequest();
            ajax.open("GET", path, true);
            ajax.send();
            ajax.onload = function(e) {
                var div = document.createElement("div");
                div.className = 'd-none';
                div.innerHTML = ajax.responseText;
                document.body.insertBefore(div, document.body.childNodes[0]);
            }
        }
        // SVG sprite removed - external resource no longer available
    </script>
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

