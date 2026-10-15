<?php
include('connection.php');
$error = null;

// Fetch schools for dropdown
$schools = [];
$schools_result = $conn->query("SELECT id, school_name FROM schools ORDER BY school_name ASC");
if ($schools_result) {
    while ($row = $schools_result->fetch_assoc()) {
        $schools[] = $row;
    }
}

// if($_SERVER["REQUEST_METHOD"]== "POST")
if(isset($_POST["submit"]))
{
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $email =$_POST["email"];
    $alternative_email =$_POST["alternative_email"];
    $role =$_POST["role"];
    $phoneNo =$_POST["phoneNo"];
    $alternative_phoneNo =$_POST["alternative_phoneNo"];
    $address =$_POST["address"];
    $password = $_POST["password"];
    $password1 = $_POST["password1"];

    if ($password != $password1) {
      echo "<script>alert('Password did not match!')</script>";
  } else {
      // Hash the password
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      
      // Modernized: Check email in users table
      $sql = "SELECT * FROM users WHERE email = ?";
      $result = db_query($conn, $sql, "s", [$email]);
      
      if($result && $result->num_rows > 0)
      {
          echo "<span class='text-danger'>Email address is already exists please try again using different email.</span>";
      }
      else {
          // Modernized: Use users table with correct column names and school_id
          $school_id = !empty($_POST['school_id']) ? $_POST['school_id'] : null;
          
          $sql = "INSERT into users (`name`, `surname`, `email`, `alternative_email`, `role`, `phone_no`, `alternative_phone_no`, `address`, `reg_date`, `password`, `school_id`)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, now(), ?, ?)";
          $params = [$name, $surname, $email, $alternative_email, $role, $phoneNo, $alternative_phoneNo, $address, $hashed_password, $school_id];
          
          if (db_query($conn, $sql, "sssssssssi", $params)) {
              // Release session lock so the email sending doesn't block other requests
              session_write_close();
              
              // Registration successful
              $to = $email;
              $subject = 'Welcome to Find and Return!';
              
              // Only attach PDF if it exists on disk
              $attachment = null;
              $pdfPath = __DIR__ . '/img/REGISTRATION_RESOURCE_PACK-2.pdf';
              if (file_exists($pdfPath)) {
                  $attachment = $pdfPath;
              }

              $messageBody = "Welcome to the Find and Return customer community. We are honored that you have chosen to register for our service.<br><br>
              It is our utmost priority to ensure you have an excellent experience with our Find & Return service and team from day one. 
              We have compiled the attached brief resource pack to provide a framework and a clear path for success in the onboarding process and beyond.<br><br>
              
              <b>Your Login Credentials:</b><br>
              Email: $email<br>
              Password: $password<br><br>

              Inside this packet, you will find:
              <ul>
              <li>An introduction to our team and key contact details</li>
              <li>Links to our policies</li>
              <li>Process beyond the onboarding stage</li>
              <li>Links to our support videos</li>
              </ul>
              Should you have any queries, please find below our contact information.<br>
              Service Management: pinky@kdbssolutions.co.za<br>
              Legal Queries: Legal@findandreturn.co.za<br>
              User Support: support@findandreturn.co.za<br><br>
              Kind Regards,<br>
              FindAndReturn Team";

              $htmlContent = "<h3>Welcome!</h3>Dear $name $surname<p>$messageBody</p>";

              // Use the central send_email function which handles authentication headers
              $mailResult = send_email($to, $subject, $htmlContent, $attachment);

              // WhatsApp Notification — only send if phone number is valid
              $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNo);
              if (strlen($cleanPhone) === 10 && strpos($cleanPhone, '0') === 0) {
                  $cleanPhone = '27' . substr($cleanPhone, 1);
              }
              if (!empty($cleanPhone) && strlen($cleanPhone) >= 9) {
                  $waResult = send_whatsapp($cleanPhone, '', true, 'welcome_new_user', [$name, $to], 'en_US');
              } else {
                  $waResult = false;
              }
              
              error_log("Register: email=" . ($mailResult ? 'sent' : 'failed') . ", whatsapp=" . ($waResult ? 'sent' : 'failed') . ", to=$to, phone=$cleanPhone");

              if ($mailResult) {
                  echo "<script>alert('You are successfully registered. A confirmation email has been sent to $to. Please check your inbox (and spam folder).');</script>";
                  echo "<script>window.location.href='login';</script>";
              } else {
                  echo "<script>alert('Registration successful, but we could not send the confirmation email to $to. Your account is created, you can try logging in.');</script>";
                  echo "<script>window.location.href='login';</script>";
              }
              exit();
          }
      else {
          $error = $conn->error;
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Find and Return</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
    <?php include 'pwa-head.php'; ?>
    <?php include 'meta_pixel.php'; ?>
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .navbar-brand img {
            width: 40px;
            height: 40px;
            margin-right: 12px;
            border-radius: 8px;
        }

        .navbar-brand span {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: #333 !important;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            padding: 80px 20px 20px 20px; /* Add padding for fixed navbar and sides */
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('img/background.jpg') center/cover;
            opacity: 0.1;
            z-index: -1;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 1200px;
            min-height: auto;
            display: flex;
            animation: slideInUp 0.8s ease-out;
            margin: 20px auto; /* Add some margin for better spacing */
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-sidebar {
            background: linear-gradient(135deg, var(--success-color), #1e7e34);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .register-sidebar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .sidebar-content {
            position: relative;
            z-index: 2;
        }

        .sidebar-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar-logo img {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .sidebar-logo h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .brand-logo {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .brand-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }

        .feature-list i {
            margin-right: 12px;
            width: 20px;
            color: #ffd700;
            font-size: 1.1rem;
        }

        .register-form {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            overflow-y: auto;
            max-height: 700px;
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

        .register-form {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            width: 100%;
            overflow-y: auto;
            max-height: 100vh;
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
        }

        /* Password strength styles */
        .password-strength {
            margin-top: 8px;
        }

        .password-strength .progress {
            border-radius: 10px;
            background-color: #e9ecef;
        }

        .password-strength .progress-bar {
            transition: all 0.3s ease;
            border-radius: 10px;
        }

        .password-tips {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px;
            margin-top: 8px;
        }

        .password-tips ul {
            margin-bottom: 0;
            font-size: 0.85rem;
        }

        .password-tips li {
            transition: color 0.3s ease;
        }

        .password-match {
            margin-top: 8px;
        }

        /* Show/hide password button */
        .input-group .btn-outline-secondary {
            border-color: #ced4da;
            color: #6c757d;
        }

        .input-group .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        .input-group .btn-outline-secondary:focus {
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #1e7e34);
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

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            background: linear-gradient(135deg, #1e7e34, var(--success-color));
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

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
        }

        .divider span {
            background: white;
            padding: 0 20px;
            color: var(--secondary-color);
            font-size: 0.9rem;
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

        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
                margin: 10px;
                border-radius: 15px;
                min-height: auto;
            }
            
            .register-sidebar, .register-form {
                padding: 40px 30px;
            }
            
            .brand-logo {
                font-size: 2rem;
            }
            
            .form-header h2 {
                font-size: 1.5rem;
            }
            
            body {
                padding: 80px 10px 10px 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index">
                <img src="img/f-icon.ico" alt="Find & Return Logo" class="me-2">
                <span>Find & Return</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="register">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index4">Scan</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="register-container">
        <div class="register-sidebar">
            <div class="sidebar-content">
                <div class="sidebar-logo">
                    <img src="img/f-icon.ico" alt="Find and Return Logo">
                    <h3>Find and Return</h3>
                </div>
                <h1 class="brand-logo"><i class="fas fa-user-plus me-3"></i>Join Us!</h1>
                <p class="brand-subtitle">Create your account and become part of the Find and Return community. Help us connect lost items with their owners.</p>
                
                <ul class="feature-list">
                    <li><i class="fas fa-user-shield"></i>Secure registration process</li>
                    <li><i class="fas fa-bell"></i>Instant notifications</li>
                    <li><i class="fas fa-mobile-alt"></i>Mobile-friendly platform</li>
                    <li><i class="fas fa-headset"></i>24/7 support available</li>
                </ul>
            </div>
        </div>
        
        <div class="register-form">
            <div class="form-header">
                <h2><i class="fas fa-user-plus me-2"></i>Create Account</h2>
                <p>Fill in your details to create your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-2"></i>First Name
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       placeholder="Enter your first name"
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="surname" class="form-label">
                                <i class="fas fa-user me-2"></i>Last Name
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="surname" 
                                       name="surname" 
                                       placeholder="Enter your last name"
                                       required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
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
                                       placeholder="Enter your email address"
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="alternative_email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Alternative Email
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control" 
                                       id="alternative_email" 
                                       name="alternative_email" 
                                       placeholder="Enter alternative email">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phoneNo" class="form-label">
                                <i class="fas fa-phone me-2"></i>Phone Number
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phoneNo" 
                                       name="phoneNo" 
                                       placeholder="Enter your phone number"
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="alternative_phoneNo" class="form-label">
                                <i class="fas fa-phone me-2"></i>Alternative Phone
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" 
                                       class="form-control" 
                                       id="alternative_phoneNo" 
                                       name="alternative_phoneNo" 
                                       placeholder="Enter alternative phone">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="school_id" class="form-label">
                        <i class="fas fa-school me-2"></i>School Association (Optional for Parents)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-school"></i>
                        </span>
                        <select class="form-select" id="school_id" name="school_id">
                            <option value="">-- Select School --</option>
                            <?php foreach ($schools as $school): ?>
                                <option value="<?php echo $school['id']; ?>">
                                    <?php echo h($school['school_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-text text-muted">Select the school your children attend.</div>
                </div>

                <!-- Hidden role field - default to parent -->
                <input type="hidden" name="role" value="parent">
                
                <div class="form-group">
                    <label for="address" class="form-label">
                        <i class="fas fa-map-marker-alt me-2"></i>Address
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <textarea class="form-control" 
                                  id="address" 
                                  name="address" 
                                  rows="3"
                                  placeholder="Enter your address"
                                  required></textarea>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter your password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted" id="passwordStrengthText">Password strength: Very Weak</small>
                            </div>
                            <div class="password-tips mt-2">
                                <small class="text-muted">
                                    <strong>Password Requirements:</strong>
                                    <ul class="mt-1 mb-0" style="padding-left: 20px;">
                                        <li id="lengthCheck">At least 8 characters</li>
                                        <li id="uppercaseCheck">One uppercase letter</li>
                                        <li id="lowercaseCheck">One lowercase letter</li>
                                        <li id="numberCheck">One number</li>
                                        <li id="specialCheck">One special character (!@#$%^&*)</li>
                                    </ul>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password1" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirm Password
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password1" 
                                       name="password1" 
                                       placeholder="Confirm your password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-match mt-2">
                                <small class="text-muted" id="passwordMatchText">Passwords must match</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="submit" class="btn btn-success">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
                
                <div class="divider">
                    <span>Already have an account?</span>
                </div>
                
                <a href="login" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In Instead
                </a>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'pwa-footer.php'; ?>
    <script>
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
        
        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            let checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*]/.test(password)
            };
            
            // Update check marks
            document.getElementById('lengthCheck').style.color = checks.length ? '#28a745' : '#dc3545';
            document.getElementById('uppercaseCheck').style.color = checks.uppercase ? '#28a745' : '#dc3545';
            document.getElementById('lowercaseCheck').style.color = checks.lowercase ? '#28a745' : '#dc3545';
            document.getElementById('numberCheck').style.color = checks.number ? '#28a745' : '#dc3545';
            document.getElementById('specialCheck').style.color = checks.special ? '#28a745' : '#dc3545';
            
            // Calculate strength
            if (checks.length) strength += 20;
            if (checks.uppercase) strength += 20;
            if (checks.lowercase) strength += 20;
            if (checks.number) strength += 20;
            if (checks.special) strength += 20;
            
            // Update progress bar
            const progressBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('passwordStrengthText');
            
            progressBar.style.width = strength + '%';
            
            if (strength <= 20) {
                progressBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Password strength: Very Weak';
            } else if (strength <= 40) {
                progressBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Password strength: Weak';
            } else if (strength <= 60) {
                progressBar.className = 'progress-bar bg-info';
                strengthText.textContent = 'Password strength: Fair';
            } else if (strength <= 80) {
                progressBar.className = 'progress-bar bg-primary';
                strengthText.textContent = 'Password strength: Good';
            } else {
                progressBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Password strength: Strong';
            }
            
            return strength;
        }
        
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
        
        document.getElementById('togglePassword1').addEventListener('click', function() {
            const passwordField = document.getElementById('password1');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
        
        // Real-time password strength checking
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            checkPasswordStrength(password);
            
            // Check password match
            const password1 = document.getElementById('password1').value;
            if (password1) {
                checkPasswordMatch(password, password1);
            }
        });
        
        // Password confirmation validation
        function checkPasswordMatch(password, password1) {
            const matchText = document.getElementById('passwordMatchText');
            
            if (password1 && password !== password1) {
                matchText.textContent = 'Passwords do not match';
                matchText.style.color = '#dc3545';
                document.getElementById('password1').classList.add('is-invalid');
                document.getElementById('password1').classList.remove('is-valid');
            } else if (password1 && password === password1) {
                matchText.textContent = 'Passwords match';
                matchText.style.color = '#28a745';
                document.getElementById('password1').classList.add('is-valid');
                document.getElementById('password1').classList.remove('is-invalid');
            } else {
                matchText.textContent = 'Passwords must match';
                matchText.style.color = '#6c757d';
                document.getElementById('password1').classList.remove('is-valid', 'is-invalid');
            }
        }
        
        document.getElementById('password1').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const password1 = this.value;
            checkPasswordMatch(password, password1);
        });
    </script>
</body>
</html>
