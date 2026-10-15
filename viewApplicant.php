
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'pwa-head.php'; ?>
    <link rel="shortcut icon" href="img/favicon.ico">
    <title>Dashboard</title>
    <style>
      body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
        }

        header {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: center;
        }
        nav {
            background-color: #ddd;
            padding: 10px;
            text-align: center;
        }

        nav a {
            display: inline-block;
            margin: 0 10px;
            color: #333;
            text-decoration: none;
            font-size: 16px;
        }

        nav a i {
            margin-right: 5px;
        }

        main {
            padding: 20px;
        }

        section {
            display: none;
            margin-bottom: 20px;
            background-color: whitesmoke;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #333;
            color: black;
        }

        footer {
            background-color: #4caf50;
            color: black;
            padding: 10px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

    <header>
        <h1>Welcome to Your Dashboard</h1>
    </header>
    <nav style="background-color: black;">
    <a href="javascript:void(0);" onclick="toggleSection('section1')">Applicant List</a> |
    <a href="javascript:void(0);" onclick="toggleSection('section2')">Add Job Post</a> |
    <a href="javascript:void(0);" onclick="toggleSection('section3')">Upload Study Material</a> |
    <a href="javascript:void(0);" onclick="toggleSection('section4')">Upload Useful Links</a> |
    <a href="javascript:void(0);" onclick="toggleSection('section5')">Post Bursaries</a> |
</nav>


    <main>
        <section id="section1">
            <h2>Applicant List</h2>
            <?php
                // display.php
                // Assuming you have connected to the database
                include("connection.php");

                // Select data from the database
                $sqlSelect = "SELECT * FROM applications";
                $result = $conn->query($sqlSelect);

                if ($result->num_rows > 0) {
                    echo '<table>';
                    echo '<tr><th>Name</th><th>Surname</th><th>Age</th><th>Email</th><th>Contact</th><th>Title</th><th>Address</th><th>Experience</th><th>Qualification File</th></tr>';
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row['name'] . '</td>';
                        echo '<td>' . $row['surname'] . '</td>';
                        echo '<td>' . $row['age'] . '</td>';
                        echo '<td>' . $row['email'] . '</td>';
                        echo '<td>' . $row['contact'] . '</td>';
                        echo '<td>' . $row['title'] . '</td>';
                        echo '<td>' . $row['address'] . '</td>';
                        echo '<td>' . $row['experience'] . '</td>';
                        echo '<td><a href="' . $row["qualification"] . '" target="_blank">Open PDF</a></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo 'No applications found.';
                }

                // Close the database connection
                $conn->close();
            ?>
        </section>

<section id="section2">
    <h2>Add Post</h2>
    <p>
    <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
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
    <link rel="shortcut icon" href="img/favicon.ico">
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
        // Retrieve form data
        $title = $_POST["title"];
        $description = $_POST["description"];
        $location = $_POST["location"];
        $employmentStatus = $_POST["employmentStatus"];
        $date =$_POST["date"];
        $closeDate =$_POST["closeDate"];
        $experience = $_POST["experience"];
        $age = $_POST["age"];
        $qualification = mysqli_real_escape_string($conn, $_POST["qualification"]);
        $qualification2 = mysqli_real_escape_string($conn, $_POST["qualification2"]);
        $qualification3 = mysqli_real_escape_string($conn, $_POST["qualification3"]);
        $qualification4 = mysqli_real_escape_string($conn, $_POST["qualification4"]);
        $qualification5 = mysqli_real_escape_string($conn, $_POST["qualification5"]);
        $qualification6 = mysqli_real_escape_string($conn, $_POST["qualification6"]);
        $qualification7 = mysqli_real_escape_string($conn, $_POST["qualification7"]);
        $qualification8 = mysqli_real_escape_string($conn, $_POST["qualification8"]);
        $qualification9 = mysqli_real_escape_string($conn, $_POST["qualification9"]);
        $qualification10 = mysqli_real_escape_string($conn, $_POST["qualification10"]);
        
        // SQL query to insert data into the jobs table
        $sql = "INSERT INTO careers (title, description,location,employmentStatus,date,closeDate, experience, age, qualification, qualification2, qualification3,qualification4,qualification5,qualification6,qualification7,qualification8,
        qualification9,qualification10)
                VALUES ('$title', '$description','$location','$employmentStatus','$date','$closeDate', '$experience', '$age', '$qualification', '$qualification2', '$qualification3', '$qualification4', '$qualification5', '$qualification6', '$qualification7'
                , '$qualification8', '$qualification9', '$qualification10')";

        if ($conn->query($sql) === TRUE) {
            echo "<p>Job posted successfully!</p>";
        } else {
            echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
        }

        // Close connection
        $conn->close();
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"  method="post">
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

    </p>
