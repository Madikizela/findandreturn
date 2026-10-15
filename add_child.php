<?php
include('connection.php');
// session_start(); // Already called in connection.php -> security.php
$name = $_SESSION['user_name'];
$email = $_SESSION['socialWorkerEmail'];
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
  header("Location: login");
  exit();
}

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
    <style>
        .progress-indicator {
            display: flex;
            justify-content: center;
            margin-block-end: 30px;
        }

        .progress-step {
            inline-size: 40px;
            block-size: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .progress-step.active {
            background: var(--primary-color);
            color: white;
        }

        .progress-step.completed {
            background: var(--success-color);
            color: white;
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
              <h2 class="mb-0 p-1"><i class="fas fa-child me-2"></i>Add Child</h2>
            </div>
          </header>
          <!-- Breadcrumb-->
          <div class="bg-white">
            <div class="container-fluid">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-3">
                  <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                  <li class="breadcrumb-item active fw-light" aria-current="page">Add Child</li>
                </ol>
              </nav>
            </div>
          </div>
          <div class="container-fluid">
            <?php
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            if (file_exists('phpqrcode/qrlib.php')) {
                include('phpqrcode/qrlib.php');
            } else {
                echo '<div class="alert alert-danger">Error: QR Code library not found at phpqrcode/qrlib.php</div>';
            }
            require_once('connection.php');

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Check if images directory is writable
                if (!is_writable('images')) {
                    echo '<div class="alert alert-danger">Error: The "images" directory is not writable. Please check permissions.</div>';
                }
                
                // Check CSRF token
                if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
                    echo '<div class="alert alert-danger">Security validation failed. Please try again.</div>';
                } else {
                    $first_names = $_POST["first_name"] ?? [];
                    $number = count($first_names);

                    if ($number === 0) {
                        echo '<div class="alert alert-warning">No learner data received. Please fill in the form.</div>';
                    } else {
                        if (!function_exists('generateUniqueReference')) {
                    function generateUniqueReference($conn) {
                        // Modernized: Use children table and unique_reference column
                        $checkStmt = $conn->prepare("SELECT unique_reference FROM children WHERE unique_reference = ?");
                        if (!$checkStmt) return (string)mt_rand(1000, 9999); // Fallback
                        
                        while (true) {
                            $reference = (string)mt_rand(1000, 9999);
                            $checkStmt->bind_param("s", $reference);
                            $checkStmt->execute();
                            $result = $checkStmt->get_result();
                            if ($result && $result->num_rows === 0) {
                                $checkStmt->close();
                                return $reference;
                            }
                        }
                    }
                }

                $successCount = 0;
                $errorCount = 0;

                $first_names = $_POST["first_name"] ?? [];
                $surnames = $_POST["surname"] ?? [];
                $school_ids = $_POST["school_id"] ?? [];
                $grades = $_POST["grade"] ?? [];
                
                // Modernized: Get parent info from session instead of POST
                $parent_email = $_SESSION['socialWorkerEmail'] ?? '';
                $parent_phoneNo = $_SESSION['phoneNo'] ?? '';

                for ($i = 0; $i < $number; $i++) {
                    // Modernized: Safely get values with fallbacks. 
                    // No need for mysqli_real_escape_string as we use prepared statements below.
                    $first_name = $first_names[$i] ?? '';
                    $surname = $surnames[$i] ?? '';
                    $school_id = intval($school_ids[$i] ?? 0);
                    $grade = $grades[$i] ?? '';

                    // Modernized: Check if the child already exists using children table and parent_id
                    $checkStmt = $conn->prepare("SELECT id FROM children WHERE first_name = ? AND parent_id = ?");
                    $checkStmt->bind_param("si", $first_name, $user_id);
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();

                    if ($checkResult && $checkResult->num_rows > 0) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Error:</strong> A child with the name "' . htmlspecialchars($first_name) . '" already exists for this parent.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
                        $errorCount++;
                    } else {
                        // Generate a unique reference number
                        $uniqueReference = generateUniqueReference($conn);
                        $path = "images/";
                        $file = $path . uniqid() . ".png";

                        $ecc = 'H';  // High error correction (30%) - required when logo overlays QR
                        $pixel_Size = 10;
                        $frame_Size = 2;

                        // Generate QR code for the reference number
                        try {
                            QRcode::png($uniqueReference, $file, $ecc, $pixel_Size, $frame_Size);
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">QR Code generation failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            $errorCount++;
                            continue;
                        }

                        $reg_date = date('Y-m-d H:i:s');

                        // Modernized: Insert into children table
                        $stmt = $conn->prepare("INSERT INTO children (parent_id, school_id, first_name, surname, grade, unique_reference, qr_image, reg_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        if ($stmt === false) {
                            echo '<div class="alert alert-danger">Prepare failed: ' . htmlspecialchars($conn->error) . '</div>';
                            $errorCount++;
                            continue;
                        }
                        
                        $stmt->bind_param("iissssss", $user_id, $school_id, $first_name, $surname, $grade, $uniqueReference, $file, $reg_date);
                        
                        if ($stmt->execute()) {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Success!</strong> Child "' . htmlspecialchars($first_name) . ' ' . htmlspecialchars($surname) . '" has been successfully registered with reference number: <strong>' . $uniqueReference . '</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                  </div>';
                            $successCount++;

                            // Send email notification
                            $to = $parent_email;
                            
                            // WhatsApp Notification — template: child_registered [firstName, surname, referenceNumber]
                            $wa_phone = preg_replace('/[^0-9]/', '', $parent_phoneNo ?? '');
                            if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
                                $wa_phone = '27' . substr($wa_phone, 1);
                            }
                            error_log("add_child WA: parent_phoneNo=$parent_phoneNo, wa_phone=$wa_phone");
                            $wa_result = send_whatsapp($wa_phone, '', true, 'child_registered', [$first_name, $surname, $uniqueReference], 'en_US');
                            global $last_whatsapp_error;
                            error_log("add_child WA result=" . ($wa_result ? 'YES' : 'NO') . ", error=" . ($last_whatsapp_error ?? 'none'));

                            $from = 'no-reply@findandreturn.co.za'; 
                            $fromName = 'Find and Return Team'; 
                            $subject = 'Child Registration Confirmation - Find and Return'; 
                            
                            $htmlContent = '
                            <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
                                <h2 style="color: #333;">🎉 Congratulations!</h2>
                                <p><strong>' . htmlspecialchars($first_name) . ' ' . htmlspecialchars($surname) . '</strong> has been successfully added to your profile.</p>
                                
                                <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
                                    <h3 style="margin-top: 0;">Unique Reference Number: <span style="color: #007bff; font-weight: bold;">' . $uniqueReference . '</span></h3>
                                    <p>This reference number works the same as the QR code for item tracking.</p>
                                </div>
                                
                                <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                                    <h4 style="margin-top: 0; color: #856404;">📋 Important Information:</h4>
                                    <ul>
                                        <li>Attached is a unique QR Code for easy item tracking</li>
                                        <li>You can order labels from us or print your own</li>
                                        <li>QR Code works on Iron-on stickers, fabric labels, and sticker paper</li>
                                        <li>Follow our announcements for printing partner updates</li>
                                    </ul>
                                </div>
                                
                                <p>Kind Regards,<br><strong>Find and Return Team</strong></p>
                            </div>'; 
                            
                            // Use the central send_email function which handles SMTP correctly
                            $mail = send_email($to, $subject, $htmlContent, $file); 
                            
                            if ($mail) {
                                echo '<div class="alert alert-info alert-dismissible fade show" role="alert">
                                        <i class="fas fa-envelope me-2"></i>
                                        <strong>Email Sent:</strong> Registration confirmation sent to ' . htmlspecialchars($parent_email) . '
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                      </div>';
                            } else {
                                echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                        <i class="fas fa-envelope-open-text me-2"></i>
                                        <strong>Email Notice:</strong> Registration successful, but confirmation email could not be sent.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                      </div>';
                            }

                            $child_id = $conn->insert_id;
                            
                            // Modernized: Use prepared statement for shopping_basket
                            $basketStmt = $conn->prepare("INSERT INTO shopping_basket (id, child_name, parent_email, qrImage) VALUES (?, ?, ?, ?)");
                            if ($basketStmt) {
                                $basketStmt->bind_param("isss", $child_id, $first_name, $parent_email, $file);
                                if (!$basketStmt->execute()) {
                                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Warning:</strong> Error updating shopping basket: ' . htmlspecialchars($basketStmt->error) . '
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                          </div>';
                                }
                                $basketStmt->close();
                            }
                        } else {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <strong>Error:</strong> Failed to register child "' . htmlspecialchars($first_name) . '": ' . htmlspecialchars($stmt->error) . '
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                  </div>';
                            $errorCount++;
                        }
                    }
                }

                if ($successCount > 0) {
                    echo '<div class="alert alert-success">
                            <strong>Registration Complete!</strong> Successfully registered ' . $successCount . ' child(ren). 
                            <a href="parent_dashboard" class="btn btn-sm btn-primary ms-3">
                                <i class="fas fa-arrow-left me-1"></i>Go to Dashboard
                            </a>
                          </div>';
                }
                } // End if number > 0
                } // End CSRF check else
            }
            ?>

            <div class="form-container">
                <div class="form-header">
                    <h3><i class="fas fa-child me-2"></i>Add Child Registration</h3>
                    <p>Fill in the details below to register your child(ren) for the Find and Return service</p>
                </div>

                <?php if (isset($_SESSION['number'])): ?>
                <div class="progress-indicator">
                    <?php for ($i = 1; $i <= $_SESSION['number']; $i++): ?>
                        <div class="progress-step <?php echo $i == 1 ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </div>
                    <?php endfor; ?>
                </div>

                <form method="POST" action="add_child">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <?php
                    $number = $_SESSION['number'];
                    for ($i = 1; $i <= $number; $i++) {
                        echo '<div class="child-section">';
                        echo '<h4><i class="fas fa-user-graduate me-2"></i>Child ' . $i . ' Details</h4>';
                        
                        echo '<div class="row">';
                        echo '<div class="col-md-6">';
                        echo '<div class="floating-label">';
                        echo '<input class="form-control" type="text" name="first_name[]" required placeholder=" " oninput="capitalizeFirstLetter(this)">';
                        echo '<label><i class="fas fa-user me-1"></i>First Name <span class="required">*</span></label>';
                        echo '<div class="invalid-feedback">Please enter the first name.</div>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '<div class="col-md-6">';
                        echo '<div class="floating-label">';
                        echo '<input class="form-control" type="text" name="surname[]" required placeholder=" " oninput="capitalizeFirstLetter(this)">';
                        echo '<label><i class="fas fa-user me-1"></i>Surname <span class="required">*</span></label>';
                        echo '<div class="invalid-feedback">Please enter the surname.</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';


                        echo '<div class="row">';
                        echo '<div class="col-md-6">';
                        echo '<div class="floating-label">';
                        echo '<select class="form-select school-select" name="school_id[]" required>';
                        echo '<option value="">Select School</option>';
                        foreach ($schools_list as $s) {
                            echo '<option value="' . $s['id'] . '">' . htmlspecialchars($s['school_name']) . '</option>';
                        }
                        echo '</select>';
                        echo '<label><i class="fas fa-school me-1"></i>School <span class="required">*</span></label>';
                        echo '<div class="invalid-feedback">Please select a school.</div>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '<div class="col-md-6">';
                        echo '<div class="floating-label">';
                        echo '<select class="form-select grade-select" name="grade[]" required>';
                        echo '<option value="">Select Grade</option>';
                        for ($grade_num = 1; $grade_num <= 12; $grade_num++) {
                            echo '<option value="' . $grade_num . '">Grade ' . $grade_num . '</option>';
                        }
                        echo '</select>';
                        echo '<label><i class="fas fa-graduation-cap me-1"></i>Grade <span class="required">*</span></label>';
                        echo '<div class="invalid-feedback">Please select a grade.</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '</div>';
                    }
                    ?>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Register Child(ren)
                        </button>
                        <a href="parent_dashboard" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>

                <div class="mt-5">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>What happens after registration?</h5>
                        <ul class="mb-0">
                            <li>Each child will receive a unique QR code and reference number</li>
                            <li>You'll receive an email confirmation with the QR code attached</li>
                            <li>The QR code can be printed on various materials for item tracking</li>
                            <li>Your child's information will be available in your dashboard</li>
                        </ul>
                    </div>
                </div>

                <?php else: ?>
                <div class="text-center">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>No children selected!</strong> Please go back and select the number of children to register.
                    </div>
                    <a href="x" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Selection
                    </a>
                </div>
                <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
        function validateForm() {
            let isValid = true;
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('[required]');
            
            inputs.forEach(input => {
                input.classList.remove('is-invalid');
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                }
            });

            if (!isValid) {
                // Scroll to first invalid input
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            return isValid;
        }

        function capitalizeFirstLetter(input) {
            input.value = input.value.replace(/\b\w/g, (char) => char.toUpperCase());
        }

        // Handle dynamic grades based on school selection
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('school-select')) {
                const schoolSelect = e.target;
                const schoolName = schoolSelect.value.toLowerCase();
                const childSection = schoolSelect.closest('.child-section');
                const gradeSelect = childSection.querySelector('.grade-select');
                
                // Save current grade selection
                const currentGrade = gradeSelect.value;
                
                // Clear existing options except the first one
                gradeSelect.innerHTML = '<option value="">Select Grade</option>';
                
                let startGrade = 1;
                let endGrade = 12;
                
                if (schoolName.includes('high school')) {
                    startGrade = 8;
                    endGrade = 12;
                } else if (schoolName.includes('primary school')) {
                    startGrade = 1;
                    endGrade = 7;
                }
                
                for (let i = startGrade; i <= endGrade; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = 'Grade ' + i;
                    if (i.toString() === currentGrade) {
                        option.selected = true;
                    }
                    gradeSelect.appendChild(option);
                }
            }
        });

        // Progress indicator functionality
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, select');
            const progressSteps = document.querySelectorAll('.progress-step');
            
            inputs.forEach((input, index) => {
                input.addEventListener('focus', function() {
                    const childIndex = Math.floor(index / 8); // 8 fields per child
                    progressSteps.forEach((step, stepIndex) => {
                        step.classList.remove('active', 'completed');
                        if (stepIndex < childIndex) {
                            step.classList.add('completed');
                        } else if (stepIndex === childIndex) {
                            step.classList.add('active');
                        }
                    });
                });
            });
        });
    </script>

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

    </script>
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
  </body>
</html>

