<?php
include('connection.php');
session_start();
//echo $_SESSION['school'];
$qrImage= $_SESSION['qrImage'];


$sql2 = "SELECT * FROM notifications WHERE qrImage = '$qrImage'";
$result = $conn->query($sql2);
if($result->num_rows>0)
{
    $sql2 = "UPDATE notifications set quantity = quantity +1  WHERE  qrImage = '".$qrImage."';";

}

else{
 $sql2= "INSERT into notifications 
SELECT *
FROM  child_table
WHERE qrImage ='".$qrImage."'";

}
if ($conn->query($sql2) === TRUE) {
 echo "Record copied successfully";
echo "<script>window.location.href='';</script>";

}
else 
{
 echo "Error updating record: " . $conn->error;
}



$conn->close();
?>

