<?php
 require_once('connection.php');
 session_start();
 while (true) {
 $sql = "SELECT id FROM child_table";
 $result = mysqli_query($conn, $sql);

 if (!$result) {
  die("Query failed: " . mysqli_error($conn));
}
 
     while ($row = mysqli_fetch_assoc($result)) {
         $id = $row["id"];
         // Use the ID in your WHERE statement or perform any other action
         $sql = "SELECT * FROM child_table WHERE id = '$id' AND status = 0";
         $query_result = mysqli_query($conn, $sql);
 
         // Fetch the row and process it
         if (mysqli_num_rows($query_result) > 0) {
             $row_data =$query_result->fetch_assoc();
             // Process the row data as needed
             $id = $row_data["id"];
             $parent_email = $row_data["parent_email"];
             $first_name = $row_data["first_name"];
              $qrImage = $row_data["qrImage"];
             $surname = $row_data["surname"];
             $parent_phoneNo = $row_data["parent_phoneNo"];
             $expire_date = $row_data["expire_date"];
             // Check the subscription status
             if (strtotime($expire_date) <= time()) {
                 // The subscription has expired, send an email to the user
                 $to = $parent_email;
                 $subject = 'Reminder: Your childs QR Code is about to expire';
                 $message = ' Hello ' . $parent_email . ' Your childs QR Code '.$first_name.'  '.$surname.' expired on ' . $row_data['expire_date'] . '. Don\'t forget to renew it!';
                 $headers = 'From:admin@findandreturn.co.za' . "\r\n" .
                     'Reply-To: admin@findandreturn.co.za' . "\r\n" .
                     'X-Mailer: PHP/' . phpversion();
                     if (mail($to, $subject, $message, $headers)) {
                        // Email sent successfully, update the table
                        $sql = "UPDATE child_table SET status = 1 WHERE qrImage = '$qrImage'";
                        $result = mysqli_query($conn, $sql);
                    
                        if ($result) {
                           
                        } else {
                            echo "Error updating table: " . mysqli_error($conn);
                        }
                    } else {
                        echo "Error sending email.";
                    }
             
             // WhatsApp Notification — template: subscription_expired [childFirstName, expiryDate]
             $wa_phone = preg_replace('/[^0-9]/', '', $parent_phoneNo ?? '');
             if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
                 $wa_phone = '27' . substr($wa_phone, 1);
             }
             send_whatsapp($wa_phone, '', true, 'subscription_expired', [$first_name, $expire_date], 'en_US');
            
         }
        }
         }
     sleep(60); // Wait one hour
  }


?>

