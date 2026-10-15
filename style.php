<?php
// Rest of your PHP code remains the same
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('connection.php');

$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE id = 12";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#0000";
    $logoPath = "default_logo.png";
}

// Close the database connection if you're done with it
// $conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Page</title>
    <style>
        body {
            background-color: <?php echo $themeColor; ?>;
        }
       

        /* Add more CSS rules as needed */
    </style>
    <link rel="stylesheet" type="text/css" href="">
</head>
<body>
    Your Page<br>
    <?php
        include('connection.php');
        $query = "SELECT logo_path, theme_color FROM admin_settings WHERE id = 12";
        $result = mysqli_query($conn, $query);
 
        while ($data = mysqli_fetch_assoc($result)) {
        ?>

            <img src='<?php  echo  $data["logo_path"]; ?>' width="50px" height="50px"
        <?php
        }
        ?>
  
    </div>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>


