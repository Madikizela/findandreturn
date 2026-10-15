<?php

include_once('connection.php');
// session_start(); // Already started in connection.php -> security.php

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
  header("Location: login");
  exit();
}

$name = $_SESSION['user_name'] ?? '';
$email = $_SESSION['socialWorkerEmail'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch schools for dropdown
$schools_list = [];
$schools_res = $conn->query("SELECT id, school_name FROM schools ORDER BY school_name ASC");
if ($schools_res) {
    while ($row = $schools_res->fetch_assoc()) {
        $schools_list[] = $row;
    }
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
    <title>Update Child - Parent Dashboard</title>
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
              <h2 class="mb-0 p-1"><i class="fas fa-edit me-2"></i>Update Child Details</h2>
            </div>
          </header>
          <!-- Breadcrumb-->
          <div class="bg-white">
            <div class="container-fluid">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-3">
                  <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                  <li class="breadcrumb-item active fw-light" aria-current="page">Update Child</li>
                </ol>
              </nav>
            </div>
          </div>
          <?php

// Initialize variables
$id = $first_name = $surname = $parent_email = $child_phoneNo = $parent_phoneNo = $grade = $school = $p_address = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    if (isset($_POST['search'])) {
        // Sanitize and assign input values
        $first_name = trim($_POST['first_name']);
        $parent_email = trim($_POST['parent_email']);

        // Check if input fields are not empty
        if (!empty($first_name)) {
            $user_id = $_SESSION['user_id'];
            // Modernized: Query children table using parent_id
            $sql = "SELECT * FROM children WHERE first_name = ? AND parent_id = ?";
            $result = db_query($conn, $sql, "si", [$first_name, $user_id]);

            if ($result) {
                if ($result->num_rows > 0) {
                    // Fetch the result
                    $row = $result->fetch_assoc();
                    $id = $row['id'];
                    $first_name = $row['first_name'];
                    $surname = $row['surname'];
                    $grade = $row['grade'];
                    $school_id = $row['school_id'];
                    $school = $row['school'];
                } else {
                    echo "<script>alert('This child is not registered');</script>";
                }
            } else {
                echo "<script>alert('Error in query.');</script>";
            }
        } else {
            echo "<script>alert('Please enter both first name and parent email');</script>";
        }
    }

    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $first_name = $_POST['first_name'];
        $surname = $_POST['surname'];
        $grade = $_POST['grade'];
        $school_id = $_POST['school_id'];

        // Modernized: Use children table with school_id
        $sql1 = "UPDATE children SET first_name=?, surname=?, grade=?, school_id=? WHERE id=? AND parent_id=?";
        $params = [$first_name, $surname, $grade, $school_id, $id, $user_id];
        
        if (db_query($conn, $sql1, "sssiii", $params)) {
            echo "<script>alert('Record updated successfully'); window.location.href='';</script>";
        } else {
            echo "<script>alert('Error updating record.');</script>";
        }
    }
}
          
          
          ?>
          
          <div class="container-fluid">
            <div class="form-container">
                <div class="form-header">
                    <h3><i class="fas fa-search me-2"></i>Search Child</h3>
                    <p>Enter child's name to find and update their details</p>
                </div>
                
                <!-- Search Form -->
                <form method="POST" class="mb-4">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-child me-1"></i>Child's Name
                                </label>
                                <input class="form-control" id="first_name" name="first_name" type="text" 
                                       placeholder="Enter child's name" value="<?php echo h($first_name); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent_email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Parent Email
                                </label>
                                <input class="form-control" id="parent_email" name="parent_email" type="email" 
                                       value="<?php echo h($email); ?>" readonly>
                                <small class="form-text text-muted">Your registered email address</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" name="search" class="btn btn-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Search Child
                        </button>
                    </div>
                </form>

                <?php if (!empty($first_name) && !empty($surname)): ?>
                <!-- Update Form -->
                <div class="form-header mt-5">
                    <h3><i class="fas fa-edit me-2"></i>Update Child Details</h3>
                    <p>Modify the information below and click update to save changes</p>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <input type="hidden" name="id" value="<?php echo h($id); ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="update_first_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>First Name
                                </label>
                                <input class="form-control" id="update_first_name" type="text" 
                                       placeholder="First Name" name="first_name" 
                                       value="<?php echo h($first_name); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="update_surname" class="form-label">
                                    <i class="fas fa-user me-1"></i>Surname
                                </label>
                                <input class="form-control" id="update_surname" type="text" 
                                       placeholder="Surname" name="surname" 
                                       value="<?php echo h($surname); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="update_grade" class="form-label">
                                    <i class="fas fa-graduation-cap me-1"></i>Grade
                                </label>
                                <select class="form-select" id="update_grade" name="grade">
                                    <option value="">Select Grade</option>
                                    <?php for ($g = 1; $g <= 12; $g++): ?>
                                        <option value="<?php echo $g; ?>" <?php echo ($grade == $g) ? 'selected' : ''; ?>>
                                            Grade <?php echo $g; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="update_school" class="form-label">
                                    <i class="fas fa-school me-1"></i>School
                                </label>
                                <select class="form-select" id="update_school" name="school_id">
                                    <option value="">Select School</option>
                                    <?php foreach ($schools_list as $s): ?>
                                        <option value="<?php echo $s['id']; ?>" <?php echo ($school_id == $s['id']) ? 'selected' : ''; ?>>
                                            <?php echo h($s['school_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" name="update" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>Update Child Details
                        </button>
                        <a href="parent_dashboard" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
          </div>

          <!-- Page Footer-->
        </div>
      </div>
    </div>
    <!-- JavaScript files-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/just-validate/dist/just-validate.min.js"></script>
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
      // // SVG sprite removed - external resource no longer available

      $('#body').css('min-height', screen.height);
    </script>
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
  </body>
</html>

