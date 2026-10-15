<!-- user_apply_job.php -->
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
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Saira:wght@500;600;700&display=swap" rel="stylesheet"> 

        <!-- Icon Font Stylesheet -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

        <!-- Libraries Stylesheet -->
        <link href="lib/animate/animate.min.css" rel="stylesheet">
        <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

        <!-- Customized Bootstrap Stylesheet -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    
    <!--Fontawesome CDN-->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">

        <!-- Template Stylesheet -->
        <link href="css/style.css" rel="stylesheet">

   <style>

    form {
        background-color: #fff;
        padding: 50px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 400px; /* Increased the width for a larger form */
        max-width: 100%;
        box-sizing: border-box;
    }

    label {
        display: block;
        margin-bottom: 8px;
    }

    input {
        width: 100%;
        padding: 10px; /* Increased the padding for better spacing */
        margin-bottom: 16px; /* Increased the margin for better spacing */
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    input[type="submit"] {
        background-color: #4caf50;
        color: white;
        cursor: pointer;
        /* Added some extra styles for better appearance */
        padding: 12px;
        font-size: 16px;
    }

    .custom-checkbox {
        width: 20px; /* Adjusted the width for smaller checkboxes */
        height: 20px; /* Adjusted the height for smaller checkboxes */
        /* Add any other styles as needed */
    }
</style>

    <script>
    function showRejectionPopup(message) {
        alert(message); // You can use more sophisticated pop-up libraries if needed
    }
    
</script>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 40px; padding: 0; text-align: center;">
<div style="display: inline-block; text-align: left;">
        <!-- Your HTML content goes here, including the form -->
        
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("connection.php");

// Check if the form is submitted

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    // Retrieve form data
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $age = $_POST["age"];
    $email = $_POST["email"];
    $altanativeEmail = $_POST["altanativeEmail"];
    $contact = $_POST["contact"];
    $altanativeContact = $_POST["altanativeContact"];
    $title = $_POST["title"];
    $address = $_POST["address"];
    $district = $_POST["district"];
    $municipality = $_POST["municipality"];
    $area = $_POST["area"];
    $experience = $_POST["experience"];

    $qualification1 = $_POST["qualification1"] ?? '';
    $qualification2 = $_POST["qualification2"] ?? '';
    $qualification3 = $_POST["qualification3"] ?? '';
    $qualification4 = $_POST["qualification4"] ?? '';
    $qualification5 = $_POST["qualification5"] ?? '';
    $qualification6 = $_POST["qualification6"] ?? '';
    $qualification7 = $_POST["qualification7"] ?? '';
    $qualification8 = $_POST["qualification8"] ?? '';
    $qualification9 = $_POST["qualification9"] ?? '';
    $qualification10 = $_POST["qualification10"] ?? '';
    $qualification_other = $_POST["qualification_other"] ?? '';
    
    // Check if "Other" is selected and none of the other qualifications are selected
    if (empty($qualification1) && empty($qualification2) && empty($qualification3) && !empty($qualification_other)) {
        echo "<script>alert('You do not qualify. Please select at least one qualification field.');</script>";
    } else {
        // Retrieve job information from the query parameter
        $jobTitle = $_GET['title'] ?? '';

        // Fetch age and experience requirements from the database securely
        $sqlFetchRequirements = "SELECT age, experience FROM careers WHERE title = ?";
        $resultRequirements = db_query($conn, $sqlFetchRequirements, "s", [$jobTitle]);

        if ($resultRequirements === FALSE) {
            echo "<p>Error in SQL query.</p>";
        } elseif ($resultRequirements->num_rows > 0) {
            $rowRequirements = $resultRequirements->fetch_assoc();
            $minAgeRequirement = $rowRequirements['age'];
            $minExperienceRequirement = $rowRequirements['experience'];

            $sqlCheckPreviousApplication = "SELECT * FROM applications WHERE email = ? AND title = ?";
            $resultPreviousApplication = db_query($conn, $sqlCheckPreviousApplication, "ss", [$email, $jobTitle]);
            
            if ($resultPreviousApplication && $resultPreviousApplication->num_rows > 0) {
                echo "<script>alert('You have already applied for this position.');</script>";
            } else {
                // Check if the applicant meets the minimum requirements
                if ($age > $minAgeRequirement) {
                    echo "<script>showRejectionPopup('Sorry, your application for " . h($jobTitle) . " has been rejected. Minimum age requirement is " . h($minAgeRequirement) . ".');</script>";
                } elseif ($experience < $minExperienceRequirement) {
                    echo "<script>showRejectionPopup('Sorry, your application for " . h($jobTitle) . " has been rejected. Minimum " . h($minExperienceRequirement) . " years of experience is required.');</script>";
                } else {
                    // Handle file upload for qualification with security checks
                    $targetDirectory = "uploads/";
                    if (!is_dir($targetDirectory)) {
                        mkdir($targetDirectory, 0755, true);
                    }

                    $fileName = basename($_FILES["qualification"]["name"]);
                    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

                    if (!in_array($fileType, $allowedExtensions)) {
                        echo "<script>alert('Error: Only PDF, JPG, JPEG, and PNG files are allowed.');</script>";
                    } else {
                        // Generate a unique file name to prevent overwriting and directory traversal
                        $newFileName = uniqid('qual_', true) . '.' . $fileType;
                        $targetFile = $targetDirectory . $newFileName;

                        if (move_uploaded_file($_FILES["qualification"]["tmp_name"], $targetFile)) {
                            // File upload successful, now insert data into the database using prepared statements
                            $sql = "INSERT INTO applications (name, surname, age, email, altanativeEmail, contact, altanativeContact, title, address, district, municipality, area, experience, qualification1, qualification2, qualification3, qualification4, qualification5, qualification6, qualification7, qualification8, qualification9, qualification10, qualification)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            
                            $params = [
                                $name, $surname, $age, $email, $altanativeEmail, $contact, $altanativeContact, $title, $address, $district, $municipality, $area, $experience,
                                $qualification1, $qualification2, $qualification3, $qualification4, $qualification5, $qualification6, $qualification7, $qualification8, $qualification9, $qualification10, $targetFile
                            ];

                            if (db_query($conn, $sql, "ssisssssssssssssssssssss", $params)) {
                                // Send confirmation email to applicant
                                $message = "Dear " . $name . ",\n\nThank you for applying for the position of " . $title . ". Your application has been received successfully. We will review your qualifications and contact you if you are shortlisted.\n\nBest regards,\n\nThe Admin Team";
                                $to = $email;
                                $from = 'admin@findandreturn.co.za'; 
                                $fromName = 'MDI'; 
                                $subject = 'Application Confirmation';

                                $htmlContent = '<p>' . nl2br(h($message)) . '</p>'; 
                                $headers = "From: $fromName <$from>\r\n";
                                $headers .= "MIME-Version: 1.0\r\n";
                                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                                @mail($to, $subject, $htmlContent, $headers);

                                // WhatsApp Notification
                                $whatsappMessage = "Dear " . $name . ", thank you for applying for the position of " . $jobTitle . " at Find and Return. Your application has been received successfully.";
                                send_whatsapp($contact, $whatsappMessage);

                                echo "<script>alert('Application submitted successfully!'); window.location.href='careers';</script>";
                                
                                // Admin notification
                                $adminMessage = "Dear Admin,\n\nA new job application has been submitted for the position of $title.\n\nApplicant: $name\n\nPlease log in to the admin panel for more details.\n\nBest regards,\nThe Notification System";
                                $adminTo = 'madikizela21517799@gmail.com';
                                $adminHtmlContent = '<p>' . nl2br(h($adminMessage)) . '</p>';
                                @mail($adminTo, 'New Application: ' . $title, $adminHtmlContent, $headers);
                            } else {
                                echo "<p>Error: Could not save application.</p>";
                            }
                        } else {
                            echo "<p>Sorry, there was an error uploading your file.</p>";
                        }
                    }
                }
            }
        } else {
            echo "<p>No job requirements found for " . h($jobTitle) . "</p>";
        }
    }
}
}
?>



