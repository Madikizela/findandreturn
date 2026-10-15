<?php
// session_start(); // Already called in connection.php
include_once 'connection.php';

if (isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $resetToken = bin2hex(random_bytes(32));

    // Use prepared statement to find user
    $sql = "SELECT * FROM scanner WHERE email = ?";
    $result = db_query($conn, $sql, "s", [$email]);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email_id = $row['email'];
        $phoneNo = $row['phoneNo'] ?? '';

        // Update token in database using prepared statement
        $updateSql = "UPDATE scanner SET token = ? WHERE email = ?";
        if (db_query($conn, $updateSql, "ss", [$resetToken, $email_id])) {
            
            // Detect if running locally or on production
            $isLocal = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1' || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
            $baseUrl = $isLocal ? "http://localhost/findandreturntesting" : "https://findandreturn.co.za";
            $resetLink = $baseUrl . "/reset_password.php?token=" . $resetToken;

            // Email Logic
            $to = $email_id;
            $from = 'no-reply@findandreturn.co.za';
            $fromName = 'Find and Return Team';
            $subject = 'Reset Your Password';
            
            $htmlContent = '
            <html>
            <body>
                <h3>Password Reset Request</h3>
                <p>Click the link below to reset your password:</p>
                <p><a href="' . $resetLink . '">' . $resetLink . '</a></p>
                <p>If you did not request this, please ignore this email.</p>
    <?php include 'pwa-footer.php'; ?>
            </body>
            </html>';

            // Use the enhanced send_email function which uses PHPMailer
            $mailSent = send_email($to, $subject, $htmlContent);

            // WhatsApp Notification
            if (!empty($phoneNo)) {
                $whatsappMessage = "Hello! Click the link below to reset your password for Find and Return: " . $resetLink;
                send_whatsapp($phoneNo, $whatsappMessage);
            }

            echo "<script>alert('The link to reset your password was sent to your email and WhatsApp.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error updating reset token. Please try again.'); window.location.href='forgot_password.php';</script>";
        }
    } else {
        echo "<script>alert('This user does not have an account with us.'); window.location.href='forgot_password.php';</script>";
    }
} else {
    header("Location: forgot_password.php");
    exit();
}
?>

