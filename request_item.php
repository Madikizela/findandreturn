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
    <title>Report Lost Item - Parent Dashboard</title>
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
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }

        .form-header h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
            margin: 0;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
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
              <h2 class="mb-0 p-1"><i class="fas fa-exclamation-triangle me-2"></i>Report Lost Item</h2>
            </div>
          </header>
          <!-- Breadcrumb-->
          <div class="bg-white">
            <div class="container-fluid">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-3">
                  <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                  <li class="breadcrumb-item active fw-light" aria-current="page">Report Lost Item</li>
                </ol>
              </nav>
            </div>
          </div>
          
          <div class="container-fluid">
            <?php
            include('connection.php');
            $email = $_SESSION['socialWorkerEmail'];
            $user_id = $_SESSION['user_id'];

            if(isset($_POST['submit']))
            {
            $child_id = $_POST['id'];
            
            // Modernized: Get child from children table with school_id
            $sql1 = "SELECT c.*, s.school_name FROM children c 
                     LEFT JOIN schools s ON c.school_id = s.id 
                     WHERE c.id = ?";
            $result = db_query($conn, $sql1, "i", [$child_id]);
            
            $school_id = null;
            if($result && $result->num_rows>0)
            {
            $row = $result->fetch_assoc();
            $child_name = trim($row['first_name'] . ' ' . $row['surname']);
            $school = $row['school_name'] ?? '';
            $school_id = $row['school_id'];
            }
            
            $item=$_POST['item'];
            $description=$_POST['description'];
            $date_lost = !empty($_POST['date_lost']) ? $_POST['date_lost'] : date('Y-m-d');
            
            // Function to generate a single unique random number
            function generateUniqueRandomNumber($min, $max, $existingNumbers) {
              $uniqueNumber = null;

              while ($uniqueNumber === null) {
                  $randomNumber = mt_rand($min, $max);

                  // Check if the number is not already in the existing numbers array
                  if (!in_array($randomNumber, $existingNumbers)) {
                      $uniqueNumber = $randomNumber;
                  }
              }

              return $uniqueNumber;
            }

            // Existing user IDs (you would replace this with your actual data)
            $existingUserIDs = [1001, 2002, 3003, 4004, 5005];

            // Generate a unique random number within the specified range
            $newUserID = generateUniqueRandomNumber(1000, 9999, $existingUserIDs);

            // Modernized: Insert into new_lost_items (no parent_id column)
            $lost_item_sql = "INSERT into new_lost_items (`child_id`,`school_id`,`item_name`, `description`, `date_lost`, `unique_report_id`)
            VALUES (?, ?, ?, ?, ?, ?)";
            
            if (db_query($conn, $lost_item_sql, "iissss", [$child_id, $school_id, $item, $description, $date_lost, $newUserID])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                      <i class="fas fa-check-circle me-2"></i>
                      <strong>Success!</strong> Lost item report submitted successfully for ' . htmlspecialchars($child_name) . ' at ' . htmlspecialchars($school) . '
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';

                // Fetch child and user details to get contact info
                $sql = "SELECT c.first_name, c.surname, u.email, u.phone_no, s.school_name 
                        FROM children c 
                        LEFT JOIN users u ON c.parent_id = u.id 
                        LEFT JOIN schools s ON c.school_id = s.id 
                        WHERE c.id = ?";
                $result = db_query($conn, $sql, "i", [$child_id]);
                
                if ($result && $row = $result->fetch_assoc()) {
                    $child_name = trim($row['first_name'] . ' ' . $row['surname']);
                    $parent_email = $row['email'];
                    $parent_phoneNo = $row['phone_no'];
                    $school = $row['school_name'] ?? '';

                    // WhatsApp Notification — template: lost_item_reported [item, childName, school]
                    // Ensure number is cleaned and formatted
                    $whatsapp_phone = preg_replace('/[^0-9]/', '', $parent_phoneNo ?? '');
                    if (strlen($whatsapp_phone) === 10 && strpos($whatsapp_phone, '0') === 0) {
                        $whatsapp_phone = '27' . substr($whatsapp_phone, 1);
                    }
                    $whatsappMessage = "Hello! You have reported a lost item (" . $item . ") for " . $child_name . " at " . $school . ". We will notify you if it is found.";
                    
                    file_put_contents('whatsapp_debug.log', "[" . date('Y-m-d H:i:s') . "] Attempting WhatsApp to $whatsapp_phone. Msg: $whatsappMessage\n", FILE_APPEND);
                    
                    $ws_sent = send_whatsapp($whatsapp_phone, '', true, 'lost_item_reported', [$item, $child_name, $school], 'en_US');

                    if (!$ws_sent) {
                        global $last_whatsapp_error;
                        file_put_contents('whatsapp_debug.log', "[" . date('Y-m-d H:i:s') . "] WhatsApp FAILED to $whatsapp_phone. Error: $last_whatsapp_error\n", FILE_APPEND);
                        error_log("WhatsApp failed to send for parent phone: " . $parent_phoneNo . " Error: " . $last_whatsapp_error);
                    } else {
                        file_put_contents('whatsapp_debug.log', "[" . date('Y-m-d H:i:s') . "] WhatsApp SUCCESS to $whatsapp_phone\n", FILE_APPEND);
                    }

                    // Email Notification
                    $to = $parent_email;
                    $from = 'admin@findandreturn.co.za';
                    $fromName = 'Find and Return Team';
                    $subject = 'Lost Item Reported - Find and Return';
            
                    $htmlContent = '
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
                        <h2 style="color: #333;">📢 Lost Item Reported</h2>
                        <p>Hello,</p>
                        <p>You have successfully reported a lost item for <strong>' . htmlspecialchars($child_name) . '</strong> at <strong>' . htmlspecialchars($school) . '</strong>.</p>
                        
                        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
                            <h4 style="margin-top: 0;">Item Details:</h4>
                            <ul style="list-style: none; padding-left: 0;">
                                <li><strong>Item:</strong> ' . htmlspecialchars($item) . '</li>
                                <li><strong>Description:</strong> ' . htmlspecialchars($description) . '</li>
                                <li><strong>Date Lost:</strong> ' . htmlspecialchars($date_lost) . '</li>
                            </ul>
                        </div>
                        
                        <p>We will notify you immediately if this item is found and returned to the school help desk.</p>
                        
                        <p>Kind Regards,<br><strong>Find and Return Team</strong></p>
                    </div>'; 

                    // Use the central send_email function (PHPMailer SMTP) instead of basic mail()
                    require_once 'mailer_utils.php';
                    $mail = send_email($to, $subject, $htmlContent);  

                    // Email sending status 
                    if($mail) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-envelope me-2"></i>
                                <strong>Email Sent!</strong> Confirmation email with tracking details has been sent to ' . htmlspecialchars($parent_email) . '
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
                    } else {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> Email sending failed, but your report has been submitted successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
                    }
                } else {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Error:</strong> Could not retrieve parent contact information.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
                }
                exit();
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
                    <h3><i class="fas fa-exclamation-triangle me-2"></i>Report Lost Item</h3>
                    <p>Fill in the details below to report a lost item for your child</p>
                </div>

                <form action="" method="post" onsubmit="return validateForm()">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="child" class="form-label"><i class="fas fa-child me-1"></i>Select Child <span class="required">*</span></label>
                                <select name="id" id="child" class="form-select" required>
                                    <option value="">Choose your child...</option>
                                    <?php
                                    // Modernized: Get children from children table using parent_id
                                    $query = "SELECT id, first_name, surname FROM children WHERE parent_id = ?";
                                    $result = db_query($conn, $query, "i", [$user_id]);
                                    
                                    if($result && $result->num_rows > 0){
                                        while($optionData = $result->fetch_assoc()){
                                            $id = $optionData['id'];
                                            $option = trim($optionData['first_name'] . ' ' . $optionData['surname']);
                                    ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($option); ?></option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">Select the child who lost the item</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item" class="form-label"><i class="fas fa-tshirt me-1"></i>Lost Item <span class="required">*</span></label>
                                <select name="item" id="item" class="form-select" required>
                                    <option value="">Select item type...</option>
                                    <option value="Shoes">👟 Shoes</option>
                                    <option value="Shirt">👕 Shirt</option>
                                    <option value="Tie">👔 Tie</option>
                                    <option value="Blazer">🧥 Blazer</option>
                                    <option value="Cardigan">🧶 Cardigan</option>
                                    <option value="Socks">🧦 Socks</option>
                                    <option value="Jersey">🏈 Jersey</option>
                                    <option value="Trouser">👖 Trouser</option>
                                    <option value="Bag">🎒 Bag</option>
                                    <option value="Water Bottle">💧 Water Bottle</option>
                                    <option value="Lunch Box">🍱 Lunch Box</option>
                                    <option value="Stationery">✏️ Stationery</option>
                                    <option value="Other">📦 Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label"><i class="fas fa-align-left me-1"></i>Description <span class="required">*</span></label>
                        <textarea class="form-control" name="description" id="description" rows="4" placeholder="Please provide details about the item (size, color, brand, last seen location, etc.)" required></textarea>
                        <small class="form-text text-muted">Include size, color, brand, and where it was last seen</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_lost" class="form-label"><i class="fas fa-calendar-alt me-1"></i>Date Last Seen <span class="required">*</span></label>
                                <input type="date" class="form-control" name="date_lost" id="date_lost" required>
                                <small class="form-text text-muted">When did you last see this item?</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-user me-1"></i>Parent Information</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($name); ?>" readonly>
                                <small class="form-text text-muted">This report will be submitted under your account</small>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Lost Item Report
                        </button>
                        <a href="parent_dashboard" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>

                <div class="mt-5">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>What happens after you submit a report?</h5>
                        <ul class="mb-0">
                            <li>Your report will be assigned to a Find and Return agent</li>
                            <li>You'll receive an email confirmation with tracking details</li>
                            <li>Our team will search the school premises for your item</li>
                            <li>You'll get updates within 48 hours</li>
                            <li>You can track progress through your dashboard</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-lightbulb me-2"></i>Tips for better item recovery:</h5>
                        <ul class="mb-0">
                            <li>Provide detailed descriptions including size, color, and brand</li>
                            <li>Mention specific locations where the item was last seen</li>
                            <li>Include any unique identifying features</li>
                            <li>Report as soon as possible after noticing the item is missing</li>
                        </ul>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
        function validateForm() {
            let isValid = true;
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                alert('Please fill in all required fields marked with *');
            }

            return isValid;
        }

        // Set max date to today
        document.getElementById('date_lost').max = new Date().toISOString().split('T')[0];

        // Auto-focus on first field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('child').focus();
        });
    </script>

    <!-- JavaScript files-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <!-- Main File-->
    <script src="js/front.js"></script>
    <?php include 'pwa-footer.php'; ?>
  </body>
</html>