<form method="POST" style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 700px;" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
<?php
// Retrieve the job title from the URL parameter
$jobTitle = isset($_GET['title']) ? $_GET['title'] : '';

// Check if a job title is provided
if (!empty($jobTitle)) {
    echo '<h1>Application for ' . h($jobTitle) . '</h1>';
    // Add your application form or processing logic here
} else {
    echo '<p>No job title provided for application.</p>';
}
?>
    <label for="name">Name:</label>
    <input type="text" name="name" required>

    <label for="surname">Surname:</label>
    <input type="text" name="surname" required>

    <label for="age">Age:</label>
    <input type="text" name="age" required>

    <label for="email">Email:</label>
    <input type="email" name="email" required>

    <label for="email">Altanative Email:</label>
    <input type="email" name="altanativeEmail" required>

    <label for="contact">Contact Number:</label>
    <input type="text" name="contact" required>

    <label for="contact">Altanative Contact Number:</label>
    <input type="text" name="altanativeContact" required>

    <input type="hidden" name="title" value="<?php echo htmlspecialchars($jobTitle); ?>">

    <label for="address">Physical Address:</label>
    <input type="text" name="address" required>

    <label for="district">Select District</label>
<select class="form-control" required id="district" name="district">
    <?php
    // Assuming $conn is your database connection
    $sql = "SELECT DISTINCT district FROM province";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['district'] . "'>" . $row['district'] . "</option>";
        echo "<option value='" . $row['district'] . "'>" . $row['district'] . "</option>";
    }
    ?>
