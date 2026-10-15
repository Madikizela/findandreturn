<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php
$error = null;

// Use default Find and Return theme for parents
$themeColor = "#007bff";
$logoPath = "img/Find&R.png";
$isLoggedIn = isset($_SESSION['socialWorkerEmail']);

if($_SERVER["REQUEST_METHOD"] == "POST")
{
  $qrImage = $_POST['qrImage'];
  
  // Use prepared statements for searching
  $query_s = "SELECT c.*, s.school_name as school, u.phone_no as parent_phoneNo, u.email as parent_email 
              FROM children c 
              LEFT JOIN schools s ON c.school_id = s.id 
              LEFT JOIN users u ON c.parent_id = u.id 
              WHERE (c.qr_image LIKE ? OR c.unique_reference LIKE ?) AND c.status = 'active'";
              
  $query_sub = "SELECT c.*, s.school_name as school, u.phone_no as parent_phoneNo, u.email as parent_email 
                FROM children c 
                LEFT JOIN schools s ON c.school_id = s.id 
                LEFT JOIN users u ON c.parent_id = u.id 
                WHERE (c.qr_image LIKE ? OR c.unique_reference LIKE ?) AND c.status = 'inactive'";
  
  $result = db_query($conn, $query_s, "ss", ["%$qrImage%", "%$qrImage%"]);
  $result1 = db_query($conn, $query_sub, "ss", ["%$qrImage%", "%$qrImage%"]);

  if($result && $result->num_rows > 0)
  {
      while($row = $result->fetch_assoc())
      {
          
          $_SESSION['qrImage'] = $qrImage;
            $first_name = $row['first_name'];
             $surname = $row['surname'];
             $school = $row['school'] ?? '';
             $grade = $row['grade'];
             $parent_phoneNo=$row['parent_phoneNo'];
             $parent_email=$row['parent_email'];
        

            $_SESSION['qrImage'] = $qrImage;
            $_SESSION['grade'] = $grade;
             header("Location: massege");
             exit();
           }  
      }
      else if($result1 ->num_rows>0)
      {
       echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
               <i class="fas fa-exclamation-triangle me-2"></i>
               <strong>Subscription Required:</strong> The user for this QR code did not pay for subscription, so we cannot keep their item.
               <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
             </div>';
       $sql = "SELECT c.*, s.school_name as school, u.phone_no as parent_phoneNo, u.email as parent_email 
               FROM children c 
               LEFT JOIN schools s ON c.school_id = s.id 
               LEFT JOIN users u ON c.parent_id = u.id 
               WHERE c.qr_image LIKE ? OR c.unique_reference LIKE ?";
        $r = db_query($conn, $sql, "ss", ["%$qrImage%", "%$qrImage%"]);
        if($r && $r ->num_rows>0)
    {
        while($row = $r->fetch_assoc())
        {
             $first_name = $row['first_name'];
             $surname = $row['surname'];
             $school = $row['school'] ?? '';
             $grade = $row['grade'];
             $parent_phoneNo=$row['parent_phoneNo'];
             $parent_email=$row['parent_email'];
          
             $to =$parent_email;
             $subject ="Found and Return Desk - Subscription Required";
             
             $htmlContent = '
             <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
                 <h2 style="color: #333;">📦 Item Found - Action Required</h2>
                 
                 <div style="background-color: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;">
                     <h3 style="margin-top: 0; color: #856404;">Item Details</h3>
                     <p><strong>Child:</strong> ' . htmlspecialchars($first_name) . ' ' . htmlspecialchars($surname) . '</p>
                     <p><strong>School:</strong> ' . htmlspecialchars($school) . '</p>
                     <p><strong>Grade:</strong> ' . htmlspecialchars($grade) . '</p>
                 </div>
                 
                 <div style="background-color: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0;">
                     <h4 style="margin-top: 0; color: #721c24;">⚠️ Important Notice</h4>
                     <p>Your child\'s item has been found, but in order to keep it for you, please renew your subscription account.</p>
                     <p>Please contact us immediately to arrange collection and subscription renewal.</p>
                 </div>
                 
                 <p>Kind Regards,<br><strong>Find and Return Team</strong></p>
             </div>';

              $header = "From: Find and Return <admin@findandreturn.co.za>\r\n";
              $header .= "Reply-To: admin@findandreturn.co.za\r\n";
              $header .= "MIME-Version: 1.0\r\n";
              $header .= "Content-type: text/html; charset=UTF-8\r\n";
              
              $retval = mail ($to,$subject,$htmlContent,$header);
              
              // WhatsApp Notification — template: item_found_notification [itemName, childFullName, school]
              $wa_phone = preg_replace('/[^0-9]/', '', $parent_phoneNo ?? '');
              if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
                  $wa_phone = '27' . substr($wa_phone, 1);
              }
              send_whatsapp($wa_phone, '', true, 'item_found_notification', [$first_name . "'s item", $first_name . ' ' . $surname, $school], 'en_US');
              
              if( $retval == true ) {
                 echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                         <i class="fas fa-envelope me-2"></i>
                         <strong>Email Sent:</strong> Notification sent to parent about subscription requirement.
                         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                       </div>';
              }else {
                 echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                         <i class="fas fa-exclamation-triangle me-2"></i>
                         <strong>Warning:</strong> Email notification could not be sent.
                         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                       </div>';
              }
            }   
    }
     
     
  }
   else{
         
          echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <i class="fas fa-times-circle me-2"></i>
                  <strong>Error:</strong> QR Code not linked to Find And Return.
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 3000);</script>";
      }
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>QR Code Scanner - Find and Return</title>
    <meta name="description" content="Scan QR codes to identify lost items">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="all,follow">
    <?php include 'pwa-head.php'; ?>
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
    <script src="html5-qrcode.min.js"></script>
    <?php include 'header_css.php'; ?>
    <style>
      .scanner-container {
          max-width: 800px;
          margin: 20px auto;
          padding: 30px;
          background-color: #ffffff;
          border-radius: 12px;
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }

      .scanner-header {
          text-align: center;
          margin-bottom: 30px;
          padding-bottom: 20px;
          border-bottom: 2px solid #f8f9fa;
      }

      .scanner-header h3 {
          color: #333;
          margin-bottom: 10px;
      }

      .scanner-header p {
          color: #666;
          margin: 0;
      }

      .qr-reader-container {
          display: flex;
          justify-content: center;
          margin: 30px 0;
      }

      #reader {
          border: 2px solid var(--primary-color);
          border-radius: 12px;
          overflow: hidden;
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      .scan-result-container {
          background: #f8f9fa;
          border: 2px solid #e9ecef;
          border-radius: 12px;
          padding: 25px;
          margin: 20px 0;
          text-align: center;
      }

      .scan-result-container h4 {
          color: var(--primary-color);
          margin-bottom: 20px;
          font-weight: 600;
      }

      .form-control {
          border: 2px solid #e9ecef;
          border-radius: 8px;
          padding: 12px 15px;
          transition: all 0.3s ease;
          font-size: 16px;
      }

      .form-control:focus {
          border-color: var(--primary-color);
          box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
      }

      .btn-info {
          background-color: var(--info-color);
          border-color: var(--info-color);
          padding: 12px 30px;
          border-radius: 8px;
          font-weight: 600;
          transition: all 0.3s ease;
      }

      .btn-info:hover {
          background-color: #138496;
          border-color: #117a8b;
          transform: translateY(-2px);
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      }

      .scanner-instructions {
          background: #e3f2fd;
          border: 2px solid #bbdefb;
          border-radius: 12px;
          padding: 20px;
          margin: 20px 0;
      }

      .scanner-instructions h5 {
          color: #1976d2;
          margin-bottom: 15px;
      }

      .scanner-instructions ul {
          margin-bottom: 0;
          padding-left: 20px;
      }

      .scanner-instructions li {
          margin-bottom: 8px;
      }

      .logo-container {
          text-align: center;
          margin-bottom: 20px;
      }

      .logo-container img {
          max-width: 120px;
          height: auto;
          border-radius: 10px;
      }

      @media (max-width: 768px) {
          .scanner-container {
              margin: 10px;
              padding: 20px;
          }
          
          #reader {
              width: 100% !important;
              max-width: 400px;
          }
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
              <h2 class="mb-0 p-1"><i class="fas fa-qrcode me-2"></i>QR Code Scanner & Search</h2>
            </div>
          </header>
          <!-- Breadcrumb-->
          <div class="bg-white">
            <div class="container-fluid">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-3">
                  <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                  <li class="breadcrumb-item active fw-light" aria-current="page">QR Scanner</li>
                </ol>
              </nav>
            </div>
          </div>
          
          <div class="container-fluid">
            <div class="scanner-container">
                <div class="scanner-header">
                    <div class="logo-container">
                        <img src="img/f-icon.ico" alt="Find and Return Logo" style="width: 80px; height: 80px; border-radius: 10px;">
                    </div>
                    <h3><i class="fas fa-qrcode me-2"></i>QR Code Scanner & Search</h3>
                    <p>Scan QR codes with your camera or manually enter codes to identify lost items</p>
                </div>

                <div class="scanner-instructions">
                    <h5><i class="fas fa-info-circle me-2"></i>How to use the scanner:</h5>
                    <ul>
                        <li><strong>Option 1 - Scan QR Code:</strong> Click "Start Camera" below, allow camera access when prompted, then point your camera at the QR code</li>
                        <li><strong>Option 2 - Manual Entry:</strong> Type the QR code or reference number directly into the text box</li>
                    </ul>
                </div>

                <!-- Option 1: Camera Scanner -->
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Option 1 — Scan QR Code</h5>
                    </div>
                    <div class="card-body text-center">
                        <div id="reader-wrapper">
                            <button id="startCameraBtn" class="btn btn-primary btn-lg mb-3" onclick="startCamera()">
                                <i class="fas fa-camera me-2"></i>Start Camera
                            </button>
                            <p class="text-muted small">Click to open your camera and scan a QR code</p>
                        </div>
                        <div id="reader" style="width:100%; max-width:500px; margin:0 auto; display:none;"></div>
                        <button id="stopCameraBtn" class="btn btn-outline-danger btn-sm mt-2" onclick="stopCamera()" style="display:none;">
                            <i class="fas fa-stop me-1"></i>Stop Camera
                        </button>
                    </div>
                </div>

                <!-- Option 2: Manual Entry -->
                <div class="card mb-4 border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-keyboard me-2"></i>Option 2 — Manual Entry</h5>
                    </div>
                    <div class="card-body">
                        <form class="scanner-form" method="POST" action="">
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-qrcode"></i></span>
                                        <input type="text"
                                               id="result"
                                               class="form-control"
                                               name="qrImage"
                                               placeholder="Enter QR code or reference number..."
                                               required/>
                                        <button type="submit" class="btn btn-info">
                                            <i class="fas fa-search me-2"></i>Search
                                        </button>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Scanning with camera above auto-fills this field and searches automatically
                                    </small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-lightbulb me-2"></i>What happens after scanning?</h5>
                        <ul class="mb-0">
                            <li>If the QR code is valid, you'll be redirected to the item details</li>
                            <li>If the subscription has expired, an email will be sent to the parent</li>
                            <li>If the QR code is not recognized, you'll see an error message</li>
                            <li>All scans are logged for tracking purposes</li>
                        </ul>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="parent_dashboard" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
 
