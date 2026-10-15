<?php
include('connection.php');
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

$school = $_SESSION['school'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Notifications - Find and Return</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include 'pwa-head.php'; ?>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
    
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
        <table id="tableId" class="table mb-0">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>NAME</th>
                              <th>SURNAME</th>
                              <th>PARENT PHONE NUMBER</th>
                              <th>SCHOOL</th>
                              <th>GRADE</th>
                              <th>QRCODE IMAGE</th>

                            </tr>
                          </thead>
                          <tbody>
          <?php
          $count = 1;
          $sql = "SELECT * FROM child_table WHERE status = 1 and school = ?";
          $result = db_query($conn, $sql, "s", [$school]);

          if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  ?>
                  <tr>
                  <td><?php echo $count; ?></td>
                  <td><?php echo h($row["first_name"]); ?></td>
                  <td><?php echo h($row["surname"]); ?></td>
                  <td><?php echo h($row["parent_phoneNo"]); ?></td>
                  <td><?php echo h($row["school"]); ?></td>
                  <td><?php echo h($row["grade"]); ?></td>
                  <td><img src='<?php echo h($row["qrImage"]); ?>' width="50px" height="50px"></td>
                </tr>
                <?php 
                $count++;
              }
          } else {
              echo "<tr><td colspan='7' class='text-center'>No notifications found.</td></tr>";
          }
          ?>
                          </tbody>
        </table>
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


