<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php

if (isset($_POST["submit"])) {
    $sent_to_admin = $_POST["sent_to_admin"];
    if ($sent_to_admin == "button1") {
        header("Location: modal");
        exit();
    } elseif ($sent_to_admin == "button2") {
        header("Location: class");
        exit();
    }
}

// System theme
$themeColor = "#007bff";
$logoPath = "img/Find&R.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Return Item - Find and Return</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7fa; }
        .return-card {
            max-width: 600px;
            margin: 80px auto;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header-custom {
            background: var(--primary-color);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .card-body { padding: 40px; background: white; }
        .btn-return {
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card return-card">
            <div class="card-header-custom" style="background-color: <?php echo $themeColor; ?>;">
                <div class="icon-box">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <h2 class="h4 mb-0">How would you like to return the item?</h2>
            </div>
            <div class="card-body text-center">
                <?php $grade = $_SESSION['grade'] ?? 'Unknown'; ?>
                <p class="text-muted mb-4">
                    Thank you for finding this item! It belongs to a learner in <strong>Grade <?php echo htmlspecialchars($grade); ?></strong>.
                </p>
                
                <form action="massege" method="post">
                    <div class="mb-4 text-start">
                        <label class="form-label fw-bold">Select a return option:</label>
                        <select name="sent_to_admin" class="form-select form-select-lg" required>
                            <option value="">Choose an option...</option>
                            <option value="button1">Take it to a Find and Return Box</option>
                            <option value="button2">Take it to the learner's class</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="submit" class="btn btn-primary btn-lg btn-return" style="background-color: <?php echo $themeColor; ?>; border: none;">
                            Continue <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                        <a href="index" class="btn btn-link text-decoration-none text-muted">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

