<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php
//echo $_SESSION['socialWorkerEmail'];
$error = null;
$token = $_GET['token'];
if(isset($_POST["submit"]))
{
    $email = $_SESSION['socialWorkerEmail'];
    $password =$_POST["password"];
    $password1 =$_POST["password1"];

    if($password !=$password1){
        echo"password does not match";
        
    }else{
        // Modernized: Hash password and use users table
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        
        if (db_query($conn, $sql, "ss", [$hashed_password, $email])) {
              echo "Record updated successfully";

$to = $email;

// Fetch phone number for WhatsApp
// Modernized: Use users table and phone_no
$userQuery = "SELECT phone_no FROM users WHERE email = ?";
$userResult = db_query($conn, $userQuery, "s", [$email]);
$phone_no = "";
if ($userResult && $userRow = $userResult->fetch_assoc()) {
    $phone_no = $userRow['phone_no'];
}

$subject ="Find And Return.";

// Sender 
$from = 'no-reply@findandreturn.co.za'; 
$fromName = 'Find And Return.'; 

// Email subject 
$subject = 'New Password.'; 
// Attachment file 
//$message = '<img src="images/'.$file.'">'; 
$message = "<b>Your login details are as follow.</b>";
$message .= "<b>Your new password is: ".$password;

// Email body content 
$htmlContent = '
<h3>Your new password is as follow.</h3> 
<p>Your password is: '.$password.'</p> 
'; 

// Header for sender info 
$headers = "From: $fromName"." <".$from.">"; 

// Boundary  
$semi_rand = md5(time());  
$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";  

// Headers for attachment  
$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 

// Multipart boundary  
$message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" . 
"Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n";  

// Preparing attachment 
if(!empty($file) > 0){ 
if(is_file($file)){ 
$message .= "--{$mime_boundary}\n"; 
$fp =    @fopen($file,"rb"); 
$data =  @fread($fp,filesize($file)); 

@fclose($fp); 
$data = chunk_split(base64_encode($data)); 
$message .= "Content-Type: application/octet-stream; name=\"".basename($file)."\"\n" .  
"Content-Description: ".basename($file)."\n" . 
"Content-Disposition: attachment;\n" . " filename=\"".basename($file)."\"; size=".filesize($file).";\n" .  
"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n"; 
} 
} 
$message .= "--{$mime_boundary}--"; 
$returnpath = "-f" . $from; 

// Send email 
$mail = @mail($to, $subject, $message, $headers, $returnpath);  

// WhatsApp Notification
if (!empty($phone_no)) {
    $whatsappMessage = "Hello! Your new password for Find and Return is: " . $password;
    send_whatsapp($phone_no, $whatsappMessage);
}

// Email sending status 
echo $mail?"<h1>Email Sent Successfully!</h1>":"<h1>Email sending failed.</h1>"; 

   if ($conn->query($sql)=== TRUE)
{
//echo "New record created successfully";
header("Location: index");
             exit();
}
else{
    $error=$conn->error;
}
          }}
        }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
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
    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
  </head>
  <body>
    <div class="login-page">
      <div class="container d-flex align-items-center position-relative py-5">
        <div class="card shadow-sm w-100 rounded overflow-hidden bg-none">
          <div class="card-body p-0">
            <div class="row gx-0 align-items-stretch">
              <!-- Logo & Information Panel-->
              <div class="col-lg-6">
                <div class="info d-flex justify-content-center flex-column p-4 h-100">
                  <div class="py-5">
                    <h1 class="display-6 fw-bold">Reset Password</h1>
                    <p class="fw-light mb-0"></p>
                  </div>
                </div>
              </div>
              <!-- Form Panel    -->
              <div class="col-lg-6 bg-white">
                <div class="d-flex align-items-center px-4 px-lg-5 h-100">
                  <form class="" method="POST" action="">
                    <div class="input-material-group mb-4">
                      <input class="input-material" required type="password" name="password"id="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" data-validate-field="registerPhoneNo">
                      <label class="label-material">Password</label>
                    </div>
                    <div class="input-material-group mb-4">
                      <input class="input-material" required type="password" name="password1"id="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" data-validate-field="registerPhoneNo">
                      <label class="label-material">Confirm Password</label>
                    </div>
                    <input  onclick="index.php" class="btn btn-primary mb-3" id="" name="submit"  type="submit"><br><small class="text-gray-500">
                  </form>
                  <div id="message">
                  <h3>Password must contain the following:</h3>
                  <p id="letter" class="invalid">A <b>lowercase</b> letter</p>
                 <p id="capital" class="invalid">A <b>capital (uppercase)</b> letter</p>
                  <p id="number" class="invalid">A <b>number</b></p>
                  <p id="length" class="invalid">Minimum <b>8 characters</b></p>
                 </div>
                </div>
              </div>
            </div>
          </div> 
        </div>
      </div>
      <div class="text-center position-absolute bottom-0 start-0 w-100 z-index-20">
        <p class="text-white">Design by <a class="external" href="">KDBS Solutions</a>
          <!-- Please do not remove the backlink to us unless you support further theme's development at https://bootstrapious.com/donate. It is part of the license conditions. Thank you for understanding :)-->
        </p>
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
    <script>
var myInput = document.getElementById("password");
var letter = document.getElementById("letter");
var capital = document.getElementById("capital");
var number = document.getElementById("number");
var length = document.getElementById("length");

// When the user clicks on the password field, show the message box
myInput.onfocus = function() {
  document.getElementById("message").style.display = "block";
}

// When the user clicks outside of the password field, hide the message box
myInput.onblur = function() {
  document.getElementById("message").style.display = "none";
}

// When the user starts to type something inside the password field
myInput.onkeyup = function() {
  // Validate lowercase letters
  var lowerCaseLetters = /[a-z]/g;
  if(myInput.value.match(lowerCaseLetters)) {
    letter.classList.remove("invalid");
    letter.classList.add("valid");
  } else {
    letter.classList.remove("valid");
    letter.classList.add("invalid");
  }

  // Validate capital letters
  var upperCaseLetters = /[A-Z]/g;
  if(myInput.value.match(upperCaseLetters)) {
    capital.classList.remove("invalid");
    capital.classList.add("valid");
  } else {
    capital.classList.remove("valid");
    capital.classList.add("invalid");
  }

  // Validate numbers
  var numbers = /[0-9]/g;
  if(myInput.value.match(numbers)) {
    number.classList.remove("invalid");
    number.classList.add("valid");
  } else {
    number.classList.remove("valid");
    number.classList.add("invalid");
  }

  // Validate length
  if(myInput.value.length >= 8) {
    length.classList.remove("invalid");
    length.classList.add("valid");
  } else {
    length.classList.remove("valid");
    length.classList.add("invalid");
  }
}
</script>
<style>
/* The message box is shown when the user clicks on the password field */
#message {
  display:none;
  background: #f1f1f1;
  color: #000;
  position: relative;
  padding: 20px;
  margin-top: 10px;
}

#message p {
  padding: 10px 35px;
  font-size: 18px;
}

/* Add a green text color and a checkmark when the requirements are right */
.valid {
  color: green;
}

.valid:before {
  position: relative;
  left: -35px;
  content: "✔";
}

/* Add a red text color and an "x" when the requirements are wrong */
.invalid {
  color: red;
}

.invalid:before {
  position: relative;
  left: -35px;
  content: "✖";
}
</style>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
  </body>
</html>

