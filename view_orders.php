<?php
include('connection.php');

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

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#007bff";
    $logoPath = "img/Find&R.png";
}

// Handle order status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id  = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];

    // Fetch order details before updating so we can notify the customer
    $orderRow = db_query($conn,
        "SELECT reference_number, customer_email, total_price FROM orders WHERE id = ? AND school = ?",
        "is", [$order_id, $school]
    );

    $update_sql = "UPDATE orders SET status = ? WHERE id = ? AND school = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sis", $new_status, $order_id, $school);

    if ($stmt->execute()) {
        $status_message = "Order status updated successfully!";

        // ── Notify customer by email and WhatsApp ──────────────────────
        if ($orderRow && $orderRow->num_rows > 0) {
            $ord = $orderRow->fetch_assoc();
            $ref   = $ord['reference_number'];
            $cemail = $ord['customer_email'];
            $total  = number_format($ord['total_price'], 2);

            // Look up customer phone from users table
            $phoneRow = db_query($conn, "SELECT phone_no FROM users WHERE email = ? LIMIT 1", "s", [$cemail]);
            $cphone = '';
            if ($phoneRow && $phoneRow->num_rows > 0) {
                $cphone = $phoneRow->fetch_assoc()['phone_no'] ?? '';
            }

            // Build status label
            $statusLabel = ucfirst($new_status);

            // Email notification
            $subject     = "Order Update - $ref | $school";
            $htmlContent = '
            <div style="max-width:600px;margin:0 auto;padding:20px;font-family:Arial,sans-serif;">
                <h2 style="color:#333;">Order Status Update</h2>
                <p>Hello,</p>
                <p>Your order <strong>' . $ref . '</strong> from <strong>' . htmlspecialchars($school) . '</strong> has been updated.</p>
                <div style="background:#f4f4f4;padding:15px;border-radius:8px;margin:20px 0;">
                    <p><strong>Reference:</strong> ' . $ref . '</p>
                    <p><strong>New Status:</strong> <span style="color:#28a745;font-weight:bold;">' . $statusLabel . '</span></p>
                    <p><strong>Total:</strong> R' . $total . '</p>
                </div>
                ' . ($new_status === 'completed' ? '<p style="color:#28a745;">Your order is ready for collection. Please bring your reference number.</p>' : '') . '
                <p>Kind Regards,<br><strong>' . htmlspecialchars($school) . '</strong></p>
            </div>';
            send_email($cemail, $subject, $htmlContent);

            // WhatsApp notification
            if (!empty($cphone)) {
                $wa_phone = preg_replace('/[^0-9]/', '', $cphone);
                if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
                    $wa_phone = '27' . substr($wa_phone, 1);
                }
                // Free-form message — works within 24h window
                $waMsg = "Hello! Your order $ref from $school has been updated to: $statusLabel."
                       . ($new_status === 'completed' ? " Your order is ready for collection. Please bring your reference number." : "")
                       . " Total: R$total";
                send_whatsapp($wa_phone, $waMsg);
            }
        }
        // ──────────────────────────────────────────────────────────────
    } else {
        $status_message = "Error updating order status: " . $conn->error;
    }
    $stmt->close();
}

// Ensure orders status enum includes 'collected'
$conn->query("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','confirmed','completed','cancelled','collected') DEFAULT 'pending'");

// Ensure collection columns exist on orders table
foreach (['collected_by VARCHAR(255)', 'collected_id VARCHAR(100)', 'collected_at DATETIME'] as $col) {
    $colName = explode(' ', $col)[0];
    $check = $conn->query("SHOW COLUMNS FROM orders LIKE '$colName'");
    if ($check && $check->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN $col DEFAULT NULL");
    }
}

