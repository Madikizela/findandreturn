<?php
session_start();

// Get reference number, email, and school from URL parameters
$reference_number = isset($_GET['ref']) ? $_GET['ref'] : '';
$customer_email = isset($_GET['email']) ? $_GET['email'] : '';
$school = isset($_GET['school']) ? $_GET['school'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation - Find and Return Store</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include 'pwa-head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .thank-you-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .success-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .success-body {
            padding: 40px;
        }
        .reference-number {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .info-card {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .steps-list {
            list-style: none;
            padding: 0;
        }
        .steps-list li {
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
            position: relative;
            padding-left: 30px;
        }
        .steps-list li:before {
            content: counter(step-counter);
            counter-increment: step-counter;
            position: absolute;
            left: 0;
            top: 10px;
            background: #007bff;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .steps-list li:last-child {
            border-bottom: none;
        }
        .btn-home {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-home:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="thank-you-container">
            <div class="success-header">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h1>Order Confirmed!</h1>
                <p>Thank you for your order. Your reference number has been sent to your email.</p>
            </div>
            
            <div class="success-body">
                <?php if ($reference_number): ?>
                    <div class="text-center">
                        <h4>Your Reference Number</h4>
                        <div class="reference-number">
                            <?php echo $reference_number; ?>
                        </div>
                        <p class="text-muted">Keep this reference number safe!</p>
                    </div>
                <?php endif; ?>
                
                <div class="info-card">
                    <h5><i class="fas fa-envelope"></i> Email Confirmation</h5>
                    <p>A detailed confirmation email has been sent to:</p>
                    <p class="fw-bold"><?php echo htmlspecialchars($customer_email); ?></p>
                </div>
                
                <div class="info-card">
                    <h5><i class="fas fa-info-circle"></i> What happens next?</h5>
                    <ol class="steps-list" style="counter-reset: step-counter;">
                        <li>We'll process your order and prepare it for collection</li>
                        <li>You'll receive an email notification when your order is ready</li>
                        <li>Visit the school with your reference number to collect your order</li>
                        <li>Present the reference number to the school staff for verification</li>
                    </ol>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Important Reminder</h6>
                    <p class="mb-0">Please bring your reference number when collecting your order. Without it, we cannot release your items.</p>
                </div>
                
                <div class="d-grid gap-2">
                    <?php if ($school): ?>
                        <a href="school_marketplace?school=<?php echo urlencode($school); ?>" class="btn btn-home btn-lg">
                            <i class="fas fa-shopping-bag"></i> Continue Shopping
                        </a>
                    <?php else: ?>
                        <a href="school_page" class="btn btn-home btn-lg">
                            <i class="fas fa-shopping-bag"></i> Continue Shopping
                        </a>
                    <?php endif; ?>
                    <a href="parent_dashboard" class="btn btn-outline-secondary">
                        <i class="fas fa-home"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'pwa-footer.php'; ?>
</body>
</html>
