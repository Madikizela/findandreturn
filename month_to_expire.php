<?php
 require_once('connection.php');
 session_start();
	// Your code here
$current_datetime = date('Y-m-d');
$reminder_datetime = date('Y-m-d', strtotime($current_datetime . ' +1 month')); 
// Send a reminder one week before the expiration date
$sql = "SELECT * FROM child_table WHERE expire_date <= '$reminder_datetime' AND email_reminder_sent =0 ";
$result = mysqli_query($conn, $sql);
// Check for query execution errors
if (!$result) {
    die('Query failed: ' . $conn->error);
}
 while($row = mysqli_fetch_assoc($result))
 {
    $id = $row['id'];
    $first_name = $row['first_name'];
	$surname = $row['surname'];
    $parent_email = $row['parent_email'];
    $parent_phoneNo = $row["parent_phoneNo"];
    $qrImage = $row['qrImage'];
   $expire_date = $row["expire_date"];
    // Send an email reminder to the record
    $to = $parent_email;
    $subject = 'Reminder: Your childs QR Code is about to expire';
    $message =' Hello ' .$parent_email. ' Your childs QR Code '.$first_name.' is about to expire in 1 month on ' . $row['expire_date'] . '.!';
    $headers = 'From:admin@findandreturn.co.za' . "\r\n" .
    'Reply-To: admin@findandreturn.co.za' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
    $sent = mail($to, $subject, $message, $headers);
    if (mail($to, $subject, $message, $headers)) {
        // Email sent successfully, update the table
       // $sql = "UPDATE child_table SET email_reminder_sent = 1 WHERE qrImage = '$qrImage'";
        $result = mysqli_query($conn, $sql);
            echo "Table updated successfully.";
        }
        else
        {
            echo "email failed to update ";
        }
        
        // WhatsApp Notification — template: subscription_expiring_soon [childFirstName, expiryDate]
        $wa_phone = preg_replace('/[^0-9]/', '', $parent_phoneNo ?? '');
        if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
            $wa_phone = '27' . substr($wa_phone, 1);
        }
        send_whatsapp($wa_phone, '', true, 'subscription_expiring_soon', [$first_name, $expire_date], 'en_US');
}

?>

