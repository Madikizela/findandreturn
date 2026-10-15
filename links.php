<?php
include("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $grade = $_POST['grade'];
    $subject = $_POST['subject'];
    $mathsTopic = $_POST['mathsTopic']; // Corrected field name
    $link = $_POST['link'];

    // Use prepared statements to prevent SQL Injection
    $sql = "INSERT INTO links (grade, subject, mathsTopic, link) VALUES (?, ?, ?, ?)";
    $result = db_query($conn, $sql, "ssss", [$grade, $subject, $mathsTopic, $link]);

    if ($result !== false) {
        echo "Data saved successfully.";
    } else {
        echo "Error saving data.";
    }

    // Close the database connection
    $conn->close();
}
?>



