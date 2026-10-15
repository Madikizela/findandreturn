<?php
include("connection.php");
// session_start(); // Already started in connection.php -> security.php

// Function to generate reference number
function generateReferenceNumber() {
    $prefix = 'ORD';
    $timestamp = date('YmdHis');
    $random = strtoupper(substr(md5(uniqid()), 0, 6));
    return $prefix . $timestamp . $random;
}

// Function to send order confirmation email
function sendOrderEmail($customerEmail, $referenceNumber, $orderDetails, $totalAmount, $school) {
    $to = $customerEmail;
    $subject = "Order Confirmation - Reference: " . $referenceNumber;
    
    $from = 'no-reply@findandreturn.co.za';
    $fromName = 'Find and Return Store - ' . $school;
    
    $htmlContent = '
    <html>
    <head>
        <title>Order Confirmation</title>
    </head>
    <body>
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
            <h2 style="color: #333;">Order Confirmation</h2>
            <p>Thank you for your order from <strong>' . $school . '</strong>!</p>
            
            <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h3 style="margin-top: 0;">Reference Number: <span style="color: #007bff; font-weight: bold;">' . $referenceNumber . '</span></h3>
                <p><strong>Total Amount:</strong> R' . number_format($totalAmount, 2) . '</p>
                <p><strong>Order Date:</strong> ' . date('Y-m-d H:i:s') . '</p>
                <p><strong>School:</strong> ' . $school . '</p>
            </div>
            
            <div style="margin: 20px 0;">
                <h4>Order Details:</h4>
                ' . $orderDetails . '
            </div>
            
            <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4 style="margin-top: 0; color: #856404;">Important:</h4>
                <p>Please keep this reference number safe. You will need to present it when collecting your order from ' . $school . '.</p>
            </div>
            
            <p>If you have any questions, please contact the school administration.</p>
            
            <hr style="margin: 30px 0;">
            <p style="color: #666; font-size: 12px;">This is an automated email from Find and Return Store - ' . $school . '.</p>
        </div>
    </body>
    </html>';
    
    $headers = "From: $fromName <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $htmlContent, $headers);
}

if (isset($_POST["checkout"]) && !empty($_SESSION["cart_item"])) {
    // Get customer email from form
    $customer_email = $_POST['customer_email'];
    $customer_phone = $_POST['customer_phone'];
    $school = $_POST['school']; // Get school from form
    
    // Validate that all items are from the same school
    $all_same_school = true;
    $first_school = "";
    
    foreach ($_SESSION["cart_item"] as $item) {
        $product_query = "SELECT school FROM tblproduct WHERE code = ?";
        $product_stmt = $conn->prepare($product_query);
        $product_stmt->bind_param("s", $item["code"]);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();
        
        if ($product_row = $product_result->fetch_assoc()) {
            $item_school = $product_row['school'];
            if (empty($first_school)) {
                $first_school = $item_school;
            } elseif ($item_school !== $first_school) {
                $all_same_school = false;
                break;
            }
        }
        $product_stmt->close();
    }
    
    if (!$all_same_school) {
        $error_message = "Error: All items in your cart must be from the same school. Please review your cart.";
    } else {
    
    // Generate reference number
    $reference_number = generateReferenceNumber();
    
    // Calculate totals and prepare order details
    $total_price = 0;
    $total_quantity = 0;
    $order_details_html = '<table style="width: 100%; border-collapse: collapse;">';
    $order_details_html .= '<tr style="background-color: #f8f9fa;"><th style="padding: 8px; border: 1px solid #dee2e6;">Product</th><th style="padding: 8px; border: 1px solid #dee2e6;">Quantity</th><th style="padding: 8px; border: 1px solid #dee2e6;">Price</th><th style="padding: 8px; border: 1px solid #dee2e6;">Total</th></tr>';
    
    foreach ($_SESSION["cart_item"] as $item) {
        $total_quantity += $item["quantity"];
        $item_total = $item["price"] * $item["quantity"];
        $total_price += $item_total;
        
        $order_details_html .= '<tr>';
        $order_details_html .= '<td style="padding: 8px; border: 1px solid #dee2e6;">' . $item["name"] . '</td>';
        $order_details_html .= '<td style="padding: 8px; border: 1px solid #dee2e6;">' . $item["quantity"] . '</td>';
        $order_details_html .= '<td style="padding: 8px; border: 1px solid #dee2e6;">R' . number_format($item["price"], 2) . '</td>';
        $order_details_html .= '<td style="padding: 8px; border: 1px solid #dee2e6;">R' . number_format($item_total, 2) . '</td>';
        $order_details_html .= '</tr>';
    }
    $order_details_html .= '</table>';
    
    // Save order to database
    $order_date = date("Y-m-d H:i:s");
    
    // Insert main order
    $order_query = "INSERT INTO orders (reference_number, customer_email, total_quantity, total_price, order_date, school, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("ssdsss", $reference_number, $customer_email, $total_quantity, $total_price, $order_date, $school);
    
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        
        // Insert order items and update inventory
        foreach ($_SESSION["cart_item"] as $item) {
            $product_query = "SELECT id, quantity FROM tblproduct WHERE code = ?";
            $product_stmt = $conn->prepare($product_query);
            $product_stmt->bind_param("s", $item["code"]);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();
            
            if ($product_row = $product_result->fetch_assoc()) {
                $product_id = $product_row['id'];
                $current_quantity = $product_row['quantity'];
                $ordered_quantity = $item["quantity"];
                
                // Check if we have enough stock
                if ($current_quantity >= $ordered_quantity) {
                    // Insert order item
                    $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                    $item_stmt = $conn->prepare($item_query);
                    $item_stmt->bind_param("iids", $order_id, $product_id, $ordered_quantity, $item["price"]);
                    $item_stmt->execute();
                    $item_stmt->close();
                    
                    // Update inventory (reduce stock)
                    $new_quantity = $current_quantity - $ordered_quantity;
                    $update_query = "UPDATE tblproduct SET quantity = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("ii", $new_quantity, $product_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                } else {
                    // Not enough stock - this shouldn't happen if validation is working
                    $error_message = "Error: Insufficient stock for some items. Please review your cart.";
                    break;
                }
            }
            $product_stmt->close();
        }
        
        // Send confirmation email
        $email_sent = sendOrderEmail($customer_email, $reference_number, $order_details_html, $total_price, $school);
        
        // Send WhatsApp confirmation
        $whatsappMessage = "Hello! Thank you for your order from " . $school . ". Your reference number is: " . $reference_number . ". Total Amount: R" . number_format($total_price, 2) . ". Please present this reference when collecting your order.";
        send_whatsapp($customer_phone, $whatsappMessage);
        
        // Clear the cart after successful checkout
        unset($_SESSION["cart_item"]);
        
        // Redirect to thank you page with reference number and school
        header("Location: thank_you?ref=" . $reference_number . "&email=" . urlencode($customer_email) . "&school=" . urlencode($school));
        exit();
    } else {
        $error_message = "Error processing order: " . $conn->error;
    }
    $stmt->close();
    } // Close the else statement for school validation
}

