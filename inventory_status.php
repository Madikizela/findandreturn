<?php
include('connection.php');

$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
$school = $_SESSION['school'];
$user_id = $_SESSION['user_id'];
$school_id = $_SESSION['school_id'];

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

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

// Handle stock updates
if (isset($_POST['update_stock'])) {
    $product_id = intval($_POST['product_id']);
    $new_quantity = intval($_POST['new_quantity']);
    
    $update_sql = "UPDATE tblproduct SET quantity = ? WHERE id = ? AND school_admin_email = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("iis", $new_quantity, $product_id, $email);
    if ($stmt->execute()) {
        $status_message = "Stock updated successfully!";
    } else {
        $error_message = "Error updating stock: " . $conn->error;
    }
    $stmt->close();
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);
    $del = db_query($conn,
        "DELETE FROM tblproduct WHERE id = ? AND school_admin_email = ?",
        "is", [$product_id, $email]
    );
    if ($del) {
        $status_message = "Product deleted successfully.";
    } else {
        $error_message = "Error deleting product.";
    }
}

// Get inventory status using school_admin_email
$inventory_sql = "SELECT * FROM tblproduct WHERE school_admin_email = ? ORDER BY quantity ASC, name ASC";
$stmt = $conn->prepare($inventory_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$inventory_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Inventory Status - <?php echo $school; ?></title>
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
        .stock-critical {
            background-color: #f8d7da !important;
            border-color: #f5c6cb !important;
        }
        .stock-low {
            background-color: #fff3cd !important;
            border-color: #ffeaa7 !important;
        }
        .stock-good {
            background-color: #d1edff !important;
            border-color: #bee5eb !important;
        }
        .stock-out {
            background-color: #f8f9fa !important;
            border-color: #dee2e6 !important;
            opacity: 0.7;
        }
        .stock-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        .stock-critical .stock-indicator {
            background-color: #dc3545;
        }
        .stock-low .stock-indicator {
            background-color: #ffc107;
        }
        .stock-good .stock-indicator {
            background-color: #28a745;
        }
        .stock-out .stock-indicator {
            background-color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="page">
        <?php include 'main_header.php'; ?>
        <div class="page-content d-flex align-items-stretch">
            <?php include 'main_sidebar.php'; ?>
            <div class="content-inner w-100">
                <div class="container-fluid">
                    <!-- Status Messages -->
                    <?php if (isset($status_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $status_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Stock Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Out of Stock</h5>
                                    <h3 class="mb-0">
                                        <?php
                                        $out_of_stock = $conn->query("SELECT COUNT(*) as count FROM tblproduct WHERE school_admin_email = '$email' AND quantity = 0");
                                        echo $out_of_stock->fetch_assoc()['count'];
                                        ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h5 class="card-title">Low Stock (1-5)</h5>
                                    <h3 class="mb-0">
                                        <?php
                                        $low_stock = $conn->query("SELECT COUNT(*) as count FROM tblproduct WHERE school_admin_email = '$email' AND quantity BETWEEN 1 AND 5");
                                        echo $low_stock->fetch_assoc()['count'];
                                        ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Good Stock (6-20)</h5>
                                    <h3 class="mb-0">
                                        <?php
                                        $good_stock = $conn->query("SELECT COUNT(*) as count FROM tblproduct WHERE school_admin_email = '$email' AND quantity BETWEEN 6 AND 20");
                                        echo $good_stock->fetch_assoc()['count'];
                                        ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">High Stock (20+)</h5>
                                    <h3 class="mb-0">
                                        <?php
                                        $high_stock = $conn->query("SELECT COUNT(*) as count FROM tblproduct WHERE school_admin_email = '$email' AND quantity > 20");
                                        echo $high_stock->fetch_assoc()['count'];
                                        ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Product Inventory</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Code</th>
                                            <th>Current Stock</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($product = $inventory_result->fetch_assoc()) {
                                            $quantity = $product['quantity'];
                                            
                                            // Determine stock status
                                            if ($quantity == 0) {
                                                $status_class = 'stock-out';
                                                $status_text = 'Out of Stock';
                                                $status_icon = 'times-circle';
                                            } elseif ($quantity <= 5) {
                                                $status_class = 'stock-critical';
                                                $status_text = 'Critical';
                                                $status_icon = 'exclamation-triangle';
                                            } elseif ($quantity <= 20) {
                                                $status_class = 'stock-low';
                                                $status_text = 'Low';
                                                $status_icon = 'exclamation-circle';
                                            } else {
                                                $status_class = 'stock-good';
                                                $status_text = 'Good';
                                                $status_icon = 'check-circle';
                                            }
                                        ?>
                                            <tr class="<?php echo $status_class; ?>">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="stock-indicator"></span>
                                                        <div>
                                                            <strong><?php echo $product['name']; ?></strong>
                                                            <br>
                                                            <small class="text-muted">R<?php echo number_format($product['price'], 2); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><code><?php echo $product['code']; ?></code></td>
                                                <td>
                                                    <strong><?php echo $quantity; ?></strong> units
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $status_class == 'stock-out' ? 'secondary' : ($status_class == 'stock-critical' ? 'danger' : ($status_class == 'stock-low' ? 'warning' : 'success')); ?>">
                                                        <i class="fas fa-<?php echo $status_icon; ?> me-1"></i>
                                                        <?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateStockModal<?php echo $product['id']; ?>">
                                                        <i class="fas fa-edit me-1"></i>Update Stock
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger ms-1"
                                                        onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            
                                            <!-- Update Stock Modal -->
                                            <div class="modal fade" id="updateStockModal<?php echo $product['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Update Stock - <?php echo $product['name']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="post">
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="new_quantity" class="form-label">New Quantity</label>
                                                                    <input type="number" class="form-control" id="new_quantity" name="new_quantity" value="<?php echo $quantity; ?>" min="0" required>
                                                                    <div class="form-text">Current stock: <?php echo $quantity; ?> units</div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" name="update_stock" class="btn btn-primary">Update Stock</button>
                                                            </div>
                                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        $stmt->close();
                                        
                                        if ($inventory_result->num_rows == 0) {
                                            echo '<tr><td colspan="5" class="text-center">No products found.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Hidden delete form -->
    <form id="deleteForm" method="POST" action="inventory_status" style="display:none;">
        <input type="hidden" name="delete_product" value="1">
        <input type="hidden" name="product_id" id="deleteProductId">
    </form>

    <script>
        function confirmDelete(productId, productName) {
            if (confirm('Are you sure you want to delete "' + productName + '"?\nThis cannot be undone.')) {
                document.getElementById('deleteProductId').value = productId;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
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