<script type="text/javascript">
var html5Qrcode = null;

function startCamera() {
    document.getElementById('startCameraBtn').style.display = 'none';
    document.getElementById('reader').style.display = 'block';
    document.getElementById('stopCameraBtn').style.display = 'inline-block';
    document.querySelector('#reader-wrapper p').style.display = 'none';

    html5Qrcode = new Html5Qrcode("reader");
    html5Qrcode.start(
        { facingMode: "environment" }, // rear camera by default
        { fps: 10, qrbox: { width: 250, height: 250 } },
        function(qrCodeMessage) {
            // Success — fill field and submit
            document.getElementById('result').value = qrCodeMessage;
            document.getElementById('result').style.borderColor = '#28a745';
            document.getElementById('result').style.backgroundColor = '#d4edda';

            stopCamera();

            setTimeout(function() {
                document.querySelector('.scanner-form').submit();
            }, 800);
        },
        function(errorMessage) {
            // Ignore frame errors
        }
    ).catch(function(err) {
        document.getElementById('reader').style.display = 'none';
        document.getElementById('startCameraBtn').style.display = 'inline-block';
        document.getElementById('stopCameraBtn').style.display = 'none';
        alert('Could not start camera: ' + err + '\n\nPlease allow camera access or use manual entry.');
    });
}

function stopCamera() {
    if (html5Qrcode) {
        html5Qrcode.stop().then(function() {
            html5Qrcode.clear();
            document.getElementById('reader').style.display = 'none';
            document.getElementById('startCameraBtn').style.display = 'inline-block';
            document.getElementById('stopCameraBtn').style.display = 'none';
            document.querySelector('#reader-wrapper p').style.display = 'block';
        }).catch(function() {});
    }
}
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