</section>

<section id="section3">
    <h2>Post Previous Papers</h2>
    <p><div class="container">
	<div class="d-flex justify-content-center h-100">
		<div class="card">
			<div class="card-body">
            <form method="post" action="study.php" enctype="multipart/form-data">
    <div class="input-group form-group">
        <select id="role" name="grade" class="form-control" required>
            <option value="">Select Grade</option>
            <option value="12">12</option>
            <option value="11">11</option>
            <option value="10">10</option>
            <option value="9">9</option>
            <option value="8">8</option>
        </select>
    </div>
    <div class="input-group form-group">
        <select id="role" name="subject" class="form-control" required>
            <option value="">Select Subject</option>
            <option value="Mathematics">Mathematics</option>
            <option value="Physical Science">Physical Science</option>
            <option value="Life Science">Life Science</option>
            <option value="Geography">Geography</option>
            <option value="Information Technology">Information Technology</option>
        </select>
    </div>
    <div class="input-group form-group">
        <input type="file" class="form-control" name="file_path" required placeholder="">
    </div>
    <div class="form-group">
        <input type="submit" value="Submit" class="btn float-left login_btn">
    </div>
</form>
	</div>
	</div>
	</div>
</div>
</p>
</section>
<section id="section4">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Table Form</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        .comment-section {
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>

<h2>Populate YouTube links</h2>

<form id="mathsForm" method="post" action="links.php">
    <label for="grade">Grade:</label>
    <select id="grade" name="grade" required>
        <option value="12">12</option>
        <option value="11">11</option>
        <option value="10">10</option>
    </select>

    <label for="subject">Subject:</label>
    <select id="subject" name="subject" required>
        <option value="">Select Subject</option>
        <option value="Mathematics">Mathematics</option>
        <option value="Physical Science">Physical Science</option>
        <option value="Life Science">Life Science</option>
        <option value="Geography">Geography</option>
        <option value="Information Technology">Information Technology</option>
    </select>

    <label for="mathsTopic">Topic:</label>
    <input type="text" id="mathsTopic" name="mathsTopic" required>

    <label for="link">Link to Relevant Topic:</label>
    <input type="url" id="link" name="link" required>


    <input type="submit">
</form>
</body>
</html>

</section>
<section id="section5">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Table Form</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        .comment-section {
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>

<h2></h2>

<form id="mathsForm" method="post" action="bursaries.php">
  
    <label for="mathsTopic">Heading:</label>
    <input type="text" id="mathsTopic" name="heading" required>

    <label for="link">Page Link:</label>
    <input type="url" id="link" name="link" required>

    <label for="comment">Deadline:</label>
    <input type="date" name="date" class="comment-section" required>

    <input type="submit">
</form>
</body>
</html>

</section>
</main>
<script>
function toggleSection(sectionId) {
    var sections = document.querySelectorAll('section');
    sections.forEach(function(section) {
        section.style.display = 'none';
    });

    var selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.style.display = 'block';
    }
}
</script>

</body>
</html>
