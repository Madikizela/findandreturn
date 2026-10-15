<?php
session_start();
include('connection.php'); 
//echo $_SESSION['socialWorkerEmail'];
// Check if the ID parameter is set in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Modernized: Use children table and qr_image column
    $result = db_query($conn, "SELECT qr_image FROM children WHERE id = ?", "i", [$id]);

    // Fetch the result
    if ($result && $row = $result->fetch_assoc()) {
        $images = $row['qr_image'];
        // Make sure the file exists before downloading
        if (file_exists($images)) {
            // Set the appropriate headers for download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($images) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($images));
            readfile($images);
            exit;
        } else {
            echo "File not found.";
        }
    } else {
        echo "Picture with ID $id not found.";
    }
}


// Close the connection
$conn->close();
?>
<!-- Replace "download_picture.php" with the filename of your PHP script -->
<a href="download.php?">Download Picture</a>


