<?php
include('connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    if (!isset($_POST["admin_email"]) || empty($_POST["admin_email"])) {
        echo "<script>alert('Admin email is required!'); window.history.back();</script>";
        exit();
    }
    
    if (!isset($_POST["school_name"]) || empty($_POST["school_name"])) {
        echo "<script>alert('School name is required!'); window.history.back();</script>";
        exit();
    }
    
    if (!isset($_POST["theme_color"]) || empty($_POST["theme_color"])) {
        echo "<script>alert('Theme color is required!'); window.history.back();</script>";
        exit();
    }
    
    $email = $_POST["admin_email"];
    $school = $_POST["school_name"];
    $themeColor = $_POST["theme_color"];
    // Sanitize and validate the color code further if needed

    // Directory where uploaded files will be stored
    $uploadDir = "uploads/";

    // Create the "uploads" directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Handle logo upload
    $logoPath = null;
    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
        $logoName = $_FILES["logo"]["name"];
        $logoPath = $uploadDir . $logoName;
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($_FILES["logo"]["type"], $allowedTypes)) {
            echo "<script>alert('Invalid file type. Please upload a valid image file (JPG, PNG, GIF)!'); window.history.back();</script>";
            exit();
        }
    }

    // Check if a record with the same email exists in the database
    $sql = "SELECT id FROM admin_settings WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the existing record
        $row = $result->fetch_assoc();
        $settingsId = $row["id"];
        
        if ($logoPath) {
            $sql = "UPDATE admin_settings SET logo_path = ?, theme_color = ?, school = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $logoPath, $themeColor, $school, $settingsId);
        } else {
            $sql = "UPDATE admin_settings SET theme_color = ?, school = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $themeColor, $school, $settingsId);
        }
        $stmt->execute();
    } else {
        // Insert a new record
        if ($logoPath) {
            $sql = "INSERT INTO admin_settings (email, school, logo_path, theme_color) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $email, $school, $logoPath, $themeColor);
        } else {
            $sql = "INSERT INTO admin_settings (email, school, theme_color) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $email, $school, $themeColor);
        }
        $stmt->execute();
    }

    // Update school name in schools table and session
    $school_id = $_SESSION['school_id'] ?? 0;
    if ($school_id > 0) {
        db_query($conn, "UPDATE schools SET school_name = ? WHERE id = ?", "si", [$school, $school_id]);
    }
    $_SESSION['school'] = $school;

    // Move uploaded file if logo was provided
    if ($logoPath && move_uploaded_file($_FILES["logo"]["tmp_name"], $logoPath)) {
        header('Location: settings?success=1');
        exit();
    } elseif (!$logoPath) {
        header('Location: settings?success=1');
        exit();
    } else {
        header('Location: settings?error=logo_upload_failed');
        exit();
    }
}

?>

