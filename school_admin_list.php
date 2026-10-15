<?php

include('connection.php');
session_start();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Find and return</title>
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

  </head>
  <body>
    <div class="page">
      <!-- Main Navbar-->
      <header class="header z-index-50">
        <nav class="navbar py-3 px-0 shadow-sm text-white position-relative">
          <!-- Search Box-->
          <div class="search-box shadow-sm">
            <button class="dismiss d-flex align-items-center">
              <svg class="svg-icon svg-icon-heavy">
                <use xlink:href="#close-1"> </use>
              </svg>
            </button>
            <form id="searchForm" action="#" role="search">
              <input class="form-control shadow-0" type="text" placeholder="What are you looking for...">
            </form>
          </div>
          <div class="container-fluid w-100">
            <div class="navbar-holder d-flex align-items-center justify-content-between w-100">
              <!-- Navbar Header-->
              <div class="navbar-header">
                <!-- Navbar Brand --><a class="navbar-brand d-none d-sm-inline-block" href="">
                  <div class="brand-text d-none d-lg-inline-block"><span>Super Admin</span><strong>Dashboard</strong></div>
                  <div class="brand-text d-none d-sm-inline-block d-lg-none"><strong>BD</strong></div></a>
                <!-- Toggle Button--><a class="menu-btn active" id="toggle-btn" href="#"><span></span><span></span><span></span></a>
              </div>
              
              <!-- Navbar Menu -->
              <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">
              <span>
              <form action="">
              </a><button  type="button"  onclick="window.location.href='';"class="btn btn-danger">
                  <i class="fa fa-bell">
              </i>
                 </button>
              </form>
  </span>
          
               

                <!-- Logout    -->
                <li class="nav-item"><a class="nav-link text-white" href="Logout"> <span class="d-none d-sm-inline">Logout</span>
                    <svg class="svg-icon svg-icon-xs svg-icon-heavy">
                      <use xlink:href="#security-1"> </use>
                    </svg></a></li>
              </ul>
            </div>
          </div>
        </nav>
      </header>
      <div class="page-content d-flex align-items-stretch">
        <!-- Side Navbar -->
        <nav class="side-navbar z-index-40">
          <!-- Sidebar Navidation Menus--><span class="text-uppercase text-gray-400 text-xs letter-spacing-0 mx-3 px-2 heading">Main</span>
          <ul class="list-unstyled py-4">
          <li class="sidebar-item "><a class="sidebar-link" href="user_list">
                <svg class="svg-icon svg-icon-sm svg-icon-heavy me-xl-2">
                  <use xlink:href=""> </use>
                </svg>List of Users</a></li>
            <li class="sidebar-item active"><a class="sidebar-link" href="">
                <svg class="svg-icon svg-icon-sm svg-icon-heavy me-xl-2">
                  <use xlink:href=""> </use>
                </svg>List of parents</a></li>
                <li class="sidebar-item"><a class="sidebar-link" href="update_user">
                <svg class="svg-icon svg-icon-sm svg-icon-heavy me-xl-2">
                  <use xlink:href="#sales-up-1"> </use>
                </svg>Update admin details</a></li>
                <li class="sidebar-item"><a class="sidebar-link" href="change_role">
                <svg class="svg-icon svg-icon-sm svg-icon-heavy me-xl-2">
                  <use xlink:href="#sales-up-1"> </use>
                </svg>Change User Role</a></li>
           
          </ul>

          </ul>
        </nav>
        <div class="content-inner w-100">
          <!-- Page Header-->
          <header class="bg-white shadow-sm px-4 py-3 z-index-20">
            <div class="container-fluid px-0">
              <h2 class="mb-0 p-1">Super Admin Dashboard</h2>
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
                          $count = 1;
                       
                          // Modernized: Use users table and join with schools
                          $sql = "SELECT u.*, s.school_name 
                                  FROM users u 
                                  LEFT JOIN schools s ON u.school_id = s.id 
                                  WHERE u.role = 'user'
                                  ORDER BY u.reg_date DESC";
                          $result = $conn->query($sql);

                          while ($row = $result->fetch_assoc()) {
                              ?>
                              <tr>
                              <td><?php echo $count; ?></td>
                              <td><?php  echo h($row["name"]); ?></td>
                              <td><?php  echo h($row["surname"]); ?></td>
                              <td><?php  echo h($row["email"]); ?></td>
                              <td><?php  echo h($row["alternative_email"]); ?></td>
                              <td><?php  echo h($row["role"]); ?></td>
                              <td><?php  echo h($row["phone_no"]); ?></td>
                              <td><?php  echo h($row["alternative_phone_no"]); ?></td>
                              <td><?php  echo h($row["address"]); ?></td>
                              <td><?php  echo h($row["school_name"] ?? 'N/A'); ?></td>
                              <td><?php  echo h($row["reg_date"]); ?></td>
                            </tr>
                                <?php $count++;} ?>
                                
                            </tr>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
          </section>
          
          <!-- Page Footer-->
          <footer class="position-absolute bottom-0 bg-darkBlue text-white text-center py-3 w-100 text-xs" id="footer">
            <div class="container-fluid">
              <div class="row gy-2">
                <div class="col-sm-6 text-sm-start">
                  <p class="mb-0"></p>
                </div>
                <div class="col-sm-6 text-sm-end">
                  <p class="mb-0">Design by <a href="" class="text-white text-decoration-none">KDBS Solutions.</a></p>
                  <!-- Please do not remove the backlink to us unless you support further theme's development at https://bootstrapious.com/donate. It is part of the license conditions. Thank you for understanding :)-->
                </div>
              </div>
            </div>
          </footer>
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

