<?php
/**
 * Security utility functions for Find and Return application
 */

/**
 * Escapes HTML output to prevent XSS
 * @param string $string The string to escape
 * @return string The escaped string
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Securely executes a database query using prepared statements
 * @param mysqli $conn The database connection
 * @param string $sql The SQL query with placeholders (?)
 * @param string $types The types of the parameters (e.g., "ssi")
 * @param array $params The parameters to bind
 * @return mysqli_result|bool The result of the query or false on failure
 */
function db_query($conn, $sql, $types = null, $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close(); // Ensure stmt is closed before returning
        return false;
    }

    $result = $stmt->get_result();
    
    // For INSERT, UPDATE, DELETE queries, get_result() returns false
    // but the query was successful if execute() returned true.
    if ($result === false) {
        if ($stmt->errno === 0) {
            $result = true;
        } else {
            error_log("Execute error after statement: " . $stmt->error);
            $result = false;
        }
    }

    $stmt->close();
    return $result;
}

/**
 * Validates and regenerates session ID to prevent session fixation
 */
function secure_session_start() {
    // Set security headers
    if (!headers_sent()) {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("Content-Security-Policy: frame-ancestors 'self' *.findandreturn.co.za findandreturn.co.za;");
    }

    if (session_status() === PHP_SESSION_NONE) {
        // Strip port from domain — session cookies must use the bare hostname
        $rawHost = $_SERVER['HTTP_HOST'] ?? '';
        $domain  = strtolower(explode(':', $rawHost)[0]);

        // Only set a cookie domain for real hostnames (not localhost / IP)
        $cookieDomain = (str_contains($domain, '.') && !filter_var($domain, FILTER_VALIDATE_IP))
            ? $domain
            : '';

        if (!headers_sent()) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => $cookieDomain,
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        session_start();
    }

    // Regenerate session ID at most once per 30 minutes to prevent fixation,
    // but NEVER during the same request that just started (avoids wiping CSRF token).
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * CSRF Protection: Generates a CSRF token
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Protection: Validates a CSRF token
 */
function validate_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Sends an email using SMTP
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $htmlContent HTML body content
 * @param string $attachmentPath Optional path to attachment
 * @return bool True on success
 */
// Include mailer utilities
require_once 'mailer_utils.php';

$last_whatsapp_error = '';

/**
 * Sends a WhatsApp message using a centralized API
 * @param string $phone The recipient's phone number
 * @param string $message The message content (or template data if $isTemplate is true)
 * @param bool $isTemplate Whether this is a template message
 * @param string $templateName The name of the template (if $isTemplate is true)
 * @param array $templateParams Optional array of text parameters for template body
 * @param string $templateLang The language code for the template (default: en_US)
 * @return bool True if sent successfully, false otherwise
 */
function send_whatsapp($phone, $message, $isTemplate = false, $templateName = '', $templateParams = [], $templateLang = 'en_US') {
    global $last_whatsapp_error;
    $last_whatsapp_error = '';
    
    $apiUrl = $_ENV['WHATSAPP_API_URL'] ?? getenv('WHATSAPP_API_URL') ?: '';
    $token = $_ENV['WHATSAPP_TOKEN'] ?? getenv('WHATSAPP_TOKEN') ?: '';
    $enabled = $_ENV['WHATSAPP_ENABLED'] ?? getenv('WHATSAPP_ENABLED') ?: 'false';

    if ($enabled !== 'true') {
        $last_whatsapp_error = "WhatsApp is disabled in settings.";
        return false;
    }
    
    if (empty($apiUrl)) {
        $last_whatsapp_error = "WhatsApp API URL is not configured.";
        return false;
    }

    if (empty($phone)) {
        $last_whatsapp_error = "Recipient phone number is empty.";
        return false;
    }

    // Standardize phone number (remove non-digits)
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Auto-format South African numbers (replace leading 0 with 27)
    if (strlen($phone) === 10 && strpos($phone, '0') === 0) {
        $phone = '27' . substr($phone, 1);
    }
    
    // Log final phone number and enabled status
    error_log("WhatsApp debug: Final Phone=$phone, Enabled=$enabled, API=" . substr($apiUrl, 0, 30) . "...");
    
    $curl = curl_init();
    
    // Check if this is a Meta (Facebook) Graph API URL
    $isMeta = (strpos($apiUrl, 'graph.facebook.com') !== false);

    if ($isMeta) {
        if ($isTemplate) {
            $templateArr = array(
                'name' => $templateName,
                'language' => array('code' => $templateLang),
            );

            if (!empty($templateParams)) {
                $parameters = array();
                foreach ($templateParams as $paramText) {
                    $parameters[] = array('type' => 'text', 'text' => $paramText);
                }
                $templateArr['components'] = array(
                    array(
                        'type' => 'body',
                        'parameters' => $parameters,
                    )
                );
            }

            $params = array(
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to' => $phone,
                'type' => 'template',
                'template' => $templateArr,
            );
        } else {
            $params = array(
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to' => $phone,
                'type' => 'text',
                'text' => array(
                    'preview_url' => false,
                    'body'        => $message,
                )
            );
        }
        $payload = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $headers = array(
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        );
    } else {
        // UltraMsg/Generic Format (Form Data)
        $params = array(
            'token' => $token,
            'to' => $phone,
            'body' => $message
        );
        $payload = http_build_query($params);
        $headers = array(
            "Content-Type: application/x-www-form-urlencoded"
        );
    }

    curl_setopt_array($curl, array(
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => $headers,
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($err) {
        $last_whatsapp_error = "cURL Error: " . $err;
        error_log("WhatsApp cURL Error: " . $err);
        return false;
    }

    $responseData = json_decode($response, true);
    
    // Log response for debugging
    error_log("WhatsApp API Response (HTTP $httpCode): " . $response);
    
    // Meta returns 'messages' array on success, UltraMsg returns 'sent' => 'true'
    $success = false;
    if ($isMeta) {
        $success = ($httpCode === 200 && isset($responseData['messages']));
        if (!$success) {
            if (isset($responseData['error']['message'])) {
                $last_whatsapp_error = "Meta API Error: " . $responseData['error']['message'];
            } elseif ($httpCode === 401) {
                $last_whatsapp_error = "Meta API Error: Invalid or expired Access Token.";
            } elseif ($httpCode === 400) {
                $last_whatsapp_error = "Meta API Error: Bad Request. Check your Phone Number ID or message format.";
            }
        }
    } else {
        $success = ($httpCode === 200 && isset($responseData['sent']) && ($responseData['sent'] === 'true' || $responseData['sent'] === true));
        if (!$success) {
            $last_whatsapp_error = "UltraMsg Error: " . ($responseData['message'] ?? 'Unknown error');
        }
    }

    if (!$success) {
        if (empty($last_whatsapp_error)) {
            $last_whatsapp_error = "API Error (HTTP $httpCode): " . $response;
        }
        error_log("WhatsApp API Error (HTTP $httpCode): " . $response);
        return false;
    }

    return true;
}

