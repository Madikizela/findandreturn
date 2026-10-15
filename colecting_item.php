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

// Initialize variables
$id = $first_name = $surname = $parent_email = $child_phoneNo = $parent_phoneNo = $p_address = $school_name = $grade = $collectedBy = $collectedByContact = "";
$message = "";
$messageType = "";
$searchPerformed = false;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $searchPerformed = true;
        $parent_email = trim($_POST['parent_email']);
        $first_name = trim($_POST['first_name']);

        if (empty($parent_email) || empty($first_name)) {
            $message = "Please enter both parent email and child name.";
            $messageType = "danger";
        } else {
            // Search children who have a found item at this school admin desk
            // Only items with tracking_status = 'found' are ready for collection
            $school_id = $_SESSION['school_id'] ?? 0;
            $sql = "SELECT c.*, s.school_name as school, u.phone_no as parent_phoneNo,
                           u.alternative_phone_no as child_phoneNo, u.email as parent_email,
                           u.address as p_address,
                           nli.id as item_id, nli.item_name, nli.unique_report_id
                    FROM children c
                    LEFT JOIN schools s ON c.school_id = s.id
                    LEFT JOIN users u ON c.parent_id = u.id
                    INNER JOIN new_lost_items nli ON nli.child_id = c.id
                    WHERE u.email = ?
                      AND c.first_name LIKE ?
                      AND c.school_id = ?
                      AND c.status = 'active'
                      AND nli.school_id = ?
                      AND nli.tracking_status = 'found'
                    ORDER BY nli.created_at DESC";
            $result = db_query($conn, $sql, "ssii", [$parent_email, "%$first_name%", $school_id, $school_id]);

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $_SESSION['parent_email'] = $parent_email;
                $_SESSION['item_id']      = $row['item_id']; // store item to mark as collected
                $id               = $row['id'];
                $first_name       = $row['first_name'];
                $surname          = $row['surname'];
                $parent_email     = $row['parent_email'];
                $child_phoneNo    = $row['child_phoneNo'] ?? '';
                $parent_phoneNo   = $row['parent_phoneNo'] ?? '';
                $p_address        = $row['p_address'] ?? '';
                $school_name      = $row['school'] ?? '';
                $grade            = $row['grade'] ?? '';
                $item_name        = $row['item_name'] ?? '';
                $item_id          = $row['item_id'] ?? 0;

                $message = "Child found! Item: <strong>" . h($item_name) . "</strong> is ready for collection.";
                $messageType = "success";
            } else {
                // Check if child exists but has no found items
                $checkChild = db_query($conn,
                    "SELECT c.id FROM children c
                     LEFT JOIN users u ON c.parent_id = u.id
                     WHERE u.email = ? AND c.first_name LIKE ? AND c.school_id = ? AND c.status = 'active'",
                    "ssi", [$parent_email, "%$first_name%", $school_id]
                );
                if ($checkChild && $checkChild->num_rows > 0) {
                    $message = "Child found but has no items currently at the school admin desk ready for collection.";
                } else {
                    $message = "No results found. Please check the parent email and child name.";
                }
                $messageType = "danger";
            }
        }
    }
}

// Get collection statistics
$totalCollections = 0;
$todayCollections = 0;
$pendingCollections = 0;

