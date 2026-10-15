<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php

// Check if user is logged in first
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

// Get session variables with fallbacks
$email = isset($_SESSION['socialWorkerEmail']) ? $_SESSION['socialWorkerEmail'] : '';
$name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$school = isset($_SESSION['school']) ? $_SESSION['school'] : '';

// If essential session data is missing, redirect to login
if (empty($email) || empty($name) || empty($school)) {
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

// Get recent announcements for reference
$recentAnnouncements = [];
try {
    $sql_recent = "SELECT * FROM announcements WHERE school = '$school' ORDER BY created_at DESC LIMIT 5";
    $result_recent = $conn->query($sql_recent);
    if ($result_recent && $result_recent->num_rows > 0) {
        while ($row = $result_recent->fetch_assoc()) {
            $recentAnnouncements[] = $row;
        }
    }
} catch (Exception $e) {
    // Handle errors gracefully
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
    <title>Create Announcement - <?php echo $school; ?></title>
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
        .announcement-container {
            max-width: 1200px;
            margin: 20px auto;
        }
        .form-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .form-header {
            background: linear-gradient(135deg, <?php echo $themeColor; ?>, <?php echo $themeColor; ?>dd);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .form-header h3 {
            margin-bottom: 10px;
            font-weight: 600;
        }
        .form-header p {
            margin: 0;
            opacity: 0.9;
        }
        .form-body {
            padding: 40px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .form-label i {
            margin-right: 8px;
            color: <?php echo $themeColor; ?>;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: <?php echo $themeColor; ?>;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .form-control[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .form-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .btn-primary {
            background-color: <?php echo $themeColor; ?>;
            border-color: <?php echo $themeColor; ?>;
            padding: 12px 30px;
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
        .recent-announcements {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .recent-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .recent-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f3f4;
            transition: background-color 0.3s ease;
        }
        .recent-item:hover {
            background-color: #f8f9fa;
        }
        .recent-item:last-child {
            border-bottom: none;
        }
        .recent-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .recent-meta {
            font-size: 12px;
            color: #6c757d;
        }
        .character-count {
            font-size: 12px;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }
        .character-count.warning {
            color: #ffc107;
        }
        .character-count.danger {
            color: #dc3545;
        }
        .form-tips {
            background-color: #e8f4fd;
            border-left: 4px solid <?php echo $themeColor; ?>;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-tips h6 {
            color: <?php echo $themeColor; ?>;
            margin-bottom: 10px;
        }
        .form-tips ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .form-tips li {
            color: #495057;
            margin-bottom: 5px;
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
                        <h2 class="mb-0 p-1"><i class="fas fa-bullhorn me-2"></i>Create Announcement</h2>
                        <div class="mt-2">
                            <a href="view_announcement" class="btn btn-sm btn-primary">
                                <i class="fas fa-list me-1"></i>View Announcements
                            </a>
                            <a href="user_dashboard" class="btn btn-sm btn-info">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </div>
                    </div>
                </header>
                <div class="container-fluid">
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Announcement posted successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $_GET['error'] === 'missing' ? 'Please fill in all required fields.' : 'Error saving announcement. Please try again.'; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <div class="announcement-container">
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

                        <div class="row">
                            <!-- Announcement Form -->
                            <div class="col-lg-8">
                                <div class="form-container">
                                    <div class="form-header">
                                        <h3><i class="fas fa-bullhorn me-2"></i>Create New Announcement</h3>
                                        <p>Share important information with parents and staff</p>
                                    </div>
                                    <div class="form-body">
                                        <div class="form-tips">
                                            <h6><i class="fas fa-lightbulb me-2"></i>Tips for Effective Announcements:</h6>
                                            <ul>
                                                <li>Keep titles clear and concise</li>
                                                <li>Use bullet points for better readability</li>
                                                <li>Include relevant dates and contact information</li>
                                                <li>Set appropriate expiry dates for time-sensitive announcements</li>
                                            </ul>
                                        </div>
                                        
                                        <form action="process_announcement" method="post" id="announcementForm">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="school" class="form-label">
                                                            <i class="fas fa-school"></i>School
                                                        </label>
                                                        <input type="text" class="form-control" id="school" name="school" value="<?php echo htmlspecialchars($school); ?>" readonly>
                                                        <div class="form-text">This announcement will be posted for your school</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="author" class="form-label">
                                                            <i class="fas fa-user"></i>Author
                                                        </label>
                                                        <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($name); ?>" required>
                                                        <div class="form-text">Your name will appear as the author</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="title" class="form-label">
                                                    <i class="fas fa-heading"></i>Announcement Title
                                                </label>
                                                <input type="text" class="form-control" id="title" name="title" placeholder="Enter announcement title..." required maxlength="100">
                                                <div class="character-count" id="titleCount">0/100 characters</div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="content" class="form-label">
                                                    <i class="fas fa-align-left"></i>Announcement Content
                                                </label>
                                                <textarea class="form-control" id="content" name="content" rows="6" placeholder="Enter announcement content..." required maxlength="1000"></textarea>
                                                <div class="character-count" id="contentCount">0/1000 characters</div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="expiry_date" class="form-label">
                                                    <i class="fas fa-calendar-alt"></i>Expiry Date (Optional)
                                                </label>
                                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" min="<?php echo date('Y-m-d'); ?>">
                                                <div class="form-text">Leave empty if the announcement has no expiry date</div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <a href="view_announcement" class="btn btn-secondary">
                                                    <i class="fas fa-arrow-left me-2"></i>Cancel
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane me-2"></i>Publish Announcement
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Announcements -->
                            <div class="col-lg-4">
                                <div class="recent-announcements">
                                    <div class="recent-header">
                                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Announcements</h5>
                                        <p class="text-muted mb-0">Your latest announcements</p>
                                    </div>
                                    <?php if (empty($recentAnnouncements)): ?>
                                        <div class="recent-item text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>No announcements yet</p>
                                            <small>Create your first announcement!</small>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recentAnnouncements as $announcement): ?>
                                            <div class="recent-item">
                                                <div class="recent-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                                <div class="recent-meta">
                                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($announcement['author']); ?>
                                                    <br>
                                                    <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($announcement['created_at'])); ?>
                                                    <?php if ($announcement['expiry_date']): ?>
                                                        <br>
                                                        <i class="fas fa-clock me-1"></i>Expires: <?php echo date('M d, Y', strtotime($announcement['expiry_date'])); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
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
        
        // Character count functionality
        document.getElementById('title').addEventListener('input', function() {
            const count = this.value.length;
            const maxLength = 100;
            const countElement = document.getElementById('titleCount');
            
            countElement.textContent = count + '/' + maxLength + ' characters';
            
            if (count > maxLength * 0.8) {
                countElement.className = 'character-count warning';
            } else if (count > maxLength * 0.9) {
                countElement.className = 'character-count danger';
            } else {
                countElement.className = 'character-count';
            }
        });
        
        document.getElementById('content').addEventListener('input', function() {
            const count = this.value.length;
            const maxLength = 1000;
            const countElement = document.getElementById('contentCount');
            
            countElement.textContent = count + '/' + maxLength + ' characters';
            
            if (count > maxLength * 0.8) {
                countElement.className = 'character-count warning';
            } else if (count > maxLength * 0.9) {
                countElement.className = 'character-count danger';
            } else {
                countElement.className = 'character-count';
            }
        });
        
        // Form validation
        document.getElementById('announcementForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            if (title.length < 5) {
                e.preventDefault();
                alert('Please enter a title with at least 5 characters.');
                return false;
            }
            
            if (content.length < 10) {
                e.preventDefault();
                alert('Please enter content with at least 10 characters.');
                return false;
            }
        });
        
        // Set minimum date for expiry date
        document.getElementById('expiry_date').min = new Date().toISOString().split('T')[0];
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

