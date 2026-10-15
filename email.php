<?php 
  include('phpqrcode/qrlib.php');
  require_once('connection.php');
// Recipient 
 

if(isset($_POST["submit"]))
{
 $name = $_POST['name'];
 $surname = $_POST['surname'];
 $parent_email = $_POST['parent_email'];
 $child_phoneNo = $_POST['child_phoneNo'];
 $parent_phoneNo = $_POST['parent_phoneNo'];
 $address = $_POST['address'];
 $school = $_POST['school'];
 $grade = $_POST['grade'];
 $path ='images/';
 $file = $path.uniqid().".png";

 $ecc = 'L';
 $pixel_Size = 10;
 $frame_Size = 10;
 QRcode::png($parent_email,$file,$ecc,$pixel_Size,$frame_Size);
 $query = "insert into child_table(name,surname,parent_email,child_phoneNo,parent_phoneNo,address,school,grade,qrImage,reg_date)
  VALUES('$name','$surname','$parent_email','$child_phoneNo','$parent_phoneNo','$address','$school','$grade','$file',now())";

  if ($conn->query($query)=== TRUE)
{
   
    echo "New record created successfully";
    $to = $parent_email;
          $subject ="Found and Return Desk ";

$to = $parent_email; 
 
// Sender 
$from = 'madikizela21517799@gmail.com'; 
$fromName = 'Find and return Desk'; 
 
// Email subject 
$subject = 'QR code image'; 
// Attachment file 
$message = '<img src="images/'.$file.'">'; 

// Email body content 
$htmlContent = '
'.$name.' '.$surname.'
    <h3>Your childs has been added to find and return application.</h3> 
    <p>Please find thier QR code.</p> 
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
 
// Email sending status 
echo $mail?"<h1>Email Sent Successfully!</h1>":"<h1>Email sending failed.</h1>"; 
}
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Abuse Reporting Admin</title>
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
                    <h1 class="display-6 fw-bold">Add child</h1>
                    <p class="fw-light mb-0"></p>
                  </div>
                </div>
              </div>
              <!-- Form Panel    -->
              <div class="col-lg-6 bg-white">
                <div class="d-flex align-items-center px-4 px-lg-5 h-100">
                  <form class="" method="POST" action="">
                    <div class="input-material-group mb-3">
                      <input class="input-material" type="text" name="name"  data-validate-field="registerName">
                      <label class="label-material" for="register-username">name </label>
                    </div>
                    <div class="input-material-group mb-3">
                      <input class="input-material" type="text" name="surname"  data-validate-field="registerSurname">
                      <label class="label-material" for="register-username">surname </label>
                    </div>
                    <div class="input-material-group mb-3">
                      <input class="input-material" type="email" name="parent_email" data-validate-field="registerEmail">
                      <label class="label-material">Parent Email </label>
                    </div>
                    <div class="input-material-group mb-4">
                      <input class="input-material" type="text" name="child_phoneNo" data-validate-field="registerPhoneNo">
                      <label class="label-material">Child Phone Number</label>
                    </div>
                    <div class="input-material-group mb-4">
                      <input class="input-material" type="text" name="parent_phoneNo" data-validate-field="registerPhoneNo">
                      <label class="label-material">Parent Phone Number</label>
                    </div>
                    <div class="input-material-group mb-4">
                      <input class="input-material" type="text" name="address" data-validate-field="registerPhoneNo">
                      <label class="label-material"> Physical Address</label>
                    </div>
                    <div class="input-material-group mb-4">
                    <label for="cars">Select School</label><br/>

                   <select name="school" id="cars">
                   <option value="Kloof High School">Kloof High School</option>
                   <option value="Kloof Junior Primary School">Kloof Junior Primary School</option>
                   <option value="Kloof Senior Primary School">Kloof Senior Primary School</option>
                    <option value="Thomas More College">Thomas More College</option>
                    </select>
                    </div>
                    <div class="input-material-group mb-4">
                    <label for="cars">Select Grade</label><br/>

                   <select name="grade" id="cars">
                   <option value="1">1</option>
                   <option value="2">2</option>
                   <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                   </select>
                    </div>
                    <input  class="btn btn-primary mb-3" id="" name="submit" value="submit" type="submit"><br><small class="text-gray-500">
                  </form>
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
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
  </body>
</html>
