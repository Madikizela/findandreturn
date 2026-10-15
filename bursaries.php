<?php
// Assuming your database credentials
include("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $heading = $_POST["heading"];
    $link = $_POST["link"];
    $date = $_POST["date"];

    // SQL to insert data into the database
    $sql = "INSERT INTO bursaries (heading, link, date) VALUES ('$heading', '$link', '$date')";

    if ($conn->query($sql) === TRUE) {
        echo "Data successfully saved to the database.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>

