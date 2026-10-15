<?php
include('connection.php');
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

//checking from databases
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL Injection
    // Modernized: Join with schools table to get school_name
    $query_s = "SELECT u.*, s.school_name 
                FROM users u 
                LEFT JOIN schools s ON u.school_id = s.id 
                WHERE u.email = ?";
    $result = db_query($conn, $query_s, "s", [$email]);

    if($result && $result->num_rows > 0)
    {
        while($row = $result->fetch_assoc())
        {
             $db_password = $row['password'];
             
             // Check if password matches (handling both hashed and plain text for migration)
             $password_match = false;
             if (password_verify($password, $db_password)) {
                 $password_match = true;
             } elseif ($password === $db_password) {
                 // Plain text match - migrate to hash
                 $password_match = true;
                 $new_hash = password_hash($password, PASSWORD_DEFAULT);
                 db_query($conn, "UPDATE users SET password = ? WHERE email = ?", "ss", [$new_hash, $email]);
             }

             if($password_match)
             {
                 $email = $row['email'];
                 $school = $row['school_name'] ?? ''; // Get school name from join
                 $school_id = $row['school_id'];
                 $role = $row['role'];
                 $phoneNo = $row['phone_no'];
                 $name = $row['name'];

                 // Secure session management — regenerate ID on login to prevent
                 // session fixation, but preserve existing session data.
                 $existingData = $_SESSION;
                 session_regenerate_id(true);
                 $_SESSION = $existingData;
                 $_SESSION['last_regeneration'] = time();
                 $_SESSION['role'] = $role;
                 $_SESSION['user_id'] = $row['id']; // Store user ID in session
                 $_SESSION['school_id'] = $school_id; // Store school ID in session
                 
                 if($role =='user'){
                    $_SESSION['socialWorkerEmail'] = $email;
                    $_SESSION['school'] = $school;
                    $_SESSION['user_name'] = $name;
                    $_SESSION["logged_in"] = true;
                    header("Location: user_dashboard");
                    exit();
                 }
                 elseif ($role == 'parent') {
                    $_SESSION['socialWorkerEmail'] = $email;
                    $_SESSION['school'] = $school;
                    $_SESSION['phoneNo'] = $phoneNo;
                    $_SESSION['user_name'] = $name;
                    $_SESSION["logged_in"] = true;
                    header("Location: parent_dashboard");
                    exit();
                 }
                 elseif ($role == 'superUser') {
                    $_SESSION['socialWorkerEmail'] = $email;
                    $_SESSION['school'] = $school;
                    $_SESSION['user_name'] = $name;
                    $_SESSION["logged_in"] = true;
                    header("Location: s_dashboard");
                    exit();
                }
             }
        }
    }
    
    // If we get here, login failed
    echo "<script>alert('invalid login details.');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Find and Return</title>
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
            padding-top: 80px; /* Add padding for fixed navbar */
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

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            display: flex;
            animation: slideInUp 0.8s ease-out;
            margin: 0 auto; /* Center the container */
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

        .login-sidebar {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-sidebar::before {
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

        .login-form {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
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

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #0056b3;
            text-decoration: underline;
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

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                margin: 10px;
                border-radius: 15px;
            }
            
            .login-sidebar, .login-form {
                padding: 40px 30px;
            }
            
            .brand-logo {
                font-size: 2rem;
            }
            
            .form-header h2 {
                font-size: 1.5rem;
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
                        <a class="nav-link" href="index#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index4">Scan</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="login-container">
        <div class="login-sidebar">
            <div class="sidebar-content">
                <div class="sidebar-logo">
                    <img src="img/f-icon.ico" alt="Find and Return Logo">
                    <h3>Find and Return</h3>
                </div>
                <h1 class="brand-logo"><i class="fas fa-shield-alt me-3"></i>Welcome Back!</h1>
                <p class="brand-subtitle">Sign in to your account to access the Find and Return system. Manage your lost and found items efficiently.</p>
                
                <ul class="feature-list">
                    <li><i class="fas fa-search"></i>Find lost items quickly</li>
                    <li><i class="fas fa-hand-holding-heart"></i>Return items to owners</li>
                    <li><i class="fas fa-shield-alt"></i>Secure and reliable</li>
                    <li><i class="fas fa-users"></i>Community-driven platform</li>
                </ul>
            </div>
        </div>
        
        <div class="login-form">
            <div class="form-header">
                <h2><i class="fas fa-sign-in-alt me-2"></i>Sign In</h2>
                <p>Enter your credentials to access your account</p>
            </div>
            
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
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
                               required
                               autocomplete="email">
                    </div>
                </div>
                
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
                               required
                               autocomplete="current-password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
                
                <div class="forgot-password">
                    <a href="forgot_password">
                        <i class="fas fa-key me-2"></i>Forgot your password?
                    </a>
                </div>
                
                <div class="divider">
                    <span>Don't have an account?</span>
                </div>
                
                <a href="register" class="btn btn-secondary">
                    <i class="fas fa-user-plus me-2"></i>Create New Account
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
    </script>
</body>
</html>

