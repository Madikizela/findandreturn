<?php
// Handle form submission
$message = "";
$messageType = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = "Please enter your email address.";
        $messageType = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "danger";
    } else {
        // Clear session before starting reset process
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Email is valid, proceed with reset logic in-file to avoid redirect issues
        include_once 'connection.php';
        
        $resetToken = bin2hex(random_bytes(32));

        // Modernized: Use users table and correct column names
        $sql = "SELECT email, phone_no FROM users WHERE email = ?";
        $result = db_query($conn, $sql, "s", [$email]);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $email_id = $row['email'];
            $phone_no = $row['phone_no'] ?? '';

            // Modernized: Update token in users table
            $updateSql = "UPDATE users SET token = ? WHERE email = ?";
            if (db_query($conn, $updateSql, "ss", [$resetToken, $email_id])) {
                
                // Detect if running locally or on production
                $isLocal = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1' || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
                $baseUrl = $isLocal ? "http://localhost/findandreturntesting" : "https://findandreturn.co.za";
                $resetLink = $baseUrl . "/reset_password.php?token=" . $resetToken;

                // Email Logic
                $to = $email_id;
                $subject = 'Reset Your Password - Find and Return';
                
                $htmlContent = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e9ecef; border-radius: 10px;'>
                        <h2 style='color: #007bff; text-align: center;'>Password Reset Request</h2>
                        <p>Hello,</p>
                        <p>We received a request to reset your password for your Find and Return account. Click the button below to proceed:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$resetLink' style='background-color: #007bff; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset My Password</a>
                        </div>
                        <p>If you have trouble clicking the button, copy and paste this link into your browser:</p>
                        <p style='word-break: break-all; color: #6c757d;'>$resetLink</p>
                        <hr style='border: 0; border-top: 1px solid #e9ecef; margin: 20px 0;'>
                        <p style='font-size: 0.8rem; color: #6c757d;'>If you did not request this password reset, please ignore this email. This link will remain active for 1 hour.</p>
                    </div>
    <?php include 'pwa-footer.php'; ?>
                </body>
                </html>";

                // Use PHPMailer via the central utility
                require_once 'mailer_utils.php';
                $mailSent = send_email($to, $subject, $htmlContent);

                // WhatsApp Notification
                if (!empty($phone_no)) {
                    $whatsappMessage = "Hello! Click the link below to reset your password for Find and Return: " . $resetLink;
                    send_whatsapp($phone_no, $whatsappMessage);
                }

                if ($mailSent) {
                    $message = "Success! A reset link has been sent to your email and WhatsApp.";
                    $messageType = "success";
                } else {
                    $message = "The reset token was generated, but we couldn't send the email. Please contact support.";
                    $messageType = "warning";
                }
            } else {
                $message = "Error updating reset token. Please try again.";
                $messageType = "danger";
            }
        } else {
            // Security best practice: don't reveal if email exists, but for testing we can be specific
            $message = "If this email is registered, you will receive a reset link shortly.";
            $messageType = "info";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Forgot Password - Find and Return</title>
    <meta name="description" content="Reset your password for the Find and Return system">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        .forgot-password-container {
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }
        
        .info-panel {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .info-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .info-content {
            position: relative;
            z-index: 2;
        }
        
        .info-panel h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .info-panel p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .feature-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }
        
        .feature-list li i {
            margin-right: 12px;
            color: #fff;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .form-panel {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .form-header h2 {
            color: var(--dark-color);
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: var(--secondary-color);
            font-size: 1rem;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background-color: white;
            outline: none;
        }
        
        .form-control.is-invalid {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        .form-control.is-valid {
            border-color: var(--success-color);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 8px;
            font-size: 0.875rem;
            color: var(--danger-color);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
            background: linear-gradient(135deg, #0056b3, var(--primary-color));
        }
        
        .btn-secondary {
            background: var(--light-color);
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1rem;
            font-weight: 500;
            color: var(--secondary-color);
            transition: all 0.3s ease;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            color: var(--dark-color);
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success-color);
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid var(--info-color);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-text {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            z-index: 3;
            background: none;
            border: none;
        }
        
        .input-group .form-control {
            padding-left: 50px;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: white;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .footer a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-section img {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .logo-section h3 {
            color: var(--dark-color);
            font-weight: 600;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .forgot-password-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .info-panel, .form-panel {
                padding: 40px 30px;
            }
            
            .info-panel h1 {
                font-size: 2rem;
            }
            
            .form-header h2 {
                font-size: 1.5rem;
            }
        }
        
        .loading {
            display: none;
        }
        
        .btn-primary.loading {
            position: relative;
            color: transparent;
        }
        
        .btn-primary.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <div class="row g-0">
            <!-- Information Panel -->
            <div class="col-lg-6">
                <div class="info-panel">
                    <div class="info-content">
                        <h1><i class="fas fa-key me-3"></i>Forgot Password?</h1>
                        <p>Don't worry! It happens to the best of us. Enter your email address and we'll send you a link to reset your password.</p>
                        
                        <ul class="feature-list">
                            <li><i class="fas fa-shield-alt"></i>Secure password reset process</li>
                            <li><i class="fas fa-envelope"></i>Reset link sent to your email</li>
                            <li><i class="fas fa-clock"></i>Quick and easy setup</li>
                            <li><i class="fas fa-lock"></i>Your account stays protected</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Form Panel -->
            <div class="col-lg-6">
                <div class="form-panel">
                    <div class="logo-section">
                        <img src="img/f-icon.ico" alt="Find and Return Logo">
                        <h3>Find and Return</h3>
                    </div>
                    
                    <div class="form-header">
                        <h2>Reset Your Password</h2>
                        <p>Enter your email address to receive a password reset link</p>
                    </div>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($email); ?>"
                                       placeholder="Enter your email address"
                                       required
                                       autocomplete="email">
                            </div>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                        </button>
                        
                        <a href="login" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Login
                        </a>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">
                            Remember your password? 
                            <a href="login" class="text-decoration-none">Sign in here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p class="mb-0">
                Design by <a href="https://kdbssolutions.co.za/" target="_blank">KDBS Solutions</a>
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
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
                        } else {
                            // Show loading state
                            var submitBtn = document.getElementById('submitBtn');
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
        // Email validation
        document.getElementById('email').addEventListener('blur', function() {
            var email = this.value;
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (email && emailRegex.test(email)) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
        
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
        
        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>

