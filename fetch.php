
   <?php
   include('connection.php');
   session_start();
   echo $_SESSION['school'];
$school = $_SESSION['school'];
   $sql = "UPDATE child_table SET status=0 Where and  school = '".$school."'";

   if ($conn->query($sql) === TRUE) {
     echo "Record updated successfully";
     

   }
    else 
   {
     echo "Error updating record: " . $conn->error;
   }
   
   $conn->close();
       ?>

