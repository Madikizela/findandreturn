<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php

$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
$school = $_SESSION['school'];

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
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

// Handle form submission
$error = null;
$success = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $qrImage = $_POST['qrImage'];
    if (!empty($qrImage)) {
        $_SESSION['qrImage'] = $qrImage;
        $success = "QR Code scanned successfully! Redirecting...";
        echo "<script>
            setTimeout(function() {
                window.location.href = 'index3.php';
            }, 1500);
        </script>";
    } else {
        $error = "Please scan a QR code first.";
    }
}

// Get lost items count for notification
$lostItemsCount = 0;
try {
    $sql_lost = "SELECT COUNT(*) as total FROM lost_items WHERE school = '$school'";
    $result_lost = $conn->query($sql_lost);
    if ($result_lost && $result_lost->num_rows > 0) {
        $row = $result_lost->fetch_assoc();
        $lostItemsCount = $row['total'];
    }
} catch (Exception $e) {
    $lostItemsCount = 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Scan QR Code - <?php echo $school; ?></title>
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
        .scan-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .scan-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }
        .scan-header h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .scan-header p {
            color: #666;
            margin: 0;
        }
        .qr-scanner-container {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px dashed #dee2e6;
        }
        .qr-scanner-container:hover {
            border-color: var(--primary-color);
        }
        #reader {
            width: 100% !important;
            max-width: 500px;
            margin: 0 auto;
            display: block;
        }
        .scan-result-container {
            background-color: #e8f4fd;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid var(--primary-color);
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
        .btn-primary:disabled {
            background-color: #6c757d;
            border-color: #6c757d;
            transform: none;
            box-shadow: none;
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
        .notification-badge {
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            position: absolute;
            top: -5px;
            right: -5px;
        }
        .scan-instructions {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .scan-instructions h5 {
            color: #856404;
            margin-bottom: 10px;
        }
        .scan-instructions ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .scan-instructions li {
            color: #856404;
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
                        <h2 class="mb-0 p-1"><i class="fas fa-qrcode me-2"></i>QR Code Scanner</h2>
                        <div class="mt-2">
                            <a href="user_dashboard" class="btn btn-sm btn-primary">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                            <a href="view_lost_items" class="btn btn-sm btn-info">
                                <i class="fas fa-search me-1"></i>View Lost Items
                            </a>
                        </div>
                    </div>
                </header>
                <div class="container-fluid">
                    <div class="scan-container">
                        <div class="scan-header">
                            <h3><i class="fas fa-qrcode me-2"></i>Scan QR Code to Confirm Item</h3>
                            <p>Use the scanner below to scan QR codes on found items for confirmation</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="scan-instructions">
                            <h5><i class="fas fa-info-circle me-2"></i>How to use the QR Scanner:</h5>
                            <ul>
                                <li>Allow camera access when prompted</li>
                                <li>Point the camera at the QR code on the item</li>
                                <li>Hold steady until the code is scanned</li>
                                <li>Click "Submit Scan" to proceed with confirmation</li>
                            </ul>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="qr-scanner-container">
                                <div class="text-center mb-3">
                                    <h5><i class="fas fa-camera me-2"></i>QR Code Scanner</h5>
                                    <p class="text-muted">Position the QR code within the scanner frame</p>
                                </div>
                                <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
                            </div>
                            
                            <div class="scan-result-container">
                                <h5><i class="fas fa-clipboard-check me-2"></i>Scan Result or Manual Entry</h5>
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" id="result" name="qrImage"
                                               placeholder="QR code will appear here — or type it manually..."
                                               oninput="document.getElementById('submit').disabled = this.value.trim() === '';">
                                        <small class="text-muted mt-1 d-block">
                                            <i class="fas fa-keyboard me-1"></i>You can also type the reference number directly
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100" id="submit" name="submit" disabled>
                                            <i class="fas fa-paper-plane me-2"></i>Submit Scan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="html5-qrcode.min.js"></script>
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
        
        // QR Code Scanner
        function onScanSuccess(qrCodeMessage) {
            document.getElementById('result').value = qrCodeMessage;
            document.getElementById('submit').disabled = false;
            
            // Show success feedback
            const resultInput = document.getElementById('result');
            resultInput.style.borderColor = '#28a745';
            resultInput.style.backgroundColor = '#d4edda';
            
            // Auto-submit after 2 seconds
            setTimeout(function() {
                document.getElementById('submit').click();
            }, 2000);
        }
        
        function onScanError(errorMessage) {
            // Handle scan error silently
            console.log('Scan error:', errorMessage);
        }
        
        // Initialize QR Scanner
        var html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            }
        );
        html5QrcodeScanner.render(onScanSuccess, onScanError);
        
        // Enable submit button when QR code is entered manually
        document.getElementById('result').addEventListener('input', function() {
            const submitBtn = document.getElementById('submit');
            submitBtn.disabled = this.value.trim() === '';
        });
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

