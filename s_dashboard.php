<?php

include('connection.php');
// session_start(); // Already called in connection.php -> security.php
$name = $_SESSION['user_name'];
$email = $_SESSION['socialWorkerEmail'];

// Get theme settings for super admin
$themeColor = "#dc3545"; // Red theme for super admin
$logoPath = "img/Find&R.png";
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Super Admin Dashboard - Find and Return</title>
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
    <link rel="shortcut icon" href="img/favicon.ico">
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
              <h2 class="mb-0 p-1">Admin Dashboard</h2>
            </div>
          </header>
          <!-- Breadcrumb-->
          <div class="bg-white">
            <div class="container-fluid">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-3">
                  <li class="breadcrumb-item"><a class="fw-light" href="">Home</a></li>
                  <li class="breadcrumb-item active fw-light" aria-current="page"></li>
                </ol>
              </nav>
            </div>
          </div>
          <section class="tables">

                        <table class="table mb-0">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>NAME</th>
                              <th>SURNAME</th>
                              <th>EMAIL</th>
                              <th>ALTERNATIVE EMAIL</th>
                              <th>ROLE</th>
                              <th>PHONE NUMBER</th>
                              <th>ALTERNATIVE NUMBER</th>
                              <th>ADDRESS</th>
                              <th>SCHOOL</th>
                              <th>REGISTERED DATE</th>

                            </tr>
                          </thead>
                          <tbody>
                          <?php
                            // $_SESSION['socialWorkerEmail'] = $email;
                             //$_SESSION['school'] = $school;
                           //  $email = $_SESSION['socialWorkerEmail'];
                            // $school = $_SESSION['school'];
                          $count = 1;
                       

                          $sql = "SELECT * FROM  scanner";
                          $result = $conn->query($sql);

                          while ($row = $result->fetch_assoc()) {
                              # code...
                              ?>
                              <tr>
                              <td><?php echo $count; ?></td>
                              <td><?php  echo $row["name"]; ?></td>
                              <td><?php  echo $row["surname"]; ?></td>
                              <td><?php  echo $row["email"]; ?></td>
                              <td><?php  echo $row["alternative_email"]; ?></td>
                              <td><?php  echo $row["role"]; ?></td>
                              <td><?php  echo $row["phoneNo"]; ?></td>
                              <td><?php  echo $row["alternative_phoneNo"]; ?></td>
                              <td><?php  echo $row["address"]; ?></td>
                              <td><?php  echo $row["school"]; ?></td>
                              <td><?php  echo $row["reg_date"]; ?></td>
                            </tr>
                            <tr>
                                <?php $count++;} ?>
                                
                            </tr>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
          </section>
          
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


    </script>
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
  </body>
</html>

