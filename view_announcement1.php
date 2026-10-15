<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php

$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
$selectedSchool = $_SESSION['school'] ?? '';
$school_id = $_SESSION['school_id'] ?? 0;

// Handle school selection from URL parameter (optional, for consistency with other pages)
if (isset($_GET['school_id'])) {
    $selectedSchoolId = intval($_GET['school_id']);
    $_SESSION['school_id'] = $selectedSchoolId;
    $school_id = $selectedSchoolId;
    
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
$stmt = $conn->prepare("SELECT logo_path, theme_color FROM admin_settings WHERE school = ?");
$stmt->bind_param("s", $selectedSchool);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#007bff";
    $logoPath = "img/Find&R.png";
}
$stmt->close();

// Prepare filtered announcements
$announcements = [];

// Base SQL query to select all announcements for the school
$sql = "SELECT *, DATE(currentDate) AS dateOnly FROM announcements WHERE school = ? ORDER BY currentDate DESC";

// Handle filter
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$params = [$selectedSchool];
$types = "s";

if (isset($_POST['filter'])) {
    if (!empty($start_date)) {
        $sql .= " AND DATE(currentDate) >= ?";
        $params[] = date('Y-m-d', strtotime($start_date));
        $types .= "s";
    }
    
    if (!empty($end_date)) {
        $sql .= " AND DATE(currentDate) <= ?";
        $params[] = date('Y-m-d', strtotime($end_date));
        $types .= "s";
    }
}

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Announcements - Find and Return</title>
    <meta name="description" content="">
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
    <?php include 'header_css.php'; ?>
    <style>
        :root {
            --primary-color: <?php echo $themeColor; ?>;
        }
        nav:first-child {
            background-color: <?php echo $themeColor; ?>;
        }
        .filter-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .announcement-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 20px;
            border-left: 5px solid var(--primary-color);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .announcement-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .announcement-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }
        .announcement-date {
            font-size: 0.9rem;
            color: #6c757d;
            background-color: #e9ecef;
            padding: 4px 12px;
            border-radius: 20px;
        }
        .announcement-meta {
            display: flex;
            gap: 15px;
            font-size: 0.95rem;
            color: #6c757d;
            margin-bottom: 12px;
        }
        .announcement-meta span i {
            margin-right: 6px;
            color: var(--primary-color);
        }
        .announcement-content {
            line-height: 1.7;
            color: #4a5568;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <div class="page">
        <?php include 'main_header.php'; ?>
        <div class="page-content d-flex align-items-stretch">
            <?php include 'school_sidebar.php'; ?>
            <div class="content-inner w-100">
                <header class="bg-white shadow-sm px-4 py-3 z-index-20">
                    <div class="container-fluid px-0 d-flex justify-content-between align-items-center">
                        <h2 class="mb-0 p-1"><i class="fas fa-bullhorn me-2"></i>Announcements</h2>
                        <?php if ($_SESSION['role'] == 'user'): ?>
                            <a href="announcement" class="btn btn-primary" style="background-color: <?php echo $themeColor; ?>; border-color: <?php echo $themeColor; ?>;">
                                <i class="fas fa-plus me-2"></i>Make Announcement
                            </a>
                        <?php endif; ?>
                    </div>
                </header>
                <!-- Breadcrumb-->
                <div class="bg-white">
                    <div class="container-fluid">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 py-3">
                                <?php if ($_SESSION['role'] == 'parent'): ?>
                                    <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item"><a class="fw-light" href="user_dashboard">Dashboard</a></li>
                                <?php endif; ?>
                                <li class="breadcrumb-item active fw-light" aria-current="page">Announcements</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <div class="container-fluid">
                    <!-- Filter Form -->
                    <div class="filter-container">
                        <form method="post" action="" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label fw-medium"><i class="fas fa-calendar-alt me-2"></i>Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label fw-medium"><i class="fas fa-calendar-check me-2"></i>End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="filter" class="btn btn-primary w-100" style="background-color: <?php echo $themeColor; ?>; border-color: <?php echo $themeColor; ?>;">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="view_announcement1.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-redo me-2"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Announcements List -->
                    <?php if (count($announcements) > 0): ?>
                        <?php foreach ($announcements as $index => $announcement): ?>
                            <div class="announcement-card">
                                <div class="announcement-header">
                                    <h3 class="announcement-title"><?php echo htmlspecialchars($announcement["title"]); ?></h3>
                                    <span class="announcement-date">
                                        <i class="far fa-calendar me-1"></i>
                                        <?php echo date('M d, Y', strtotime($announcement["dateOnly"])); ?>
                                    </span>
                                </div>
                                <div class="announcement-meta">
                                    <span><i class="fas fa-school"></i><?php echo htmlspecialchars($announcement["school"]); ?></span>
                                    <span><i class="fas fa-user-circle"></i><?php echo htmlspecialchars($announcement["author"]); ?></span>
                                </div>
                                <div class="announcement-content">
                                    <?php echo nl2br(htmlspecialchars($announcement["content"])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bullhorn"></i>
                            <h4>No Announcements Found</h4>
                            <p>There are no announcements available for this period.</p>
                        </div>
                    <?php endif; ?>
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
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
</body>
</html>
