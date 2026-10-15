<?php
include('connection.php');
// session_start(); // Already called in connection.php
$error = null;

$token = $_GET['token'] ?? '';

// Check if token exists in URL
if (empty($token)) {
    $error = "No reset token provided. Please use the link sent to your email.";
} else {
    // Modernized: Use users table
    $checkToken = db_query($conn, "SELECT email FROM users WHERE token = ?", "s", [$token]);
    if (!$checkToken || $checkToken->num_rows === 0) {
        $error = "This reset link is invalid or has already been used.";
    }
}

if (isset($_POST["submit"]) && !$error) {
    $password = $_POST["password"];
    $password1 = $_POST["password1"];

    if ($password != $password1) {
        $error = "Passwords do not match";
    } else {
        $row = $checkToken->fetch_assoc();
        $email = $row['email'];

        // Update password in the database (Hash it!)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Modernized: Use users table
        $updateQuery = "UPDATE users SET password = ?, token = NULL WHERE email = ?";
        if (db_query($conn, $updateQuery, "ss", [$hashed_password, $email])) {
            echo "<script>alert('Your password has been updated successfully.'); window.location.href='login';</script>";
            exit();
        } else {
            $error = "Error updating record: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'pwa-head.php'; ?>
    <title>Reset Password - Find and Return</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
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

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            display: flex;
            min-height: 600px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .reset-sidebar {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            padding: 60px 40px;
            width: 45%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .reset-sidebar::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="25" cy="25" r="1" fill="white" opacity="0.2"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.2"/></svg>');
            opacity: 0.3;
        }

        .sidebar-content { position: relative; z-index: 2; }

        .sidebar-logo img {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .brand-logo { font-size: 2.2rem; font-weight: 700; margin-bottom: 15px; }
        .brand-subtitle { font-size: 1.1rem; opacity: 0.9; line-height: 1.6; }

        .reset-form-panel {
            padding: 60px 50px;
            width: 55%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .form-header { text-align: center; margin-bottom: 35px; }
        .form-header h2 { color: var(--dark-color); font-weight: 700; margin-bottom: 10px; }
        
        .form-group { margin-bottom: 25px; }
        .form-label { font-weight: 600; color: var(--dark-color); margin-bottom: 8px; display: block; }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: var(--secondary-color);
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-weight: 700;
            width: 100%;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,123,255,0.3); }

        #password-requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-top: 20px;
        }

        .requirement { display: flex; align-items: center; margin-bottom: 5px; color: var(--danger-color); }
        .requirement.valid { color: var(--success-color); }
        .requirement i { margin-right: 8px; width: 15px; }

        @media (max-width: 768px) {
            .reset-container { flex-direction: column; }
            .reset-sidebar, .reset-form-panel { width: 100%; padding: 40px 30px; }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-sidebar">
            <div class="sidebar-content">
                <div class="sidebar-logo">
                    <img src="img/f-icon.ico" alt="Logo">
                </div>
                <h1 class="brand-logo">Security First</h1>
                <p class="brand-subtitle">Reset your password to keep your Find and Return account secure and protected.</p>
            </div>
        </div>

        <div class="reset-form-panel">
            <div class="form-header">
                <h2>Reset Password</h2>
                <p class="text-muted">Create a strong new password for your account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
                <a href="login" class="btn btn-outline-secondary w-100" style="border-radius: 12px;">Return to Login</a>
            <?php else: ?>
                <form method="POST" id="resetForm">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" required placeholder="Enter new password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password')">
                                <i class="fas fa-eye" id="eye-password"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password1" id="password1" class="form-control" required placeholder="Repeat new password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password1')">
                                <i class="fas fa-eye" id="eye-password1"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-shield-alt me-2"></i>Update Password
                    </button>

                    <div id="password-requirements">
                        <p class="fw-bold mb-2">Password must contain:</p>
                        <div class="requirement" id="req-length"><i class="fas fa-circle"></i> 8+ Characters</div>
                        <div class="requirement" id="req-upper"><i class="fas fa-circle"></i> Uppercase Letter</div>
                        <div class="requirement" id="req-lower"><i class="fas fa-circle"></i> Lowercase Letter</div>
                        <div class="requirement" id="req-number"><i class="fas fa-circle"></i> A Number</div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function togglePass(id) {
            const input = document.getElementById(id);
            const icon = document.getElementById('eye-' + id);
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        const password = document.getElementById('password');
        const requirements = {
            length: document.getElementById('req-length'),
            upper: document.getElementById('req-upper'),
            lower: document.getElementById('req-lower'),
            number: document.getElementById('req-number')
        };

        password.addEventListener('input', () => {
            const val = password.value;
            
            const checks = {
                length: val.length >= 8,
                upper: /[A-Z]/.test(val),
                lower: /[a-z]/.test(val),
                number: /[0-9]/.test(val)
            };

            for (const key in checks) {
                if (checks[key]) {
                    requirements[key].classList.add('valid');
                    requirements[key].querySelector('i').className = 'fas fa-check-circle';
                } else {
                    requirements[key].classList.remove('valid');
                    requirements[key].querySelector('i').className = 'fas fa-circle';
                }
            }
        });
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>
