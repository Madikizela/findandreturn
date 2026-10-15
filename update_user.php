<?php

include('connection.php');
// session_start(); // Already started in connection.php -> security.php
//echo $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
$email = $_SESSION['socialWorkerEmail'];
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
  header("Location: login");
  exit();
}

// Determine theme and title based on role
$role = $_SESSION['role'] ?? 'parent';
switch ($role) {
    case 'superUser':
        $themeColor = "#dc3545";
        $pageTitle = "Update Admin Details";
        break;
    case 'user':
        $themeColor = "#007bff";
        $pageTitle = "Update School Admin Details";
        break;
    default:
        $themeColor = "#007bff";
        $pageTitle = "Update Parent Details";
        break;
}
$logoPath = "img/Find&R.png";
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $pageTitle; ?> - Find and Return</title>
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
              <h2 class="mb-0 p-1"><i class="fas fa-user-edit me-2"></i><?php echo $pageTitle; ?></h2>
            </div>
          </header>
          <?php
          $_SESSION['socialWorkerEmail'];
              $id = $name = $surname = $email = $alternative_email = $phoneNo = $alternative_phoneNo=$address="";
              include('connection.php');
          	
          	//echo "connected";
          
          
          
          				//	$email = $_POST['email'];
                    $email = $_SESSION['socialWorkerEmail'];
                    // Modernized: Use users table and correct column names
          				$sql = "SELECT * FROM users WHERE email = ?";
          				$result = db_query($conn, $sql, "s", [$email]);
          
          				if($result && $result->num_rows > 0 ){
          					while($row = $result->fetch_assoc())
          						{
                                      $id = $row['id'];
          							$name = $row['name'];
          							$surname = $row['surname'];
          							$email = $row['email'];
          							$alternative_email = $row['alternative_email'];
                                      $phoneNo = $row['phone_no'];
          							$alternative_phoneNo = $row['alternative_phone_no'];
                                      $address = $row['address'];
          						}
          					}
          
          	if($_SERVER["REQUEST_METHOD"]=="POST")
          	{
          	//	echo "dbug";
          				if(isset($_POST['update'])){
          					$name = $_POST['name'];
          					$surname = $_POST['surname'];
          					$alternative_email = $_POST['alternative_email'];
                              $phoneNo = $_POST['phoneNo'];
          					$alternative_phoneNo = $_POST['alternative_phoneNo'];
                              $address = $_POST['address'];
          
          					// Modernized: Update users table with correct column names
          					$sql1 = "UPDATE users SET name=?, surname=?, alternative_email=?, phone_no=?, alternative_phone_no=?, address=? WHERE email=?";
          					$params = [$name, $surname, $alternative_email, $phoneNo, $alternative_phoneNo, $address, $email];
          					
          					if (db_query($conn, $sql1, "sssssss", $params)) {
          						echo "<script>alert('Record updated successfully');</script>";
          					} else {
          						echo "<script>alert('Error updating record.');</script>";
          					}
          				}
          
          
          	}
          
          
          ?>
          
          <div class="container-fluid">
            <div class="form-container">
                <div class="form-header">
                    <h3><i class="fas fa-user-edit me-2"></i>Update Parent Details</h3>
                    <p>Modify your personal information below and click update to save changes</p>
                </div>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-1"></i>First Name
                                </label>
                                <input class="form-control" id="name" type="text" 
                                       placeholder="First Name" required name="name" 
                                       value="<?php echo htmlspecialchars($name); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="surname" class="form-label">
                                    <i class="fas fa-user me-1"></i>Surname
                                </label>
                                <input class="form-control" id="surname" type="text" 
                                       placeholder="Surname" required name="surname" 
                                       value="<?php echo htmlspecialchars($surname); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Primary Email
                                </label>
                                <input class="form-control" id="email" type="email" 
                                       placeholder="Primary Email" required name="email" 
                                       value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="alternative_email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Alternative Email
                                </label>
                                <input class="form-control" id="alternative_email" type="email" 
                                       placeholder="Alternative Email (Optional)" name="alternative_email" 
                                       value="<?php echo htmlspecialchars($alternative_email); ?>">
                                <small class="form-text text-muted">Optional backup email address</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phoneNo" class="form-label">
                                    <i class="fas fa-phone me-1"></i>Primary Phone Number
                                </label>
                                <input class="form-control" id="phoneNo" type="tel" 
                                       placeholder="Primary Phone Number" required name="phoneNo" 
                                       value="<?php echo htmlspecialchars($phoneNo); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="alternative_phoneNo" class="form-label">
                                    <i class="fas fa-phone me-1"></i>Alternative Phone Number
                                </label>
                                <input class="form-control" id="alternative_phoneNo" type="tel" 
                                       placeholder="Alternative Phone Number (Optional)" name="alternative_phoneNo" 
                                       value="<?php echo htmlspecialchars($alternative_phoneNo); ?>">
                                <small class="form-text text-muted">Optional backup phone number</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Address
                                </label>
                                <input class="form-control" id="address" type="text" 
                                       placeholder="Full Address" name="address" 
                                       value="<?php echo htmlspecialchars($address); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" name="update" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>Update Parent Details
                        </button>
                        <a href="parent_dashboard" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>
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

