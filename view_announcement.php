<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php

// Check if user is logged in first
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

// Get session variables with fallbacks
$email     = $_SESSION['socialWorkerEmail'] ?? '';
$name      = $_SESSION['user_name']         ?? '';
$school    = $_SESSION['school']            ?? '';
$school_id = intval($_SESSION['school_id']  ?? 0);

// If school name is empty, look it up from school_id
if (empty($school) && $school_id > 0) {
    $sr = db_query($conn, "SELECT school_name FROM schools WHERE id = ?", "i", [$school_id]);
    if ($sr && $sr->num_rows > 0) {
        $school = $sr->fetch_assoc()['school_name'];
        $_SESSION['school'] = $school;
    }
}

// If essential session data is missing, redirect to login
if (empty($email) || empty($name)) {
    session_destroy();
    header("Location: login");
    exit();
}

// Get theme settings
$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE email = '$email'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#007bff";
    $logoPath = "img/Find&R.png";
}

// Handle date filtering
$start_date    = $_POST['start_date']    ?? '';
$end_date      = $_POST['end_date']      ?? '';
$status_filter = $_POST['status_filter'] ?? '';

// Get announcements with filtering
$announcements = [];
$filter_applied = false;

try {
    $params = [$school];
    $types  = "s";
    $base_sql = "SELECT *, currentDate AS dateOnly FROM announcements WHERE school = ?";

    if (!empty($start_date)) {
        $base_sql .= " AND currentDate >= ?";
        $params[]  = $start_date;
        $types    .= "s";
        $filter_applied = true;
    }
    if (!empty($end_date)) {
        $base_sql .= " AND currentDate <= ?";
        $params[]  = $end_date;
        $types    .= "s";
        $filter_applied = true;
    }
    if ($status_filter === 'active') {
        // Active = expiry_date is in the future, OR expiry_date is empty/null but currentDate is today or future
        $base_sql .= " AND (
            (expiry_date IS NOT NULL AND expiry_date != '' AND expiry_date >= CURDATE())
            OR
            (expiry_date IS NULL OR expiry_date = '')
        )";
        // Exclude ones where the currentDate itself is old (more than 30 days) and no expiry set
        $base_sql .= " AND NOT (
            (expiry_date IS NULL OR expiry_date = '') AND currentDate < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        )";
        $filter_applied = true;
    } elseif ($status_filter === 'expired') {
        // Expired = expiry_date is in the past (and not empty)
        // OR no expiry but currentDate is older than 30 days
        $base_sql .= " AND (
            (expiry_date IS NOT NULL AND expiry_date != '' AND expiry_date < CURDATE())
            OR
            ((expiry_date IS NULL OR expiry_date = '') AND currentDate < DATE_SUB(CURDATE(), INTERVAL 30 DAY))
        )";
        $filter_applied = true;
    }
    $base_sql .= " ORDER BY id DESC";

    $result = db_query($conn, $base_sql, $types, $params);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("view_announcement error: " . $e->getMessage());
}

// Get announcement statistics
$totalAnnouncements = 0;
$activeAnnouncements = 0;
$expiredAnnouncements = 0;

