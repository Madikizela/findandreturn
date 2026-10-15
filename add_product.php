<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('connection.php');

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

$email = $_SESSION['socialWorkerEmail'] ?? $_SESSION['email'] ?? '';
$name = $_SESSION['user_name'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
$school_id = $_SESSION['school_id'] ?? 0;

// Get school name from schools table
$school_stmt = $conn->prepare("SELECT school_name FROM schools WHERE id = ?");
$school_stmt->bind_param("i", $school_id);
$school_stmt->execute();
$school_res = $school_stmt->get_result();
if ($school_row = $school_res->fetch_assoc()) {
    $school = $school_row['school_name'];
} else {
    $school = "";
}
$school_stmt->close();

// Get theme settings securely
$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE email = ?";
$result = db_query($conn, $sql, "s", [$email]);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#007bff";
    $logoPath = "img/Find&R.png";
}

// Handle form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $name = $_POST['name'];
    $code = $_POST['code'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $size = $_POST['size'];
    
    // Handle image upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            $message = "Error: Unable to create upload directory. Please contact the administrator.";
        }
    }
    if (!is_writable($target_dir)) {
        $message = "Error: Upload directory is not writable. Please contact the administrator.";
    }

    if (empty($message)) {
    $image = $_FILES["image"]["name"];
    $imageFileType = strtolower(pathinfo($image, PATHINFO_EXTENSION));
    
    // Validate image
    $valid_extensions = array("jpg", "jpeg", "png", "gif");
    if (!in_array($imageFileType, $valid_extensions)) {
        $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    } else {
        // Use unique filename to prevent directory traversal and overwrites
        $new_image_name = uniqid('prod_', true) . '.' . $imageFileType;
        $target_file = $target_dir . $new_image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Insert into tblproduct table
            $sql = "INSERT INTO tblproduct (name, code, image, price, quantity, size, school_admin_email, school) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$name, $code, $target_file, $price, $quantity, $size, $email, $school];
            
            if (db_query($conn, $sql, "sssdisss", $params)) {
                $message = "Product added successfully!";
            } else {
                $message = "Error adding product.";
            }
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    }
    } // end if empty message
}

// Function to generate reference number
function generateReferenceNumber() {
    $prefix = 'ORD';
    $timestamp = date('YmdHis');
    $random = strtoupper(substr(md5(uniqid()), 0, 6));
    return $prefix . $timestamp . $random;
}

// Function to send order confirmation email
function sendOrderEmail($customerEmail, $referenceNumber, $orderDetails, $totalAmount) {
    $to = $customerEmail;
    $subject = "Order Confirmation - Reference: " . $referenceNumber;
    
    $from = 'no-reply@findandreturn.co.za';
    $fromName = 'Find and Return Store';
    
    $htmlContent = '
    <html>
    <head>
        <title>Order Confirmation</title>
    </head>
    <body>
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
            <h2 style="color: #333;">Order Confirmation</h2>
            <p>Thank you for your order!</p>
            
            <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h3 style="margin-top: 0;">Reference Number: <span style="color: #007bff; font-weight: bold;">' . $referenceNumber . '</span></h3>
                <p><strong>Total Amount:</strong> R' . number_format($totalAmount, 2) . '</p>
                <p><strong>Order Date:</strong> ' . date('Y-m-d H:i:s') . '</p>
            </div>
            
            <div style="margin: 20px 0;">
                <h4>Order Details:</h4>
                ' . $orderDetails . '
            </div>
            
            <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4 style="margin-top: 0; color: #856404;">Important:</h4>
                <p>Please keep this reference number safe. You will need to present it when collecting your order.</p>
            </div>
            
            <p>If you have any questions, please contact us.</p>
            
            <hr style="margin: 30px 0;">
            <p style="color: #666; font-size: 12px;">This is an automated email from Find and Return Store.</p>
        </div>
    </body>
    </html>';
    
    $headers = "From: $fromName <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $htmlContent, $headers);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Product - <?php echo h($school); ?></title>
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
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            border-color: <?php echo $themeColor; ?>;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .form-control[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
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
        .action-buttons {
            margin-top: 30px;
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #f8f9fa;
        }
        .btn-view-orders {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-left: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-view-orders:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .file-upload-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .file-upload-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        .file-upload-label {
            display: block;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .file-upload-label:hover {
            border-color: <?php echo $themeColor; ?>;
            background-color: rgba(0, 123, 255, 0.05);
        }
        .file-upload-label i {
            margin-right: 8px;
            color: <?php echo $themeColor; ?>;
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
                        <h2 class="mb-0 p-1"><i class="fas fa-plus-circle me-2"></i>Add New Product</h2>
                        <div class="mt-2">
                            <a href="view_orders" class="btn btn-sm btn-primary">
                                <i class="fas fa-shopping-cart me-1"></i>View Orders
                            </a>
                            <a href="inventory_status" class="btn btn-sm btn-info">
                                <i class="fas fa-boxes me-1"></i>Inventory Status
                            </a>
                        </div>
                    </div>
                </header>
                <div class="container-fluid">
                    <div class="form-container">
                        <div class="form-header">
                            <h3><i class="fas fa-plus-circle me-2"></i>Add New Product</h3>
                            <p>Register a new item for the <?php echo h($school); ?> marketplace</p>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                                <i class="fas <?php echo strpos($message, 'successfully') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                                <?php echo h($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-tag me-1"></i>Product Name</label>
                                        <input type="text" name="name" class="form-control" required placeholder="e.g. School Blazer">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-barcode me-1"></i>Product Code</label>
                                        <input type="text" name="code" class="form-control" required placeholder="e.g. BLAZ-001">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-money-bill-wave me-1"></i>Price (R)</label>
                                        <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-layer-group me-1"></i>Available Quantity</label>
                                        <input type="number" name="quantity" class="form-control" required placeholder="0">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-ruler me-1"></i>Size/Variant</label>
                                        <input type="text" name="size" class="form-control" placeholder="e.g. Medium, 32, etc.">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-image me-1"></i>Product Image</label>
                                        <div class="file-upload-wrapper">
                                            <label for="image" class="file-upload-label" id="file-label">
                                                <i class="fas fa-cloud-upload-alt me-2"></i>Choose Image
                                            </label>
                                            <input type="file" name="image" id="image" accept="image/*" required onchange="updateFileName(this)">
                                        </div>
                                        <small class="text-muted">Allowed: JPG, PNG, GIF (Max 2MB)</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-user-tie me-1"></i>School Admin</label>
                                        <input type="text" class="form-control" value="<?php echo $email; ?>" readonly>
                                        <small class="form-text text-muted">This product will be associated with your school admin account.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label"><i class="fas fa-school me-1"></i>School</label>
                                        <input type="text" class="form-control" value="<?php echo $school; ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus-circle me-2"></i>Add Product
                                </button>
                            </div>
                        </form>
                        
                        <div class="action-buttons">
                            <a href="view_orders" class="btn-view-orders">
                                <i class="fas fa-shopping-cart me-2"></i>View Orders
                            </a>
                            <a href="inventory_status" class="btn-view-orders" style="background-color: #17a2b8;">
                                <i class="fas fa-boxes me-2"></i>Inventory Status
                            </a>
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
        
        // File upload preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const label = document.querySelector('.file-upload-label');
            
            if (file) {
                label.innerHTML = `<i class="fas fa-check-circle"></i>Selected: ${file.name}`;
                label.style.borderColor = '#28a745';
                label.style.backgroundColor = '#d4edda';
            }
        });
    </script>
</body>
</html>