</select>

<label for="municipality">Select Municipality</label>
<select class="form-control" required id="municipality" name="municipality"></select>

<label for="area">Select Area</label>
<select class="form-control" required id="area" name="area"></select>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
$(document).ready(function(){
    $("#district").change(function(){
        var district = $(this).val();
        
        $.ajax({
            type: "POST",
            url: "getMunicipalities.php", // Replace with your server-side script to fetch municipalities
            data: { district: district },
            success: function(data){
                $("#municipality").html(data);
            }
        });
    });

    $("#municipality").change(function(){
        var municipality = $(this).val();
        
        $.ajax({
            type: "POST",
            url: "getAreas.php", // Replace with your server-side script to fetch areas
            data: { municipality: municipality },
            success: function(data){
                $("#area").html(data);
            }
        });
    });
});
</script>


    <label for="address">Check your qualification</label>
    <?php
// Assuming you have a database connection
include('connection.php');

// Assuming you have a table named 'qualifications'
$query = "SELECT * FROM careers  WHERE title = '$jobTitle'";
$result = mysqli_query($conn, $query);

// Check for errors in the query
if (!$result) {
    die("Error fetching careers: " . mysqli_error($conn));
}

// Check if there are any qualifications in the database
if (mysqli_num_rows($result) > 0) {
    // Fetch and display each qualification as a checkbox
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<span style="font-weight: bold; display: inline-block;">';

        if (!empty($row['qualification'])) {
            echo '<label style="font-weight: bold; flex-grow: 5;">' . $row['qualification'] . '</label>';
            echo '<input type="checkbox" name="qualification1" value="' . $row['qualification'] . '" class="custom-checkbox">';
        }

        echo '</span><br>';

        echo '<span style="font-weight: bold; display: inline-block;">';

        if (!empty($row['qualification2'])) {
            echo '<label style="font-weight: bold; flex-grow: 1;">' . $row['qualification2'] . '</label>';
            echo '<input type="checkbox" name="qualification2" value="' . $row['qualification2'] . '" class="custom-checkbox">';
        }

        echo '</span><br>';

        echo '<span style="font-weight: bold; display: inline-block;">';
        
        if (!empty($row['qualification3'])) {
            echo '<label style="font-weight: bold; flex-grow: 1;">' . $row['qualification3'] . '</label>';
            echo '<input type="checkbox" name="qualification3" value="' . $row['qualification3'] . '" class="custom-checkbox">';
        }

        echo '</span><br>';

        echo '<span style="font-weight: bold; display: inline-block;">';
        
        if (!empty($row['qualification4'])) {
            echo '<label style="font-weight: bold; flex-grow: 1;">' . $row['qualification4'] . '</label>';
            echo '<input type="checkbox" name="qualification4" value="' . $row['qualification4'] . '" class="custom-checkbox">';
        }

        echo '</span><br>';

        echo '<span style="font-weight: bold; display: inline-block;">';
        
        if (!empty($row['qualification5'])) {
            echo '<label style="font-weight: bold; flex-grow: 1;">' . $row['qualification5'] . '</label>';
            echo '<input type="checkbox" name="qualification5" value="' . $row['qualification5'] . '" class="custom-checkbox">';
        }

        echo '</span><br>';

        echo '<span style="font-weight: bold; display: inline-block;">';
        
        if (!empty($row['qualification6'])) {
            echo '<label style="font-weight: bold; flex-grow: 1;">' . $row['qualification6'] . '</label>';
            echo '<input type="checkbox" name="qualification6" value="' . $row['qualification6'] . '" class="custom-checkbox">';
        }

        echo '</span><br>';

        echo '<span style="font-weight: bold; display: inline-block;">';
        
        if (!empty($row['qualification7'])) {
            echo '<label style="font-weight: bold; flex-grow: 1;">' . $row['qualification7'] . '</label>';
            echo '<input type="checkbox" name="qualification7" value="' . $row['qualification7'] . '" class="custom-checkbox">';
        }

        echo '</span><br>';

        echo '<span style="font-weight: bold; display: inline-block;">';
        
        if (!empty($row['qualification8'])) {
            echo '<label style="font-weight: bold; flex-grow: 1;">' . $row['qualification8'] . '</label>';
            echo '<input type="checkbox" name="qualification8" value="' . $row['qualification8'] . '" class="custom-checkbox">';
        }

        echo '</span><br>';

        echo '<span style="font-weight: bold; display: inline-block;">';
        
        if (!empty($row['qualification9'])) {
            echo '<label style="font-weight: bold; flex-grow: 1;">' . $row['qualification9'] . '</label>';
            echo '<input type="checkbox" name="qualification9" value="' . $row['qualification9'] . '" class="custom-checkbox">';
        }

        echo '</span><br>';

        echo '<span style="font-weight: bold; display: inline-block;">';
        
        if (!empty($row['qualification10'])) {
            echo '<label style="font-weight: bold; flex-grow: 1;">' . $row['qualification10'] . '</label>';
            echo '<input type="checkbox" name="qualification10" value="' . $row['qualification10'] . '" class="custom-checkbox">';
        }

        echo '</span><br>';

        // Display the "Other" checkbox regardless of qualification presence
        echo '<span style="font-weight: bold; display: inline-block;">';
        echo '<label style="font-weight: bold; flex-grow: 1;">Other</label>';
        echo '<input type="checkbox" name="qualification_other" value="Other" class="custom-checkbox">';
        echo '</span><br>';
    }
} else {
    // No qualifications found in the database
    echo 'No qualifications found.';
}

// Close the database connection
mysqli_close($conn);
?>

 
    <label for="experience"><b>Years of Experience:</b></label>
    <input type="number" name="experience" required>

    <label for="qualification">Qualification:</label>
    <input type="file" name="qualification" required>

    <input type="submit" value="Submit">
    </form>
    </div>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

