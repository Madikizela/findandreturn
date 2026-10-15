<?php 
      session_start();
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
      
      if(isset($_POST["submit"]))
      {
        $number = $_POST["number"];
        $_SESSION['number'] = $number;
        header("Location: add_child");
        exit();
      }
      ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Child - Parent Dashboard</title>
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
            <?php include 'main_sidebar.php'; ?>
            
            <div class="content-inner w-100">
          <!-- Page Header-->
          <header class="bg-white shadow-sm px-4 py-3 z-index-20">
            <div class="container-fluid px-0">
              <h2 class="mb-0 p-1"><i class="fas fa-child me-2"></i>Add Child</h2>
            </div>
          </header>
          <!-- Breadcrumb-->

      
      <div class="container-fluid">
        <div class="form-container">
            <div class="form-header">
                <h3><i class="fas fa-child me-2"></i>Add Child to Your Account</h3>
                <p>Select how many children you want to register</p>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="number" class="form-label">
                        <i class="fas fa-users me-1"></i>Number of Children to Add
                    </label>
                    <select name="number" id="number" class="form-select" required>
                        <option value="">Select number of children...</option>
                        <option value="1">1 Child</option>
                        <option value="2">2 Children</option>
                        <option value="3">3 Children</option>
                        <option value="4">4 Children</option>
                        <option value="5">5 Children</option>
                        <option value="6">6 Children</option>
                    </select>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        You can add up to 6 children to your account
                    </small>
                </div>
                
                <div class="text-center">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-right me-2"></i>Continue to Registration
                    </button>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <a href="parent_dashboard" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
      </div>

          <!-- Page Footer-->
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

