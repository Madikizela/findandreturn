<?php
/**
 * Simple .env loader
 */
ob_start();

if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
}

// Load environment variables
loadEnv(__DIR__ . '/.env');

// Enable error reporting for the testing subdomain to catch 500 errors
if (strpos($_SERVER['HTTP_HOST'], 'testing.') !== false) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$servername = $_ENV['DB_HOST'] ?? "localhost";
$username = $_ENV['DB_USER'] ?? "root";
$password = $_ENV['DB_PASS'] ?? "";
$dbname = $_ENV['DB_NAME'] ?? "findandreturn";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if($conn ->connect_error){
    die("Connetion failed: ".$conn->connect_error);
}

// Include security utilities
require_once 'security.php';

// Initialize secure session
secure_session_start();
