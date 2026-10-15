<?php
//session_start();
include('connection.php');

$parent_email = $_SESSION['parent_email'];
  $sql.= "UPDATE child_table SET quantity = quantity +1 WHERE  parent_email = '".$parent_email."'";
  if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
    header("Location: index");
  } else {
    echo "Error updating record: " . $conn->error;
  }
  
  $conn->close();
 
 ?>
