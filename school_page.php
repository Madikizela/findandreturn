<?php 
      include('connection.php');
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
    <title>School Page - Parent Dashboard</title>
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
        .school-card {
            border: 1px solid #e9ecef;
            border-radius: 15px;
            width: 300px;
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin: 15px;
            position: relative;
            overflow: hidden;
        }

        .school-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
        }

        .school-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .school-card h3 {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .school-card .btn {
            margin: 5px;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .school-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .schools-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            color: white;
            border-radius: 15px;
            margin: 20px;
        }

        .page-header h1 {
            margin: 0;
            font-weight: 700;
        }

        .page-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
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
              <h2 class="mb-0 p-1"><i class="fas fa-school me-2"></i>School Page</h2>
            </div>
          </header>
          <!-- Breadcrumb-->
          <div class="bg-white">
            <div class="container-fluid">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-3">
                  <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                  <li class="breadcrumb-item active fw-light" aria-current="page">School Page</li>
                </ol>
              </nav>
            </div>
          </div>

        <?php
// Ensure the session is started

$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

// Query to fetch distinct schools associated with the parent's children
$sql = "SELECT DISTINCT s.id, s.school_name 
        FROM children c 
        JOIN schools s ON c.school_id = s.id 
        WHERE c.parent_id = ?";
$result = db_query($conn, $sql, "i", [$user_id]);

$childrenBySchool = array();

if ($result && $result->num_rows > 0) {
    echo '<div class="page-header">
            <h1><i class="fas fa-school me-3"></i>Your Children\'s Schools</h1>
            <p>Manage and view information for all schools where your children are enrolled</p>
          </div>';
    
    echo '<div class="schools-container">';

    while ($row = $result->fetch_assoc()) {
        $school_name = $row['school_name'];
        $school_id = $row['id'];
        // Set the school in the session variable
        $_SESSION['school'] = $school_name;
        $_SESSION['school_id'] = $school_id;

        // Generate a card for each school
        echo "
        <div class='school-card'>
            <h3><i class='fas fa-graduation-cap me-2'></i>" . htmlspecialchars($school_name) . "</h3>
            <div style='margin-top: 20px;'>
                <a href='school_list?school_id=" . urlencode($school_id) . "' 
                   class='btn btn-primary'>
                   <i class='fas fa-eye me-2'></i>View Details
                </a>
                <a href='list_of_found_item?school_id=" . urlencode($school_id) . "' 
                   class='btn btn-info'>
                   <i class='fas fa-hand-holding-heart me-2'></i>Found Items
                </a>
                <a href='school_marketplace?school_id=" . urlencode($school_id) . "' 
                   class='btn btn-success'>
                   <i class='fas fa-store me-2'></i>School Store
                </a>
            </div>
        </div>";
    }
    
    echo '</div>';
} else {
    echo '<div class="empty-state">
            <i class="fas fa-school"></i>
            <h4>No Schools Found</h4>
            <p>You don\'t have any children registered in schools yet.</p>
            <a href="add_child" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add a Child
            </a>
          </div>';
}
?>
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

    </script>
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
  </body>
</html>

