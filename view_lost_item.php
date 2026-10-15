<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php
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
    <title>View Lost Items - Parent Dashboard</title>
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
  </head>
  <body>
    <div class="page">
        <?php include 'main_header.php'; ?>
        
        <div class="page-content d-flex align-items-stretch">
            <?php include 'school_sidebar.php'; ?>
            
            <div class="content-inner w-100">
          <!-- Page Header-->
          <header class="bg-white shadow-sm px-4 py-3 z-index-20">
            <div class="container-fluid px-0">
              <h2 class="mb-0 p-1"><i class="fas fa-search me-2"></i>View Lost Items</h2>
            </div>
          </header>
          <!-- Breadcrumb-->
          <div class="bg-white">
            <div class="container-fluid">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-3">
                  <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                  <li class="breadcrumb-item active fw-light" aria-current="page">View Lost Items</li>
                </ol>
              </nav>
            </div>
          </div>
          
          <div class="container-fluid">
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title"><i class="fas fa-search me-2"></i>Lost Items Registry</h3>
                    <div>
                        <a href="request_item" class="btn btn-primary btn-modern">
                            <i class="fas fa-plus me-2"></i>Report New Lost Item
                        </a>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <?php
                    $email = $_SESSION['socialWorkerEmail'];
                    $user_id = $_SESSION['user_id'];
                    $count = 1;
                    
                    // Modernized: Join new_lost_items with children and users, filtered by parent
                    $sql = "SELECT l.*, c.first_name, c.surname, u.email as parent_email 
                            FROM new_lost_items l 
                            JOIN children c ON l.child_id = c.id 
                            JOIN users u ON c.parent_id = u.id 
                            WHERE c.parent_id = ?
                            ORDER BY l.date_lost DESC";
                    $lostStmt = $conn->prepare($sql);
                    $lostStmt->bind_param("i", $user_id);
                    $lostStmt->execute();
                    $result = $lostStmt->get_result();
                    
                    if ($result && $result->num_rows > 0) {
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-1"></i>#</th>
                                <th><i class="fas fa-child me-1"></i>Child Name</th>
                                <th><i class="fas fa-box me-1"></i>Item</th>
                                <th><i class="fas fa-info-circle me-1"></i>Description</th>
                                <th><i class="fas fa-calendar me-1"></i>Lost Date</th>
                                <th><i class="fas fa-envelope me-1"></i>Parent Email</th>
                                <th><i class="fas fa-fingerprint me-1"></i>Unique ID</th>
                                <th><i class="fas fa-tasks me-1"></i>Tracking Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo htmlspecialchars($row["first_name"] . " " . $row["surname"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["description"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["date_lost"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["parent_email"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["unique_report_id"]); ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-search me-1"></i><?php echo htmlspecialchars($row["tracking_status"]); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php
                    } else {
                        echo '<div class="empty-state">
                                <i class="fas fa-search"></i>
                                <h4>No Registered Lost Items</h4>
                                <p>There are currently no lost items registered in the system.</p>
                              </div>';
                    }
                    ?>
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
    <?php include 'pwa-footer.php'; ?>
  </body>
</html>