// Handle mark as collected
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_collected'])) {
    $order_id     = intval($_POST['order_id']);
    $collected_by = trim($_POST['collected_by'] ?? '');
    $collected_id = trim($_POST['collected_id'] ?? '');

    // Only allow collection if order is completed
    $checkStatus = db_query($conn, "SELECT status FROM orders WHERE id = ?", "i", [$order_id]);
    if (!$checkStatus || $checkStatus->num_rows === 0 || $checkStatus->fetch_assoc()['status'] !== 'completed') {
        $error_message = "Order must be set to Completed before it can be marked as collected.";
    } else {
    // Fetch order details first
    $orderRow = db_query($conn,
        "SELECT reference_number, customer_email, total_price, school FROM orders WHERE id = ?",
        "i", [$order_id]
    );

    // Update order status — use id only since school session may be empty
    $upd = db_query($conn,
        "UPDATE orders SET status = 'collected', collected_by = ?, collected_id = ?, collected_at = NOW() WHERE id = ?",
        "ssi", [$collected_by, $collected_id, $order_id]
    );

    if ($upd && $conn->affected_rows > 0) {
        $status_message = "Order marked as collected by $collected_by.";

        if ($orderRow && $orderRow->num_rows > 0) {
            $ord    = $orderRow->fetch_assoc();
            $ref    = $ord['reference_number'];
            $cemail = $ord['customer_email'];
            $total  = number_format($ord['total_price'], 2);

            // Look up phone
            $phoneRow = db_query($conn, "SELECT phone_no FROM users WHERE email = ? LIMIT 1", "s", [$cemail]);
            $cphone = '';
            if ($phoneRow && $phoneRow->num_rows > 0) {
                $cphone = $phoneRow->fetch_assoc()['phone_no'] ?? '';
            }

            // Email
            $subject = "Order Collected - $ref | $school";
            $htmlContent = '<div style="max-width:600px;margin:0 auto;padding:20px;font-family:Arial,sans-serif;">
                <h2 style="color:#28a745;">Order Collected ✅</h2>
                <p>Your order has been successfully collected.</p>
                <div style="background:#f4f4f4;padding:15px;border-radius:8px;margin:20px 0;">
                    <p><strong>Reference:</strong> ' . $ref . '</p>
                    <p><strong>Collected by:</strong> ' . htmlspecialchars($collected_by) . '</p>
                    <p><strong>ID/Contact:</strong> ' . htmlspecialchars($collected_id) . '</p>
                    <p><strong>Total:</strong> R' . $total . '</p>
                    <p><strong>School:</strong> ' . htmlspecialchars($school) . '</p>
                </div>
                <p>Thank you for your order!</p>
                <p>Kind Regards,<br><strong>' . htmlspecialchars($school) . '</strong></p>
            </div>';
            send_email($cemail, $subject, $htmlContent);

            // WhatsApp
            if (!empty($cphone)) {
                $wa_phone = preg_replace('/[^0-9]/', '', $cphone);
                if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
                    $wa_phone = '27' . substr($wa_phone, 1);
                }
                send_whatsapp($wa_phone, "Your order $ref from $school has been collected by $collected_by. Thank you!");
            }
        }
    } else {
        $error_message = "Error marking order as collected. DB error: " . $conn->error;
    }
    } // end status check
}