try {
    $sql_total = "SELECT COUNT(*) as total FROM announcements WHERE school = '$school'";
    $result_total = $conn->query($sql_total);
    if ($result_total && $result_total->num_rows > 0) {
        $row = $result_total->fetch_assoc();
        $totalAnnouncements = $row['total'];
    }
    
    $sql_active = "SELECT COUNT(*) as total FROM announcements WHERE school = '$school' AND (expiry_date IS NULL OR expiry_date >= CURDATE())";
    $result_active = $conn->query($sql_active);
    if ($result_active && $result_active->num_rows > 0) {
        $row = $result_active->fetch_assoc();
        $activeAnnouncements = $row['total'];
    }
    
    $sql_expired = "SELECT COUNT(*) as total FROM announcements WHERE school = '$school' AND expiry_date < CURDATE()";
    $result_expired = $conn->query($sql_expired);
    if ($result_expired && $result_expired->num_rows > 0) {
        $row = $result_expired->fetch_assoc();
        $expiredAnnouncements = $row['total'];
    }
} catch (Exception $e) {
    // Handle errors gracefully
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>View Announcements - <?php echo $school; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'header_css.php'; ?>
    <style>
        .announcements-container {
            max-width: 1400px;
            margin: 20px auto;
        }
        .stats-container {
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, <?php echo $themeColor; ?>, <?php echo $themeColor; ?>dd);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        .filter-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        .filter-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }
        .filter-header h5 {
            color: #333;
            margin-bottom: 5px;
        }
        .filter-header p {
            color: #666;
            margin: 0;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: <?php echo $themeColor; ?>;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-primary {
            background-color: <?php echo $themeColor; ?>;
            border-color: <?php echo $themeColor; ?>;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: <?php echo $themeColor; ?>;
            border-color: <?php echo $themeColor; ?>;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .table-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .table-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: <?php echo $themeColor; ?>;
            color: white;
            border: none;
            padding: 15px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .table td {
            padding: 15px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f4;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .announcement-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .announcement-content {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #666;
        }
        .announcement-content:hover {
            white-space: normal;
            overflow: visible;
            position: relative;
            z-index: 10;
            background: white;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .announcement-author {
            font-weight: 500;
            color: <?php echo $themeColor; ?>;
        }
        .announcement-date {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .filter-badge {
            background-color: <?php echo $themeColor; ?>;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: 10px;
        }
        .clear-filters {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="page">
        <?php include 'main_header.php'; ?>
        <div class="page-content d-flex align-items-stretch">
            <?php include 'main_sidebar.php'; ?>
            <div class="content-inner w-100">
                <header class="bg-white shadow-sm px-4 py-3 z-index-20">
                    <div class="container-fluid px-0">
                        <h2 class="mb-0 p-1"><i class="fas fa-list-alt me-2"></i>View Announcements</h2>
                        <div class="mt-2">
                            <a href="announcement" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Create New Announcement
                            </a>
                            <a href="user_dashboard" class="btn btn-sm btn-info">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </div>
                    </div>
                </header>
                <div class="container-fluid">
                    <div class="announcements-container">
                        <!-- Statistics Cards -->
                        <div class="stats-container">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-bullhorn fa-2x"></i>
                                            </div>
                                            <div>
                                                <h3><?php echo $totalAnnouncements; ?></h3>
                                                <p>Total Announcements</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-eye fa-2x"></i>
                                            </div>
                                            <div>
                                                <h3><?php echo $activeAnnouncements; ?></h3>
                                                <p>Active Announcements</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-clock fa-2x"></i>
                                            </div>
                                            <div>
                                                <h3><?php echo $expiredAnnouncements; ?></h3>
                                                <p>Expired Announcements</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Section -->
                        <div class="filter-container">
                            <div class="filter-header">
                                <h5><i class="fas fa-filter me-2"></i>Filter Announcements</h5>
                                <p>Filter announcements by date range</p>
                            </div>
                            <form method="post" action="">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="status_filter" class="form-label">Status</label>
                                        <select class="form-select" id="status_filter" name="status_filter">
                                            <option value="">All</option>
                                            <option value="active"   <?php echo $status_filter === 'active'  ? 'selected' : ''; ?>>Active</option>
                                            <option value="expired"  <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end gap-2">
                                        <button type="submit" name="filter" class="btn btn-primary me-2">
                                            <i class="fas fa-search me-2"></i>Filter
                                        </button>
                                        <a href="view_announcement" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Clear
                                        </a>
                                    </div>
                                </div>
                            </form>
                            <?php if ($filter_applied): ?>
                                <div class="mt-3">
                                    <span class="filter-badge">
                                        <i class="fas fa-filter me-1"></i>Filter Applied
                                    </span>
                                    <span class="clear-filters">
                                        <a href="view_announcement" class="text-muted">Clear filters</a>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Announcements Table -->
                        <div class="table-container">
                            <div class="table-header">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Announcements List</h5>
                                <p class="text-muted mb-0">Showing announcements for <?php echo $school; ?></p>
                            </div>
                            
                            <?php if (empty($announcements)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-bullhorn"></i>
                                    <h4>No Announcements Found</h4>
                                    <p><?php echo $filter_applied ? 'No announcements match your filter criteria.' : 'There are currently no announcements for this school.'; ?></p>
                                    <a href="announcement" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Create First Announcement
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Content</th>
                                                <th>Author</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($announcements as $index => $announcement): ?>
                                                <tr>
                                                    <td><strong><?php echo $index + 1; ?></strong></td>
                                                    <td>
                                                        <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="announcement-content" title="<?php echo htmlspecialchars($announcement['content']); ?>">
                                                            <?php echo htmlspecialchars($announcement['content']); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="announcement-author"><?php echo htmlspecialchars($announcement['author']); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="announcement-date">
                                                            <?php echo date('M d, Y', strtotime($announcement['dateOnly'])); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $expiry = $announcement['expiry_date'] ?? '';
                                                        $created = $announcement['currentDate'] ?? $announcement['dateOnly'] ?? '';
                                                        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

                                                        if (!empty($expiry)) {
                                                            // Has expiry date — compare to today
                                                            $status = $expiry >= date('Y-m-d') ? 'Active' : 'Expired';
                                                        } else {
                                                            // No expiry — active if created within 30 days
                                                            $createdDate = date('Y-m-d', strtotime($created));
                                                            $status = $createdDate >= $thirtyDaysAgo ? 'Active' : 'Expired';
                                                        }
                                                        $statusClass = $status === 'Active' ? 'badge bg-success' : 'badge bg-secondary';
                                                        ?>
                                                        <span class="<?php echo $statusClass; ?>"><?php echo $status; ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
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
        
        // Date validation
        document.getElementById('start_date').addEventListener('change', function() {
            const endDate = document.getElementById('end_date');
            if (this.value && endDate.value && this.value > endDate.value) {
                endDate.value = this.value;
            }
        });
        
        document.getElementById('end_date').addEventListener('change', function() {
            const startDate = document.getElementById('start_date');
            if (this.value && startDate.value && this.value < startDate.value) {
                startDate.value = this.value;
            }
        });
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

