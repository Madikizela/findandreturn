<?php

include('connection.php');
// session_start(); // Already started in connection.php -> security.php
//echo $_SESSION['school'];
$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
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
    <title>Report Found Item - Parent Dashboard</title>
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
              <h2 class="mb-0 p-1"><i class="fas fa-hand-holding-heart me-2"></i>Report Found Item</h2>
            </div>
          </header>
          <!-- Breadcrumb-->
          <div class="bg-white">
            <div class="container-fluid">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-3">
                  <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                  <li class="breadcrumb-item active fw-light" aria-current="page">Report Found Item</li>
                </ol>
              </nav>
            </div>
          </div>
          <div class="container-fluid">
            <?php
            // Handle form submission
            if(isset($_POST['submit'])) {
                $id = $_POST['id'];
                $child_name = $_POST['child_name'];

                $stmt = $conn->prepare("UPDATE new_lost_items SET tracking_status = 'found' WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Success!</strong> Item has been marked as found successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';

                    // Get parent contact details to send notification
                    $notifSql = "SELECT nli.item_name, c.first_name, c.surname, u.email as parent_email, 
                                        u.phone_no as parent_phoneNo, s.school_name as school
                                 FROM new_lost_items nli
                                 LEFT JOIN children c ON nli.child_id = c.id
                                 LEFT JOIN users u ON c.parent_id = u.id
                                 LEFT JOIN schools s ON nli.school_id = s.id
                                 WHERE nli.id = ?";
                    $notifStmt = $conn->prepare($notifSql);
                    $notifStmt->bind_param("i", $id);
                    $notifStmt->execute();
                    $notifResult = $notifStmt->get_result();
                    if ($notifResult && $notifResult->num_rows > 0) {
                        $notifRow = $notifResult->fetch_assoc();
                        $whatsappMsg = "Great news! Your lost item (" . $notifRow['item_name'] . ") for " 
                                     . $notifRow['first_name'] . " " . $notifRow['surname'] 
                                     . " has been found at " . $notifRow['school'] . ". Please arrange collection.";
                        // WhatsApp Notification — template: item_found_notification [itemName, childFullName, school]
                        $wa_phone = preg_replace('/[^0-9]/', '', $notifRow['parent_phoneNo'] ?? '');
                        if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
                            $wa_phone = '27' . substr($wa_phone, 1);
                        }
                        send_whatsapp($wa_phone, '', true, 'item_found_notification', [$notifRow['item_name'], $notifRow['first_name'] . ' ' . $notifRow['surname'], $notifRow['school']], 'en_US');
                    }
                } else {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Error:</strong> ' . $conn->error . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
                }
            }
            ?>

            <div class="form-container">
                <div class="form-header">
                    <h3><i class="fas fa-hand-holding-heart me-2"></i>Report Found Item</h3>
                    <p>Select the child and item that has been found to update its status</p>
                </div>

                <form action="" method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id" class="form-label">
                                    <i class="fas fa-child me-1"></i>Select Child Name
                                </label>
                                <select name="id" id="id" class="form-select" required>
                                    <option value="">Choose a child...</option>
                                    <?php
                                    // Get user_id from email
                                    $userRow = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "' LIMIT 1");
                                    $userId = $userRow && $userRow->num_rows > 0 ? $userRow->fetch_assoc()['id'] : 0;

                                    $query = "SELECT nli.id, CONCAT(c.first_name, ' ', c.surname) as child_name
                                              FROM new_lost_items nli
                                              LEFT JOIN children c ON nli.child_id = c.id
                                              WHERE c.parent_id = ? AND nli.tracking_status = 'lost'";
                                    $qStmt = $conn->prepare($query);
                                    $qStmt->bind_param("i", $userId);
                                    $qStmt->execute();
                                    $result = $qStmt->get_result();
                                    if($result->num_rows > 0) {
                                        while($optionData = $result->fetch_assoc()) {
                                            $option = $optionData['child_name'];
                                            $id = $optionData['id'];
                                    ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($option); ?></option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Only shows children with registered lost items
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item" class="form-label">
                                    <i class="fas fa-box me-1"></i>Select Found Item
                                </label>
                                <select name="item" id="item" class="form-select" required>
                                    <option value="">Choose an item...</option>
                                    <?php
                                    $query2 = "SELECT nli.id, nli.item_name as item
                                               FROM new_lost_items nli
                                               LEFT JOIN children c ON nli.child_id = c.id
                                               WHERE c.parent_id = ? AND nli.tracking_status = 'lost'";
                                    $qStmt2 = $conn->prepare($query2);
                                    $qStmt2->bind_param("i", $userId);
                                    $qStmt2->execute();
                                    $result = $qStmt2->get_result();
                                    if($result->num_rows > 0) {
                                        while($optionData = $result->fetch_assoc()) {
                                            $option = $optionData['item'];
                                    ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Only shows items that are currently marked as lost
                                </small>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="child_name" value="<?php echo isset($option) ? htmlspecialchars($option) : ''; ?>">
                    <input type="hidden" name="item" value="<?php echo isset($option) ? htmlspecialchars($option) : ''; ?>">

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check-circle me-2"></i>Mark Item as Found
                        </button>
                        <a href="parent_dashboard" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>

                <div class="mt-5">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-lightbulb me-2"></i>How it works:</h5>
                        <ul class="mb-0">
                            <li>Select the child who lost the item</li>
                            <li>Choose the specific item that was found</li>
                            <li>Click "Mark Item as Found" to update the status</li>
                            <li>The system will automatically notify the parent via email</li>
                        </ul>
                    </div>
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

    </script>
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
  </body>
</html>

