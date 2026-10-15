<?php
include("connection.php");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $grade = isset($_POST['grade']) ? $_POST['grade'] : '';
    $subject = isset($_POST['subject']) ? $_POST['subject'] : '';

    // File upload handling
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($_FILES['file_path']['name']);

    if (move_uploaded_file($_FILES['file_path']['tmp_name'], $uploadFile)) {
        // Prepare and execute the SQL query to insert data into the database
        $sql = "INSERT INTO study (grade, subject, file_path) 
                VALUES ('$grade', '$subject', '$uploadFile')";

        if ($conn->query($sql) === TRUE) {
            echo "Data saved successfully.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error uploading file.";
    }

    // Close the database connection
    $conn->close();
}
?>


