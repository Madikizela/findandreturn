<?php
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

// Get school from session or URL parameter
$school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : (isset($_SESSION['school_id']) ? $_SESSION['school_id'] : 0);
$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];

// If no school is set, redirect to school selection
if ($school_id <= 0) {
    header("Location: list_of_schools");
    exit();
}

// Fetch school details from the new schools table
$school_stmt = $conn->prepare("SELECT school_name FROM schools WHERE id = ?");
$school_stmt->bind_param("i", $school_id);
$school_stmt->execute();
$school_res = $school_stmt->get_result();
if ($school_row = $school_res->fetch_assoc()) {
    $school = $school_row['school_name'];
} else {
    header("Location: list_of_schools");
    exit();
}

// Get theme settings for the school using relational join
$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE school = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $school);
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

// Handle cart actions
if (isset($_GET["action"])) {
    switch($_GET["action"]) {
        case "add":
            if(!empty($_POST["quantity"])) {
                // Use tblproduct with school name
                $product_stmt = $conn->prepare("SELECT * FROM tblproduct WHERE code = ? AND school = ?");
                $product_stmt->bind_param("ss", $_GET["code"], $school);
                $product_stmt->execute();
                $productByCode = $product_stmt->get_result();
                $product = $productByCode->fetch_assoc();
                $product_stmt->close();
                
                if ($product) {
                    // Check if we have enough stock
                    $requested_quantity = $_POST["quantity"];
                    $available_quantity = $product["quantity"];
                    
                    // Check if item is already in cart
                    $cart_quantity = 0;
                    if(!empty($_SESSION["cart_item"]) && in_array($product["code"],array_keys($_SESSION["cart_item"]))) {
                        $cart_quantity = $_SESSION["cart_item"][$product["code"]]["quantity"];
                    }
                    
                    $total_needed = $cart_quantity + $requested_quantity;
                    
                    if ($total_needed <= $available_quantity) {
                        $itemArray = array($product["code"]=>array(
                            'name'=>$product["name"], 
                            'code'=>$product["code"], 
                            'quantity'=>$requested_quantity, 
                            'price'=>$product["price"], 
                            'image'=>$product["image"]
                        ));
                        
                        if(!empty($_SESSION["cart_item"])) {
                            if(in_array($product["code"],array_keys($_SESSION["cart_item"]))) {
                                foreach($_SESSION["cart_item"] as $k => $v) {
                                    if($product["code"] == $k) {
                                        if(empty($_SESSION["cart_item"][$k]["quantity"])) {
                                            $_SESSION["cart_item"][$k]["quantity"] = 0;
                                        }
                                        $_SESSION["cart_item"][$k]["quantity"] += $requested_quantity;
                                    }
                                }
                            } else {
                                $_SESSION["cart_item"] = array_merge($_SESSION["cart_item"],$itemArray);
                            }
                        } else {
                            $_SESSION["cart_item"] = $itemArray;
                        }
                        
                        // Redirect with success message
                        header("Location: school_marketplace?school_id=" . $school_id . "&added=true");
                        exit();
                    } else {
                        // Not enough stock
                        $error_msg = "Not enough stock available. You requested $requested_quantity but only $available_quantity are available.";
                        if ($cart_quantity > 0) {
                            $error_msg .= " You already have $cart_quantity in your cart.";
                        }
                        header("Location: school_marketplace?school_id=" . $school_id . "&error=" . urlencode($error_msg));
                        exit();
                    }
                } else {
                    // Redirect with error message
                    header("Location: school_marketplace?school_id=" . $school_id . "&error=Product not found");
                    exit();
                }
            } else {
                // Redirect with error message
                header("Location: school_marketplace?school_id=" . $school_id . "&error=Please select a quantity");
                exit();
            }
            break;
        case "remove":
            if(!empty($_SESSION["cart_item"])) {
                foreach($_SESSION["cart_item"] as $k => $v) {
                    if($_GET["code"] == $k)
                        unset($_SESSION["cart_item"][$k]);				
                    if(empty($_SESSION["cart_item"]))
                        unset($_SESSION["cart_item"]);
                }
            }
            break;
        case "empty":
            unset($_SESSION["cart_item"]);
            header("Location: school_marketplace?school_id=" . $school_id);
            exit();
            break;	
    }
}

