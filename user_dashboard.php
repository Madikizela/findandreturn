<?php

include('connection.php');
// session_start(); // Already called in connection.php -> security.php
$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
$school = $_SESSION['school'];
$school_id = $_SESSION['school_id'];
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
  header("Location: login");
  exit();
}

// Get theme settings securely
$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE email = ?";
$result = db_query($conn, $sql, "s", [$email]);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#007bff";
    $logoPath = "img/Find&R.png";
}

// Get statistics using modernized relational tables
$totalChildren = 0;
$totalLostItems = 0;
$totalFoundItems = 0;
$totalOrders = 0;

// Count total children using school_id
$sql_children = "SELECT COUNT(*) as total FROM children WHERE school_id = ?";
$result_children = db_query($conn, $sql_children, "i", [$school_id]);
if ($result_children && $result_children->num_rows > 0) {
    $row = $result_children->fetch_assoc();
    $totalChildren = $row['total'];
}

// Count lost items using new_lost_items table and school_id
$sql_lost = "SELECT COUNT(*) as total FROM new_lost_items WHERE school_id = ?";
$result_lost = db_query($conn, $sql_lost, "i", [$school_id]);
if ($result_lost && $result_lost->num_rows > 0) {
    $row = $result_lost->fetch_assoc();
    $totalLostItems = $row['total'];
}

// Count found items using found_items table and school_id
$sql_found = "SELECT COUNT(*) as total FROM found_items WHERE school_id = ?";
$result_found = db_query($conn, $sql_found, "i", [$school_id]);
if ($result_found && $result_found->num_rows > 0) {
    $row = $result_found->fetch_assoc();
    $totalFoundItems = $row['total'];
}

// Count orders using orders table and school_id
try {
    $sql_orders = "SELECT COUNT(*) as total FROM orders WHERE school_id = ?";
    $result_orders = db_query($conn, $sql_orders, "i", [$school_id]);
    if ($result_orders && $result_orders->num_rows > 0) {
        $row = $result_orders->fetch_assoc();
        $totalOrders = $row['total'];
    }
} catch (Exception $e) {
    $totalOrders = 0;
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin Dashboard - <?php echo h($school); ?></title>
    <meta name="description" content="School Admin Dashboard for Find and Return">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="all,follow">
    <?php include 'pwa-head.php'; ?>
    <!-- Google fonts - Poppins -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
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
              <h2 class="mb-0 p-1"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
              <p class="text-muted mb-0">Welcome back, <?php echo $name; ?>! Here's what's happening at <?php echo $school; ?>.</p>
            </div>
          </header>
          <!-- Breadcrumb-->
          <div class="bg-white">
            <div class="container-fluid">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-3">
                  <li class="breadcrumb-item"><a class="fw-light" href="user_dashboard">Home</a></li>
                  <li class="breadcrumb-item active fw-light" aria-current="page">Dashboard</li>
                </ol>
              </nav>
            </div>
          </div>
          
          <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                  <div class="card-icon theme-primary">
                    <i class="fas fa-child"></i>
                  </div>
                  <div class="card-title">Total Children</div>
                  <div class="card-value"><?php echo $totalChildren; ?></div>
                  <div class="card-description">Registered students</div>
                </div>
              </div>
              
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                  <div class="card-icon warning">
                    <i class="fas fa-search"></i>
                  </div>
                  <div class="card-title">Lost Items</div>
                  <div class="card-value"><?php echo $totalLostItems; ?></div>
                  <div class="card-description">Items reported lost</div>
                </div>
              </div>
              
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                  <div class="card-icon success">
                    <i class="fas fa-hand-holding-heart"></i>
                  </div>
                  <div class="card-title">Found Items</div>
                  <div class="card-value"><?php echo $totalFoundItems; ?></div>
                  <div class="card-description">Items recovered</div>
                </div>
              </div>
              
              <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                  <div class="card-icon danger">
                    <i class="fas fa-shopping-cart"></i>
                  </div>
                  <div class="card-title">Total Orders</div>
                  <div class="card-value"><?php echo $totalOrders; ?></div>
                  <div class="card-description">Marketplace orders</div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
              <h4><i class="fas fa-bolt me-2"></i>Quick Actions</h4>
              <a href="add_product" class="action-btn">
                <i class="fas fa-plus-circle me-2"></i>Add Product
              </a>
              <a href="index2" class="action-btn">
                <i class="fas fa-qrcode me-2"></i>Scan Item
              </a>
              <a href="view_lost_items" class="action-btn">
                <i class="fas fa-search me-2"></i>View Lost Items
              </a>
              <a href="announcement" class="action-btn">
                <i class="fas fa-bullhorn me-2"></i>Make Announcement
              </a>
              <a href="view_orders" class="action-btn">
                <i class="fas fa-shopping-cart me-2"></i>View Orders
              </a>
              <a href="settings" class="action-btn">
                <i class="fas fa-cog me-2"></i>Settings
              </a>
            </div>

            <!-- Children Table -->
            <div class="table-container">
              <h4><i class="fas fa-child me-2"></i>Registered Children</h4>
              <div style="overflow-x: auto;">
                <table class="table mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Surname</th>
                      <th>Parent Email</th>
                      <th>Phone Number</th>
                      <th>School</th>
                      <th>Grade</th>
                      <th>QR Code</th>
                      <th>Status</th>
                      <th>Registered Date</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                    $count = 1;
                    $sql = "SELECT * FROM child_table WHERE school = '$school' ORDER BY reg_date DESC";
                    $result = $conn->query($sql);

                    while ($row = $result->fetch_assoc()) {
                        $expire_date = new DateTime($row["expire_date"]);
                        $current_date = new DateTime();
                        $is_expired = $current_date > $expire_date;
                    ?>
                    <tr>
                      <td><?php echo $count; ?></td>
                      <td><strong><?php echo htmlspecialchars($row["first_name"]); ?></strong></td>
                      <td><?php echo htmlspecialchars($row["surname"]); ?></td>
                      <td><?php echo htmlspecialchars($row["parent_email"]); ?></td>
                      <td><?php echo htmlspecialchars($row["parent_phoneNo"]); ?></td>
                      <td><?php echo htmlspecialchars($row["school"]); ?></td>
                      <td>Grade <?php echo htmlspecialchars($row["grade"]); ?></td>
                      <td>
                        <img src='<?php echo $row["qrImage"]; ?>' width="50px" height="50px" class="qr-image" alt="QR Code">
                      </td>
                      <td>
                        <span class="status-badge <?php echo $is_expired ? 'status-expired' : 'status-active'; ?>">
                          <?php echo $is_expired ? 'Expired' : 'Active'; ?>
                        </span>
                      </td>
                      <td><?php echo date('M d, Y', strtotime($row["reg_date"])); ?></td>
                    </tr>
                    <?php $count++; } ?>
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

    </script>
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
  </body>
</html>

