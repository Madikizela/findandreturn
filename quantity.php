<?
include('connection.php');
session_start();
echo $_SESSION['school'];
$school = $_SESSION['school'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include 'pwa-head.php'; ?>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
    
   <?php
   include('connection.php');
   session_start();
   if(isset($_POST["submit"]))
{
    
  }
       ?>

<form action="" method="post">
<div class="container">
  <!-- Trigger the modal with a button -->
  <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">View Notifications</button>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Reported as lost Items</h4>
        </div>
        <div class="modal-body">
       <?php
          include('connection.php');
          $school = $_SESSION['school'];

      $count = 1;


   $sql = "  SELECT SUM(quantity) FROM child_table where school= '".$school."'";
   $result = $conn->query($sql);

   while ($row = $result->fetch_assoc()) {
    echo $row['SUM(quantity)'];} ?>
           </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>
  
</div>
</form>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>