// Get products from tblproduct for this school
$products_sql = "SELECT * FROM tblproduct WHERE school = ? AND quantity > 0 ORDER BY id DESC";
$stmt = $conn->prepare($products_sql);
$stmt->bind_param("s", $school);
$stmt->execute();
$products_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $school; ?> - School Marketplace</title>
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
        .marketplace-header {
            background: linear-gradient(135deg, <?php echo $themeColor; ?> 0%, <?php echo $themeColor; ?>dd 100%);
            color: white;
            padding: 50px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .marketplace-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        .marketplace-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        .marketplace-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        .product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            height: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        .product-image {
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .stock-low {
            background: rgba(255, 193, 7, 0.9) !important;
            color: #333 !important;
        }
        .stock-critical {
            background: rgba(220, 53, 69, 0.9) !important;
        }
        .product-info {
            padding: 25px;
        }
        .product-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #333;
            line-height: 1.3;
        }
        .product-price {
            font-size: 1.4rem;
            font-weight: bold;
            color: <?php echo $themeColor; ?>;
            margin-bottom: 15px;
        }
        .product-code {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
            font-family: 'Courier New', monospace;
        }
        .quantity-input {
            width: 80px;
            text-align: center;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 8px;
            margin-right: 10px;
            font-weight: 600;
        }
        .quantity-input:focus {
            border-color: <?php echo $themeColor; ?>;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-add-cart {
            background: linear-gradient(135deg, <?php echo $themeColor; ?> 0%, <?php echo $themeColor; ?>dd 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
            text-decoration: none;
        }
        .btn-add-cart:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .cart-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .cart-table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .cart-table thead th {
            background-color: <?php echo $themeColor; ?>;
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        .cart-table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }
        .btn-remove {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
        }
        .btn-remove:hover {
            background-color: #c82333;
            color: white;
        }
        .btn-checkout {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }
        .alert {
            border-radius: 10px;
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
        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .empty-cart i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid <?php echo $themeColor; ?>;
        }
    </style>
</head>
<body>
    <div class="page">
        <?php include 'main_header.php'; ?>
        
        <div class="page-content d-flex align-items-stretch">
            <?php include 'main_sidebar.php'; ?>
            
            <div class="content-inner w-100">
                <div class="container-fluid py-4">
                    <!-- Header -->
                    <div class="marketplace-header">
                        <div class="container">
                            <h1><i class="fas fa-store me-3"></i><?php echo $school; ?> Marketplace</h1>
                            <p><i class="fas fa-shopping-bag me-2"></i>Discover amazing products from your school</p>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <?php if(isset($_GET['added']) && $_GET['added'] == 'true'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Success!</strong> Item added to cart successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Error!</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Shopping Cart Section -->
                    <?php if(!empty($_SESSION["cart_item"])): ?>
                        <div class="cart-section" id="cart">
                            <h3 class="section-title"><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h3>
                            <div class="table-responsive">
                                <table class="table table-striped cart-table">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-image me-1"></i>Product</th>
                                            <th><i class="fas fa-tag me-1"></i>Name</th>
                                            <th><i class="fas fa-hashtag me-1"></i>Quantity</th>
                                            <th><i class="fas fa-money-bill-wave me-1"></i>Price</th>
                                            <th><i class="fas fa-calculator me-1"></i>Total</th>
                                            <th><i class="fas fa-cogs me-1"></i>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total_quantity = 0;
                                        $total_price = 0;
                                        foreach($_SESSION["cart_item"] as $k => $v) {
                                            $total_quantity += $v["quantity"];
                                            $total_price += ($v["price"] * $v["quantity"]);
                                        ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo $v["image"]; ?>" alt="<?php echo $v["name"]; ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                                </td>
                                                <td><?php echo $v["name"]; ?></td>
                                                <td><?php echo $v["quantity"]; ?></td>
                                                <td>R<?php echo number_format($v["price"], 2); ?></td>
                                                <td>R<?php echo number_format($v["price"] * $v["quantity"], 2); ?></td>
                                                <td>
                                                    <a href="school_marketplace?action=remove&code=<?php echo $k; ?>&school_id=<?php echo $school_id; ?>" 
                                                       class="btn btn-remove">
                                                        <i class="fas fa-trash me-1"></i>Remove
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h5><i class="fas fa-info-circle me-2"></i>Cart Summary</h5>
                                    <p><strong>Total Items:</strong> <?php echo $total_quantity; ?></p>
                                    <p><strong>Total Price:</strong> R<?php echo number_format($total_price, 2); ?></p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="school_marketplace?action=empty&school_id=<?php echo $school_id; ?>" 
                                       class="btn btn-danger me-2">
                                        <i class="fas fa-trash me-1"></i>Empty Cart
                                    </a>
                                    <a href="enhanced_checkout?school=<?php echo urlencode($school); ?>" 
                                       class="btn btn-checkout">
                                        <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Products Section -->
                    <div class="row">
                        <div class="col-12">
                            <h3 class="section-title"><i class="fas fa-box-open me-2"></i>Available Products</h3>
                        </div>
                    </div>

                    <?php if($products_result->num_rows > 0): ?>
                        <div class="row">
                            <?php while($product = $products_result->fetch_assoc()): ?>
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                    <div class="product-card">
                                        <div class="product-image">
                                            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                            <div class="stock-badge <?php 
                                                if($product['quantity'] <= 3) echo 'stock-critical';
                                                elseif($product['quantity'] <= 10) echo 'stock-low';
                                            ?>">
                                                <i class="fas fa-cubes me-1"></i><?php echo $product['quantity']; ?> left
                                            </div>
                                        </div>
                                        <div class="product-info">
                                            <div class="product-title"><?php echo $product['name']; ?></div>
                                            <div class="product-price">R<?php echo number_format($product['price'], 2); ?></div>
                                            <div class="product-code">
                                                <i class="fas fa-barcode me-1"></i><?php echo $product['code']; ?>
                                            </div>
                                            <form method="post" action="school_marketplace?action=add&code=<?php echo $product['code']; ?>&school_id=<?php echo $school_id; ?>">
                                                <div class="d-flex align-items-center mb-3">
                                                    <label for="quantity_<?php echo $product['code']; ?>" class="me-2">
                                                        <i class="fas fa-sort-numeric-up me-1"></i>Qty:
                                                    </label>
                                                    <input type="number" name="quantity" id="quantity_<?php echo $product['code']; ?>" 
                                                           class="quantity-input" value="1" min="1" max="<?php echo $product['quantity']; ?>" required>
                                                </div>
                                                <button type="submit" class="btn-add-cart w-100" 
                                                        <?php if($product['quantity'] <= 0): ?>disabled<?php endif; ?>>
                                                    <i class="fas fa-cart-plus me-2"></i>
                                                    <?php if($product['quantity'] <= 0): ?>
                                                        Out of Stock
                                                    <?php else: ?>
                                                        Add to Cart
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                            <h4>No products available</h4>
                            <p class="text-muted">Check back later for new products!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

