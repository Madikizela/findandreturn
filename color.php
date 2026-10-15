<?php

include('connection.php');
session_start();
//echo $_SESSION['school'];
$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
  header("Location: login");
  exit();
}
?>
<?php
// Database connection code goes here (use mysqli or PDO)
include('connection.php');
// Assuming you have an admin_settings table with columns 'logo_path' and 'theme_color'
$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE email = $email"; // You may adjust the query as needed
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    // Default values if settings are not found
    $themeColor = "#000000"; // Default color code
    $logoPath = "default_logo.png"; // Default logo path
}

// Close the database connection if you're done with it
// $conn->close();
?>

