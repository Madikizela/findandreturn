<?php

include('connection.php');
// session_start(); // Already called in connection.php -> security.php
$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
$school = $_SESSION['school'];
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
  header("Location: login");
  exit();
}

// Get theme settings
$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#007bff";
    $logoPath = "img/Find&R.png";
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>School Profile Settings - <?php echo $school; ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <meta name="robots" content="all,follow">
    <!-- Google fonts - Poppins -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <!-- Choices CSS-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="css/custom.css">
    <!-- Favicon-->
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'header_css.php'; ?>
    <style>
        .settings-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .settings-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }
        .settings-header h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .settings-header p {
            color: #666;
            margin: 0;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: <?php echo $themeColor; ?>;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-primary {
            background-color: <?php echo $themeColor; ?>;
            border-color: <?php echo $themeColor; ?>;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: <?php echo $themeColor; ?>;
            border-color: <?php echo $themeColor; ?>;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        input[type="color"] {
            width: 80px;
            height: 50px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .alert {
            border-radius: 8px;
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
        .current-logo {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px;
            background-color: #f8f9fa;
        }
        .file-upload-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .file-upload-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        .file-upload-label {
            display: block;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .file-upload-label:hover {
            border-color: <?php echo $themeColor; ?>;
            background-color: rgba(0, 123, 255, 0.05);
        }
        .file-upload-label i {
            margin-right: 8px;
            color: <?php echo $themeColor; ?>;
        }
    </style>
  </head>
  <body>
    <div class="page">
      <?php include 'main_header.php'; ?>
      <div class="page-content d-flex align-items-stretch">
        <?php include 'main_sidebar.php'; ?>
        <div class="content-inner w-100">
          <!-- Page Header-->
          <header class="bg-white shadow-sm px-4 py-3 z-index-20">
            <div class="container-fluid px-0">
              <h2 class="mb-0 p-1"><i class="fas fa-cog me-2"></i>School Profile Settings</h2>
            </div>
          </header>
          <!-- Breadcrumb-->
          <div class="bg-white">
            <div class="container-fluid">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-3">
                  <li class="breadcrumb-item"><a class="fw-light" href="">Home</a></li>
                  <li class="breadcrumb-item active fw-semibold">Settings</li>
                </ol>
              </nav>
            </div>
          </div>
          <!-- Dashboard Counts Section -->
          <section class="tables py-0">
            <div class="container-fluid">
              <div class="row gy-4">
                <div class="col-lg-12">
                  <div class="card mb-0">
                    <div class="card-header">
                      <h3 class="h4 mb-0">School Profile Settings</h3>
                    </div>
                    <div class="card-body">
                      <div class="settings-container">
                        <div class="settings-header">
                          <h3><i class="fas fa-cog me-2"></i>School Profile Settings</h3>
                          <p>Customize your school's appearance and branding</p>
                        </div>

                        <?php if(isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                Settings updated successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Error updating settings: <?php echo htmlspecialchars($_GET['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="save_settings" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="school_name" class="form-label">
                                    <i class="fas fa-school me-1"></i>School Name
                                </label>
                                <input type="text" class="form-control" id="school_name" name="school_name" 
                                       value="<?php echo htmlspecialchars($school); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Admin Email
                                </label>
                                <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                       value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-palette me-1"></i>Theme Color
                                </label>
                                <div class="color-picker-wrapper">
                                    <input type="color" id="theme_color" name="theme_color" 
                                           value="<?php echo $themeColor; ?>" title="Choose theme color">
                                    <span class="text-muted">Click to select your school's theme color</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-image me-1"></i>School Logo
                                </label>
                                <?php if($logoPath && $logoPath != "img/default-logo.png"): ?>
                                    <div class="mb-3">
                                        <p class="text-muted mb-2">Current Logo:</p>
                                        <img src="<?php echo $logoPath; ?>" alt="Current Logo" class="current-logo">
                                    </div>
                                <?php endif; ?>
                                <div class="file-upload-wrapper">
                                    <label for="logo" class="file-upload-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        Choose a new logo file (PNG, JPG, JPEG)
                                    </label>
                                    <input type="file" id="logo" name="logo" accept="image/*">
                                </div>
                                <small class="form-text text-muted">Recommended size: 200x200 pixels or larger</small>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </button>
                            </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
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
        
        // File upload preview
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const label = document.querySelector('.file-upload-label');
            
            if (file) {
                label.innerHTML = `<i class="fas fa-check-circle"></i>Selected: ${file.name}`;
                label.style.borderColor = '#28a745';
                label.style.backgroundColor = '#d4edda';
            }
        });
    </script>
    <?php include 'pwa-footer.php'; ?>
  </body>
</html>