// Get logged in user's email and phone number
$logged_in_email = $_SESSION['socialWorkerEmail'] ?? '';
$logged_in_phone = '';

if ($logged_in_email) {
    $user_query = "SELECT phone_no FROM users WHERE email = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("s", $logged_in_email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_row = $user_result->fetch_assoc()) {
        $logged_in_phone = $user_row['phone_no'];
    }
    $user_stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enhanced Checkout - Find and Return Store</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include 'pwa-head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .checkout-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .checkout-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .checkout-body {
            padding: 30px;
        }
        .cart-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-checkout {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
        }
        .btn-checkout:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-header">
            <h1><i class="fas fa-shopping-cart"></i> Checkout</h1>
            <p>Complete your order and receive a reference number</p>
        </div>
        
        <div class="checkout-body">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION["cart_item"])): ?>
                <?php
                // Get school name from the first cart item
                $school_name = "";
                $total = 0;
                foreach ($_SESSION["cart_item"] as $item) {
                    $item_total = $item["price"] * $item["quantity"];
                    $total += $item_total;
                    
                    // Get school name from database using product code
                    $product_query = "SELECT school FROM tblproduct WHERE code = ?";
                    $product_stmt = $conn->prepare($product_query);
                    $product_stmt->bind_param("s", $item["code"]);
                    $product_stmt->execute();
                    $product_result = $product_stmt->get_result();
                    
                    if ($product_row = $product_result->fetch_assoc()) {
                        $school_name = $product_row['school'];
                        break; // Use the first item's school
                    }
                    $product_stmt->close();
                }
                ?>
                
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h4>Order Summary</h4>
                    <?php if (!empty($school_name)): ?>
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-school me-2"></i>
                            <strong>School:</strong> <?php echo htmlspecialchars($school_name); ?>
                        </div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($_SESSION["cart_item"] as $item) {
                                    $item_total = $item["price"] * $item["quantity"];
                                ?>
                                    <tr>
                                        <td><?php echo $item["name"]; ?></td>
                                        <td><?php echo $item["quantity"]; ?></td>
                                        <td>R<?php echo number_format($item["price"], 2); ?></td>
                                        <td>R<?php echo number_format($item_total, 2); ?></td>
                                    </tr>
                                <?php } ?>
                                <tr class="table-active">
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>R<?php echo number_format($total, 2); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Checkout Form -->
                <form method="post" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email" value="<?php echo htmlspecialchars($logged_in_email); ?>" required>
                                <div class="form-text">We'll send your order confirmation and reference number to this email.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">Phone Number * (for WhatsApp)</label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_phone" value="<?php echo htmlspecialchars($logged_in_phone); ?>" required>
                                <div class="form-text">We'll send your order confirmation and reference number to WhatsApp.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="school" class="form-label">School *</label>
                                <input type="text" class="form-control" id="school" name="school" value="<?php echo htmlspecialchars($school_name); ?>" readonly>
                                <div class="form-text">School automatically detected from your cart items.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Important Information</h5>
                        <ul class="mb-0">
                            <li>You will receive a unique reference number via email</li>
                            <li>Present this reference number when collecting your order</li>
                            <li>Orders will be available for collection at the selected school</li>
                            <li>You will be notified when your order is ready for collection</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="checkout" class="btn btn-checkout btn-lg">
                            <i class="fas fa-credit-card"></i> Complete Order
                        </button>
                        <a href="payed_for_service" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Cart
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h3>Your cart is empty</h3>
                    <p>Add some products to your cart before checking out.</p>
                    <a href="payed_for_service" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html> 