try {
    $school_id = $_SESSION['school_id'] ?? 0;

    $r1 = db_query($conn, "SELECT COUNT(*) as total FROM children WHERE school_id = ?", "i", [$school_id]);
    if ($r1) $totalCollections = $r1->fetch_assoc()['total'];

    $r2 = db_query($conn, "SELECT COUNT(*) as total FROM children WHERE school_id = ? AND DATE(created_at) = CURDATE()", "i", [$school_id]);
    if ($r2) $todayCollections = $r2->fetch_assoc()['total'];

    $r3 = db_query($conn, "SELECT COUNT(*) as total FROM children WHERE school_id = ? AND status = 'active'", "i", [$school_id]);
    if ($r3) $pendingCollections = $r3->fetch_assoc()['total'];
} catch (Exception $e) {
    // Handle errors gracefully
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Parent Collection - <?php echo $school; ?></title>
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
        .collection-container {
            max-width: 1200px;
            margin: 20px auto;
        }
        .stats-container {
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-color)dd);
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
        .form-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }
        .form-header h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .form-header p {
            color: #666;
            margin: 0;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .form-control[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 12px 30px;
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
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .search-section {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .collection-section {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }
        .form-section-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f8f9fa;
        }
        .info-card {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-card h6 {
            color: #1976d2;
            margin-bottom: 10px;
        }
        .info-card p {
            color: #424242;
            margin: 0;
            font-size: 0.9rem;
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
                        <h2 class="mb-0 p-1"><i class="fas fa-users me-2"></i>Parent Collection</h2>
                        <div class="mt-2">
                            <a href="collected_items" class="btn btn-sm btn-primary">
                                <i class="fas fa-list me-1"></i>View Collected Items
                            </a>
                            <a href="user_dashboard" class="btn btn-sm btn-info">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </div>
                    </div>
                </header>
                <div class="container-fluid">
                    <div class="collection-container">
                        <!-- Statistics Cards -->
                        <div class="stats-container">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-users fa-2x"></i>
                                            </div>
                                            <div>
                                                <h3><?php echo $totalCollections; ?></h3>
                                                <p>Total Collections</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-calendar-day fa-2x"></i>
                                            </div>
                                            <div>
                                                <h3><?php echo $todayCollections; ?></h3>
                                                <p>Today's Collections</p>
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
                                                <h3><?php echo $pendingCollections; ?></h3>
                                                <p>Pending Collections</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Section -->
                        <div class="search-section">
                            <div class="form-header">
                                <h3><i class="fas fa-search me-2"></i>Search for Child</h3>
                                <p>Enter parent email and child name to find collection details</p>
                            </div>
                            
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                    <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                                    <?php echo $message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="parent_email" class="form-label required-field">
                                                <i class="fas fa-envelope me-1"></i>Parent Email
                                            </label>
                                            <input type="email" class="form-control" id="parent_email" name="parent_email" 
                                                   value="<?php echo htmlspecialchars($parent_email); ?>" required 
                                                   placeholder="Enter parent email address">
                                            <div class="invalid-feedback">
                                                Please enter a valid email address.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="first_name" class="form-label required-field">
                                                <i class="fas fa-child me-1"></i>Child Name
                                            </label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                                   value="<?php echo htmlspecialchars($first_name); ?>" required 
                                                   placeholder="Enter child's first name">
                                            <div class="invalid-feedback">
                                                Please enter the child's name.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" name="search" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Search Child
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Collection Form Section -->
                        <?php if ($searchPerformed && !empty($id)): ?>
                            <div class="collection-section">
                                <div class="form-header">
                                    <h3><i class="fas fa-handshake me-2"></i>Collection Confirmation</h3>
                                    <p>Complete the collection details below</p>
                                </div>
                                
                                <div class="info-card">
                                    <h6><i class="fas fa-info-circle me-2"></i>Collection Information</h6>
                                    <p>Please verify all child details and complete the collection information. This will mark the item as collected.</p>
                                </div>
                                
                                <form method="POST" action="collect" class="needs-validation" novalidate>
                                    <input type="hidden" name="item_id" value="<?php echo intval($item_id ?? 0); ?>">
                                    <!-- Child Information (Read-only) -->
                                    <h5 class="form-section-title"><i class="fas fa-child me-2"></i>Child Information</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">ID</label>
                                                <input type="text" class="form-control" name="id" value="<?php echo htmlspecialchars($id); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">First Name</label>
                                                <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Surname</label>
                                                <input type="text" class="form-control" name="surname" value="<?php echo htmlspecialchars($surname); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Grade</label>
                                                <input type="text" class="form-control" name="grade" value="<?php echo htmlspecialchars($grade); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label"><i class="fas fa-box-open me-1 text-success"></i>Item to Collect</label>
                                                <input type="text" class="form-control fw-bold text-success" name="item_name" value="<?php echo htmlspecialchars($item_name ?? ''); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">School</label>
                                                <input type="text" class="form-control" name="school" value="<?php echo htmlspecialchars($school_name); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Parent Email</label>
                                                <input type="email" class="form-control" name="parent_email" value="<?php echo htmlspecialchars($parent_email); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Child Phone Number</label>
                                                <input type="text" class="form-control" name="child_phoneNo" value="<?php echo htmlspecialchars($child_phoneNo); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Parent Phone Number</label>
                                                <input type="text" class="form-control" name="parent_phoneNo" value="<?php echo htmlspecialchars($parent_phoneNo); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Physical Address</label>
                                                <input type="text" class="form-control" name="p_address" value="<?php echo htmlspecialchars($p_address); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Collection Information -->
                                    <h5 class="form-section-title"><i class="fas fa-user-check me-2"></i>Collection Information</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="collectedBy" class="form-label required-field">
                                                    <i class="fas fa-user me-1"></i>Collecting Person Name
                                                </label>
                                                <input type="text" class="form-control" id="collectedBy" name="collectedBy" required 
                                                       placeholder="Enter the name of the person collecting the item">
                                                <div class="invalid-feedback">
                                                    Please enter the collecting person's name.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="collectedByContact" class="form-label required-field">
                                                    <i class="fas fa-phone me-1"></i>Collecting Person Contact
                                                </label>
                                                <input type="text" class="form-control" id="collectedByContact" name="collectedByContact" required 
                                                       placeholder="Enter contact number of the collecting person">
                                                <div class="invalid-feedback">
                                                    Please enter the collecting person's contact number.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <button type="submit" name="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-check-circle me-2"></i>Confirm Collection
                                        </button>
                                        <button type="reset" class="btn btn-secondary btn-lg ms-3">
                                            <i class="fas fa-undo me-2"></i>Reset Form
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
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
        
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
        // Auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Email validation
        document.getElementById('parent_email').addEventListener('blur', function() {
            var email = this.value;
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email)) {
                this.setCustomValidity('Please enter a valid email address');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

