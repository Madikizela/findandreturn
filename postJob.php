<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <?php include 'pwa-head.php'; ?>
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <style>
        /* styles.css */

form {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

label {
    display: block;
    margin-bottom: 12px;
}

input[type="text"],
input[type="date"],
input[type="number"],
textarea {
    width: 100%;
    padding: 8px;
    margin-bottom: 12px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
}

input[type="submit"] {
    background-color: #4caf50;
    color: white;
    cursor: pointer;
    padding: 10px;
    border: none;
    border-radius: 4px;
}
select {
        width: 100%;
        padding: 8px;
        margin-bottom: 12px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    /* Style for the label element */
    label {
        display: block;
        margin-bottom: 8px;
    }

    </style>
</head>

<body>
    <?php
    include("connection.php");
    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate CSRF token
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            die("CSRF token validation failed.");
        }

        // Retrieve form data
        $title = $_POST["title"];
        $description = $_POST["description"];
        $location = $_POST["location"];
        $employmentStatus = $_POST["employmentStatus"];
        $date = $_POST["date"];
        $closeDate = $_POST["closeDate"];
        $experience = $_POST["experience"];
        $age = $_POST["age"];
        $qualification = $_POST["qualification"];
        $qualification2 = $_POST["qualification2"];
        $qualification3 = $_POST["qualification3"];
        $qualification4 = $_POST["qualification4"];
        $qualification5 = $_POST["qualification5"];
        $qualification6 = $_POST["qualification6"];
        $qualification7 = $_POST["qualification7"];
        $qualification8 = $_POST["qualification8"];
        $qualification9 = $_POST["qualification9"];
        $qualification10 = $_POST["qualification10"];
        
        // SQL query to insert data into the jobs table using prepared statements
        $sql = "INSERT INTO careers (title, description, location, employmentStatus, date, closeDate, experience, age, qualification, qualification2, qualification3, qualification4, qualification5, qualification6, qualification7, qualification8, qualification9, qualification10)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [$title, $description, $location, $employmentStatus, $date, $closeDate, $experience, $age, $qualification, $qualification2, $qualification3, $qualification4, $qualification5, $qualification6, $qualification7, $qualification8, $qualification9, $qualification10];

        if (db_query($conn, $sql, "ssssssiissssssssss", $params)) {
            echo "<p>Job posted successfully!</p>";
        } else {
            echo "<p>Error posting job.</p>";
        }

        // Close connection
        $conn->close();
    }
    ?>

    <form action="<?php echo h($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
        <label for="job_title">Job Title:</label>
        <input type="text" name="title" required><br>

        <label for="job_description">Job Description:</label>
        <textarea name="description" rows="4" cols="50" required></textarea><br>

        <label for="job_description">Job Location:</label>
        <textarea name="location" rows="2" cols="50" required></textarea><br>

        <label for="employment">Employment Status:</label>
        <select name="employmentStatus" required>
        <option value="">Select</option>
            <option value="Permanent">Permanent</option>
            <option value="Part Time">Part Time</option>
            <option value="Contracct">Contract</option>
            <!-- Add more options as needed -->
        </select><br>

        <label for="job_description">if Short Listed Applicant can expect communication by:</label>
        <input type="date" name="date" required><br>

        <label for="job_description">application closing date:</label>
        <input type="date" name="closeDate" required><br>

        <label for="min_experience">Minimum Experience Required (in years):</label>
        <input type="number" name="experience" required><br>

        <label for="age">Age:</label>
        <input type="number" name="age" required><br>

        <label for="age">Qualification 1:</label>
        <input type="text" name="qualification" required><br>

        <label for="age">Qualification 2:</label>
        <input type="text" name="qualification2"><br>

        <label for="age">Qualification 3:</label>
        <input type="text" name="qualification3"><br>

        <label for="age">Qualification 4:</label>
        <input type="text" name="qualification4"><br>

        <label for="age">Qualification 5:</label>
        <input type="text" name="qualification5"><br>

        <label for="age">Qualification 6:</label>
        <input type="text" name="qualification6"><br>

        <label for="age">Qualification 7:</label>
        <input type="text" name="qualification7"><br>

        <label for="age">Qualification 8:</label>
        <input type="text" name="qualification8"><br>

        <label for="age">Qualification 9:</label>
        <input type="text" name="qualification9"><br>

        <label for="age">Qualification 10:</label>
        <input type="text" name="qualification10"><br>

        <input type="submit" value="Post Job">
    </form>

    <?php include 'pwa-footer.php'; ?>
</body>

</html>

