<?php

include('connection.php');
// session_start(); // Already called in connection.php -> security.php
$name = $_SESSION['user_name'];
$email = $_SESSION['socialWorkerEmail'];
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
  header("Location: login");
  exit();
}

// Use default Find and Return theme for parents
$themeColor = "#007bff";
$logoPath = "img/Find&R.png";
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Parent Dashboard - Find and Return</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="all,follow">
    <?php include 'pwa-head.php'; ?>
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
                        <h2 class="mb-0 p-1"><i class="fas fa-home me-2"></i>Parent Dashboard</h2>
                    </div>
                </header>
                
                <!-- Breadcrumb-->
                <div class="bg-white">
                    <div class="container-fluid">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 py-3">
                                <li class="breadcrumb-item"><a class="fw-light" href="index">Home</a></li>
                                <li class="breadcrumb-item active fw-light" aria-current="page">Children's List</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <div class="container-fluid">
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-hover" style="min-width: 1400px;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>Status</th>
                                        <th>Parent Email</th>
                                        <th>Child Phone</th>
                                        <th>Parent Phone</th>
                                        <th>Address</th>
                                        <th>School</th>
                                        <th>Grade</th>
                                        <th>QR Code</th>
                                        <th>Download</th>
                                        <th>Registered Date</th>
                                        <th>Expire Date</th>
                                    </tr>
                                </thead>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    $user_id = $_SESSION['user_id'];
                                    
                                    // Modernized: Join with users and schools tables
                                    $sql = "SELECT c.*, s.school_name, u.phone_no, u.alternative_phone_no, u.address 
                                            FROM children c 
                                            LEFT JOIN schools s ON c.school_id = s.id 
                                            LEFT JOIN users u ON c.parent_id = u.id 
                                            WHERE c.parent_id = ?";
                                    $result = db_query($conn, $sql, "i", [$user_id]);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            // Handle potential missing reg_date or different column name
                                            $regDateStr = $row["reg_date"] ?? date('Y-m-d');
                                            $regDate = new DateTime($regDateStr);
                                            
                                            // Calculate expire date (1 year after registration)
                                            $expireDate = clone $regDate;
                                            $expireDate->modify('+1 year');
                                            
                                            $today = new DateTime();
                                            $isExpired = $today > $expireDate;
                                            ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td><?php echo h($row["unique_reference"]); ?></td>
                                                <td><?php echo h($row["first_name"]); ?></td>
                                                <td><?php echo h($row["surname"]); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $isExpired ? 'bg-danger' : 'bg-success'; ?>">
                                                        <?php echo $isExpired ? 'Expired' : 'Active'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo h($email); ?></td>
                                                <td><?php echo h($row["phone_no"] ?? 'N/A'); ?></td>
                                                <td><?php echo h($row["alternative_phone_no"] ?? 'N/A'); ?></td>
                                                <td><?php echo h($row["address"] ?? 'N/A'); ?></td>
                                                <td><?php echo h($row["school_name"] ?? 'N/A'); ?></td>
                                                <td><?php echo h($row["grade"]); ?></td>
                                                <td><img src="<?php echo h($row["qr_image"]); ?>" width="50"></td>
                                                <td>
                                                    <a href="download.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </td>
                                                <td><?php echo $regDate->format('Y-m-d'); ?></td>
                                                <td><?php echo $expireDate->format('Y-m-d'); ?></td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='15' class='text-center'>No children registered yet.</td></tr>";
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
    <?php include 'pwa-footer.php'; ?>
    <script>
      // ------------------------------------------------------- //
      //   Inject SVG Sprite -
      //   see more here
      //   https://css-tricks.com/ajaxing-svg-sprite/
      // ------------------------------------------------------ //
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
      // this is set to BootstrapTemple website as you cannot
      // inject local SVG sprite (using only 'icons/orion-svg-sprite.svg' path)
      // while using file:// protocol
      // pls don't forget to change to your domain :)
      // SVG sprite removed - external resource no longer available

      $('#body').css('min-height', screen.height);
    </script>
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
  </body>
</html>

