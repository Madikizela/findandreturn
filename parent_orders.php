<?php
include('connection.php');

$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

// Use default theme
$themeColor = "#007bff";
$logoPath = "img/Find&R.png";

// Fetch parent's orders
$orders_sql = "SELECT * FROM orders WHERE customer_email = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($orders_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$orders_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>My Orders - Find and Return</title>
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
        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, <?php echo $themeColor; ?> 0%, <?php echo $themeColor; ?>dd 100%);
            color: white;
            border-radius: 12px;
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
            <?php include 'main_sidebar.php'; ?>
            <div class="content-inner w-100">
                <header class="bg-white shadow-sm px-4 py-3 z-index-20">
                    <div class="container-fluid px-0">
                        <h2 class="mb-0 p-1"><i class="fas fa-receipt me-2"></i>My Orders</h2>
                    </div>
                </header>
                <div class="container-fluid">
                    <div class="orders-container">
                        <div class="page-header">
                            <h3><i class="fas fa-shopping-cart me-2"></i>Your Order History</h3>
                            <p>Track the status of all your orders</p>
                        </div>

                        <?php if ($orders_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-barcode me-1"></i>Reference Number</th>
                                            <th><i class="fas fa-school me-1"></i>School</th>
                                            <th><i class="fas fa-box me-1"></i>Products</th>
                                            <th><i class="fas fa-money-bill-wave me-1"></i>Total Amount</th>
                                            <th><i class="fas fa-calendar me-1"></i>Order Date</th>
                                            <th><i class="fas fa-info-circle me-1"></i>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                                            <?php
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
                                            ?>
                                            <tr>
                                                <td><span class="reference-number"><?php echo $order['reference_number']; ?></span></td>
                                                <td><?php echo htmlspecialchars($order['school']); ?></td>
                                                <td><?php echo $products_list; ?></td>
                                                <td>R<?php echo number_format($order['total_price'], 2); ?></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                                                <td><span class="status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <h4>No orders yet!</h4>
                                <p>Start shopping at your school marketplace to place your first order.</p>
                                <a href="school_page" class="btn btn-primary">
                                    <i class="fas fa-store me-2"></i>Go to School Marketplace
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php $stmt->close(); ?>
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
