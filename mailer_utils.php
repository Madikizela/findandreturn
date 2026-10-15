<?php
/**
 * PHPMailer Integration and Enhanced send_email function
 */

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Sends an email using PHPMailer (SMTP)
 * Supports local Gmail testing and production cPanel SMTP
 */
function send_email($to, $subject, $htmlContent, $attachmentPath = null) {
    // Increase execution time for this script to 5 minutes
    set_time_limit(300);
    
    $logFile = __DIR__ . '/mailer_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] Starting email to: $to\n", FILE_APPEND);

    // Check for PHPMailer via Composer or local path
    $vendorPath = __DIR__ . '/vendor/autoload.php';
    $localPath = __DIR__ . '/phpmailer/src/';

    if (file_exists($vendorPath)) {
        require_once $vendorPath;
        file_put_contents($logFile, "[$timestamp] Loaded PHPMailer via Composer\n", FILE_APPEND);
    } elseif (file_exists($localPath . 'PHPMailer.php')) {
        require_once $localPath . 'Exception.php';
        require_once $localPath . 'PHPMailer.php';
        require_once $localPath . 'SMTP.php';
        file_put_contents($logFile, "[$timestamp] Loaded PHPMailer via local path\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "[$timestamp] PHPMailer NOT FOUND, falling back to mail()\n", FILE_APPEND);
        return send_email_fallback($to, $subject, $htmlContent, $attachmentPath);
    }

    $mail = new PHPMailer(true);
    $mail->Timeout = 30; // Reduce timeout to 30 seconds to fail faster if blocked

    try {
        // Use getenv() as fallback for $_ENV
        $smtp_host = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $smtp_user = $_ENV['SMTP_USER'] ?? getenv('SMTP_USER') ?: 'madikizela21517799@gmail.com';
        $smtp_pass = $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS') ?: 'quqeqfrygbypxoun';
        $smtp_port = (int)($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);
        $smtp_secure = strtolower($_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE') ?: 'tls');
        $smtp_from = $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL') ?: 'madikizela21517799@gmail.com';
        $smtp_name = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'Find and Return';

        file_put_contents($logFile, "[$timestamp] SMTP Config: Host=$smtp_host, User=$smtp_user, Port=$smtp_port\n", FILE_APPEND);

        // Server settings
        $mail->SMTPDebug = 0; // Disable debug output to browser for production, log instead
        $mail->Debugoutput = function($str, $level) use ($logFile, $timestamp) {
            file_put_contents($logFile, "[$timestamp] SMTP Debug: $str", FILE_APPEND);
        };
        $mail->SMTPDebug = 2; 

        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        
        // Fix for slow connections and potential IPv6 issues
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        if ($smtp_secure === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
        
        $mail->Port = $smtp_port;

        // Recipients
        $mail->setFrom($smtp_from, $smtp_name);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlContent;
        $mail->AltBody = strip_tags($htmlContent);

        // Attachment
        if ($attachmentPath) {
            // Convert to absolute path if relative
            if (!realpath($attachmentPath)) {
                $absPath = __DIR__ . '/' . ltrim($attachmentPath, '/');
            } else {
                $absPath = realpath($attachmentPath);
            }

            if (file_exists($absPath)) {
                $mail->addAttachment($absPath);
                file_put_contents($logFile, "[$timestamp] Attachment added: $absPath\n", FILE_APPEND);
            } else {
                file_put_contents($logFile, "[$timestamp] Attachment NOT FOUND: $absPath\n", FILE_APPEND);
            }
        }

        $sent = $mail->send();
        file_put_contents($logFile, "[$timestamp] Email SENT successfully\n", FILE_APPEND);
        return $sent;
    } catch (Exception $e) {
        file_put_contents($logFile, "[$timestamp] PHPMailer Error: {$mail->ErrorInfo}\n", FILE_APPEND);
        return false;
    }
}

/**
 * Basic mail() fallback if PHPMailer is not installed
 */
function send_email_fallback($to, $subject, $htmlContent, $attachmentPath = null) {
    $boundary = md5(time());
    $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? '';
    $fromName = $_ENV['SMTP_FROM_NAME'] ?? '';

    $headers = "From: $fromName <$fromEmail>\r\n";
    $headers .= "Reply-To: $fromEmail\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    $message = "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $htmlContent . "\r\n\r\n";

    if ($attachmentPath && file_exists($attachmentPath)) {
        $filename = basename($attachmentPath);
        $content = chunk_split(base64_encode(file_get_contents($attachmentPath)));
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: application/octet-stream; name=\"$filename\"\r\n";
        $message .= "Content-Disposition: attachment; filename=\"$filename\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= $content . "\r\n\r\n";
    }
    $message .= "--$boundary--";

    return mail($to, $subject, $message, $headers, "-f$fromEmail");
}
