<?php
session_start();
include('connection.php');

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

if ($result && $result->num_rows > 0) {
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
    <title>Collected Items - <?php echo $school; ?></title>
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
        .status-collected {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .collector-info {
            font-size: 0.85rem;
            color: #6c757d;
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
                        <h2 class="mb-0 p-1"><i class="fas fa-hand-holding me-2"></i>Collected Items</h2>
                    </div>
                </header>
                
                <!-- Breadcrumb-->
                <div class="bg-white">
                    <div class="container-fluid">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 py-3">
                                <li class="breadcrumb-item"><a class="fw-light" href="index">Home</a></li>
                                <li class="breadcrumb-item active fw-light" aria-current="page">Collected Items</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <div class="container-fluid">
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>Parent Email</th>
                                        <th>Child Phone</th>
                                        <th>Parent Phone</th>
                                        <th>Address</th>
                                        <th>School</th>
                                        <th>Grade</th>
                                        <th>Collected By</th>
                                        <th>Collector Contact</th>
                                        <th>Date Collected</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    $sql = "SELECT * FROM collected_items WHERE school = '$school'";
                                    $result = $conn->query($sql);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $count; ?></td>
                                        <td><?php echo htmlspecialchars($row["first_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["surname"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["parent_email"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["child_phoneNo"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["parent_phoneNo"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["p_address"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["school"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["grade"]); ?></td>
                                        <td>
                                            <div class="collector-info">
                                                <strong><?php echo htmlspecialchars($row["collectedBy"]); ?></strong>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row["collectedByContact"]); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row["collectedDate"])); ?></td>
                                        <td>
                                            <span class="status-badge status-collected">Collected</span>
                                        </td>
                                    </tr>
                                    <?php 
                                            $count++;
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="13" class="text-center py-4">
                                            <i class="fas fa-hand-holding fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No collected items found</p>
                                        </td>
                                    </tr>
                                    <?php
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
    
    <!-- JavaScript files-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <!-- Main File-->
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
    </script>
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

