<?php
session_start();
include('connection.php');

//echo $_SESSION['parent_email'];
$qrImage = $_SESSION['qrImage'];

$sql2 = "SELECT * FROM notifications WHERE qrImage = '$qrImage' and quantity >1";
$result = $conn->query($sql2);
if($result->num_rows>0)
{
    $sql2 = "UPDATE notifications set quantity = quantity -1  WHERE  qrImage = '".$qrImage."';";

}

 else {
  $sql2 = "DELETE  from notifications WHERE qrImage ='$qrImage'";
 }

if ($conn->query($sql2) === TRUE) {
    echo "Record updated successfully";

} else {
        echo "Error deleting record: " . $conn->error;
      }

         $conn->close();
 ?>