// Handle reference number verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_reference'])) {
    $reference_number = trim($_POST['reference_number']);

    // Search by reference number only — school session may be empty
    $verify_sql = "SELECT * FROM orders WHERE reference_number = ?";
    $stmt = $conn->prepare($verify_sql);
    $stmt->bind_param("s", $reference_number);
    $stmt->execute();
    $verify_result = $stmt->get_result();

    if ($verify_result->num_rows > 0) {
        $verified_order = $verify_result->fetch_assoc();
        $verification_message = "found";
    } else {
        $verification_message = "No order found with reference: $reference_number";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>View Orders - <?php echo $school; ?></title>
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
        .orders-container {
            margin: 20px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .verification-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .verification-section h4 {
            margin-bottom: 20px;
            font-weight: 600;
        }
        .verification-section .form-control {
            border: none;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
        }
        .verification-section .btn {
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 600;
            border: none;
        }
        .status-pending { 
            color: #ffc107; 
            font-weight: bold; 
            background-color: #fff3cd;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-confirmed { 
            color: #28a745; 
            font-weight: bold; 
            background-color: #d4edda;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-completed { 
            color: #007bff; 
            font-weight: bold; 
            background-color: #d1edff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-cancelled { 
            color: #dc3545; 
            font-weight: bold; 
            background-color: #f8d7da;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .reference-number {
            font-family: 'Courier New', monospace;
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            border: 2px solid #dee2e6;
            font-weight: bold;
            color: #495057;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background-color: <?php echo $themeColor; ?>;
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn-sm {
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
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
        .alert-info {
            background-color: #d1edff;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .stats-card h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
        .stats-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
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
                        <h2 class="mb-0 p-1"><i class="fas fa-shopping-cart me-2"></i>View Orders - <?php echo $school; ?></h2>
                        <div class="mt-2">
                            <a href="add_product" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Add New Product
                            </a>
                            <a href="inventory_status" class="btn btn-sm btn-info">
                                <i class="fas fa-boxes me-1"></i>Inventory Status
                            </a>
                        </div>
                    </div>
                </header>
                <div class="container-fluid">
                    <div class="orders-container">
                        <!-- Order Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <h3>
                                        <?php
                                        $total_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE school = '$school'");
                                        echo $total_orders->fetch_assoc()['count'];
                                        ?>
                                    </h3>
                                    <p><i class="fas fa-shopping-cart me-1"></i>Total Orders</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                    <h3>
                                        <?php
                                        $pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE school = '$school' AND status = 'pending'");
                                        echo $pending_orders->fetch_assoc()['count'];
                                        ?>
                                    </h3>
                                    <p><i class="fas fa-clock me-1"></i>Pending Orders</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                    <h3>
                                        <?php
                                        $completed_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE school = '$school' AND status = 'completed'");
                                        echo $completed_orders->fetch_assoc()['count'];
                                        ?>
                                    </h3>
                                    <p><i class="fas fa-check-circle me-1"></i>Completed Orders</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                                    <h3>
                                        <?php
                                        $total_revenue = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE school = '$school' AND status = 'completed'");
                                        $revenue = $total_revenue->fetch_assoc()['total'] ?? 0;
                                        echo 'R' . number_format($revenue, 2);
                                        ?>
                                    </h3>
                                    <p><i class="fas fa-money-bill-wave me-1"></i>Total Revenue</p>
                                </div>
                            </div>
                        </div>

                        <!-- Reference Number Verification Section -->
                        <div class="verification-section">
                            <h4><i class="fas fa-search me-2"></i>Verify Reference & Record Collection</h4>
                            <form method="post" class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="reference_number"
                                           placeholder="Enter reference number" required
                                           value="<?php echo htmlspecialchars($_POST['reference_number'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" name="verify_reference" class="btn btn-light">
                                        <i class="fas fa-search me-1"></i>Verify Reference
                                    </button>
                                </div>
                            </form>

                            <?php if (isset($verification_message) && $verification_message === 'found' && isset($verified_order)): ?>
                                <?php if ($verified_order['status'] === 'collected'): ?>
                                    <div class="alert alert-success mt-3">
                                        <i class="fas fa-check-circle me-2"></i>
                                        This order was already collected.
                                        <?php if (!empty($verified_order['collected_by'])): ?>
                                            Collected by <strong><?php echo htmlspecialchars($verified_order['collected_by']); ?></strong>
                                            <?php if (!empty($verified_order['collected_at'])): ?>
                                                on <?php echo date('d M Y H:i', strtotime($verified_order['collected_at'])); ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($verified_order['status'] !== 'completed'): ?>
                                    <div class="alert alert-warning mt-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        This order is <strong><?php echo ucfirst($verified_order['status']); ?></strong> and is not yet ready for collection.
                                        The order must be set to <strong>Completed</strong> before it can be collected.
                                    </div>
                                <?php else: ?>
                                    <div class="card mt-3 border-success">
                                        <div class="card-header bg-success text-white">
                                            <i class="fas fa-check-circle me-2"></i>Order Found — Ready to Collect
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Reference:</strong> <?php echo htmlspecialchars($verified_order['reference_number']); ?></p>
                                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($verified_order['customer_email']); ?></p>
                                            <p><strong>Total:</strong> R<?php echo number_format($verified_order['total_price'], 2); ?></p>
                                            <p><strong>Status:</strong> <span class="badge bg-info"><?php echo ucfirst($verified_order['status']); ?></span></p>

                                            <hr>
                                            <h6><i class="fas fa-user-check me-2"></i>Record Collection</h6>
                                            <form method="post" class="row g-2">
                                                <input type="hidden" name="order_id" value="<?php echo $verified_order['id']; ?>">
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" name="collected_by"
                                                           placeholder="Name of person collecting" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" name="collected_id"
                                                           placeholder="ID / Phone number">
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="submit" name="mark_collected" class="btn btn-success w-100">
                                                        <i class="fas fa-hand-holding me-1"></i>Mark Collected
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php elseif (isset($verification_message) && $verification_message !== 'found'): ?>
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($verification_message); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Status Update Message -->
                        <?php if (isset($status_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $status_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Orders Table -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i>#</th>
                                        <th><i class="fas fa-barcode me-1"></i>Reference Number</th>
                                        <th><i class="fas fa-envelope me-1"></i>Customer Email</th>
                                        <th><i class="fas fa-box me-1"></i>Products</th>
                                        <th><i class="fas fa-money-bill-wave me-1"></i>Total Amount</th>
                                        <th><i class="fas fa-calendar me-1"></i>Order Date</th>
                                        <th><i class="fas fa-info-circle me-1"></i>Status</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $orders_sql = "SELECT * FROM orders WHERE school = ? ORDER BY order_date DESC";
                                    $stmt = $conn->prepare($orders_sql);
                                    $stmt->bind_param("s", $school);
                                    $stmt->execute();
                                    $orders_result = $stmt->get_result();
                                    
                                    if ($orders_result->num_rows > 0) {
                                        $counter = 1;
                                        while ($order = $orders_result->fetch_assoc()) {
                                            // Get order items
                                            $items_sql = "SELECT oi.*, p.name as product_name FROM order_items oi 
                                                        JOIN tblproduct p ON oi.product_id = p.id 
                                                        WHERE oi.order_id = ?";
                                            $items_stmt = $conn->prepare($items_sql);
                                            $items_stmt->bind_param("i", $order['id']);
                                            $items_stmt->execute();
                                            $items_result = $items_stmt->get_result();
                                            
                                            $products_list = "";
                                            while ($item = $items_result->fetch_assoc()) {
                                                $products_list .= $item['product_name'] . " (x" . $item['quantity'] . ")<br>";
                                            }
                                            $items_stmt->close();
                                            
                                            echo "<tr>";
                                            echo "<td>" . $counter++ . "</td>";
                                            echo "<td><span class='reference-number'>" . $order['reference_number'] . "</span></td>";
                                            echo "<td>" . $order['customer_email'] . "</td>";
                                            echo "<td>" . $products_list . "</td>";
                                            echo "<td>R" . number_format($order['total_price'], 2) . "</td>";
                                            echo "<td>" . date('Y-m-d H:i', strtotime($order['order_date'])) . "</td>";
                                            echo "<td><span class='status-" . $order['status'] . "'>" . ucfirst($order['status']) . "</span></td>";
                                            echo "<td>";
                                            if ($order['status'] === 'collected') {
                                                echo "<span class='badge bg-success'><i class='fas fa-check-circle me-1'></i>Collected</span>";
                                                if (!empty($order['collected_by'])) {
                                                    echo "<br><small class='text-muted'>By: " . htmlspecialchars($order['collected_by']) . "</small>";
                                                }
                                                if (!empty($order['collected_at'])) {
                                                    echo "<br><small class='text-muted'>" . date('d M Y H:i', strtotime($order['collected_at'])) . "</small>";
                                                }
                                            } else {
                                                echo "<form method='post' style='display: inline;'>";
                                                echo "<input type='hidden' name='order_id' value='" . $order['id'] . "'>";
                                                echo "<select name='new_status' class='form-select form-select-sm' style='width: auto; display: inline-block; margin-right: 5px;'>";
                                                echo "<option value='pending'" . ($order['status'] == 'pending' ? ' selected' : '') . ">Pending</option>";
                                                echo "<option value='confirmed'" . ($order['status'] == 'confirmed' ? ' selected' : '') . ">Confirmed</option>";
                                                echo "<option value='completed'" . ($order['status'] == 'completed' ? ' selected' : '') . ">Completed</option>";
                                                echo "<option value='cancelled'" . ($order['status'] == 'cancelled' ? ' selected' : '') . ">Cancelled</option>";
                                                echo "</select>";
                                                echo "<button type='submit' name='update_status' class='btn btn-sm btn-primary'>Update</button>";
                                                echo "</form>";
                                            }
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center'>No orders found.</td></tr>";
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
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html> 
