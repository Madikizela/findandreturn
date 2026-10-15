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

        <!-- Template Stylesheet -->
        <link href="css/style.css" rel="stylesheet">
        <style>
        body {
            font-family: Arial, sans-serif;
        }

        .job-container {
            max-width: 600px;
            margin: auto;
        }

        .job {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .apply-button {
            background-color: #4caf50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>

    <script>
        function applyJob(jobTitle) {
            // Placeholder function, you can customize it for your needs
            console.log('Applying for job:', jobTitle);
            // Add your logic for handling the application here
        }
    </script>
</head>
    </head>
    <body>
        <!-- Spinner Start -->
        <div id="spinner" class="show position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-grow text-primary" role="status"></div>
        </div>
        <!-- Spinner End -->

        <!-- Topbar Start -->
        <div class="container-fluid bg-dark py-2 d-none d-md-flex">
            <div class="container">
                <div class="d-flex justify-content-between topbar">
                    <div class="top-info">
                        <small class="me-3 text-white-50"><a href="#"><i class="fas fa-map-marker-alt me-2 text-secondary"></i></a>Matatiele Alfred Nzo District Eastern Cape</small>
                        <small class="me-3 text-white-50"><a href="#"><i class="fas fa-envelope me-2 text-secondary"></i></a>info@mdi-za.org</small>
                    </div>
                    <div id="note" class="text-secondary d-none d-xl-flex"><small></small></div>
                    <div class="top-link">
                        <a href="https://www.facebook.com/profile.php?id=100090768176161&mibextid=LQQJ4d" class="bg-light nav-fill btn btn-sm-square rounded-circle"><i class="fab fa-facebook-f text-secondary"></i></a>
                        <a href="https://www.linkedin.com/company/mgobozi-developmental-initiatives-mdi/" class="bg-light nav-fill btn btn-sm-square rounded-circle me-0"><i class="fab fa-linkedin-in text-secondary"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Topbar End -->

        <!-- Navbar Start -->
        <div class="container-fluid bg-primary">
            <div class="container">
                <nav class="navbar navbar-dark navbar-expand-lg py-0">
                    <a href="index.html" class="navbar-brand">
                    <img src="img/logo.png" alt="" width="110" height="100" class="d-inline-block align-text-top">
                    </a>
                    <button type="button" class="navbar-toggler me-0" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse bg-transparent" id="navbarCollapse">
                    <div class="navbar-nav ms-auto mx-xl-auto p-0">
    <a href="index" class="nav-item nav-link active text-secondary">Home</a>
    <a href="about" class="nav-item nav-link">About</a>
    <a href="service" class="nav-item nav-link">Services</a>
    <div class="nav-item dropdown">
    <a href="#" class="nav-link dropdown-toggle" id="learnerPortalDropdown" role="button" data-toggle="dropdown"
       aria-haspopup="true" aria-expanded="false" onclick="toggleSubheadings()">Learner Portal</a>
    <div class="dropdown-menu" aria-labelledby="learnerPortalDropdown">
        <a class="dropdown-item" href="learnerPortal">University Links</a>
        <a class="dropdown-item" href="resource">Learner Resource Directory</a>
        <a class="dropdown-item" href="funding">Bursary Funding</a>
        <a class="dropdown-item" href="nbt">NBT Test Dates</a>
        <!-- Add more subheadings as needed -->
    </div>
</div>

<script>
    function toggleSubheadings() {
        // Toggle the visibility of the subheadings
        $('.dropdown-menu').toggle();
    }
</script>

    <a href="careers" class="nav-item nav-link">Careers</a>
    <a href="https://form.jotform.com/233170593368057" class="nav-item nav-link">School Registration</a>
    <a href="portfolio" class="nav-item nav-link">
Gallery</a>
</div>

                    </div>
                    <div class="d-none d-xl-flex flex-shirink-0">
                        <div id="phone-tada" class="d-flex align-items-center justify-content-center me-4">
                            <a href="" class="position-relative animated tada infinite">
                                <i class="fa fa-phone-alt text-white fa-2x"></i>
                                <div class="position-absolute" style="top: -7px; left: 20px;">
                                    <span><i class="fa fa-comment-dots text-secondary"></i></span>
                                </div>
                            </a>
                        </div>
                        <div class="d-flex flex-column pe-4 border-end">
                            <span class="text-black-50">Have any questions?</span>
                            <span class="text-secondary">Call: 0846725409</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center ms-4 ">
                            <a href="#"><i class="bi bi-search text-white fa-2x"></i> </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

<body>

<?php
// Database connection configuration
include("connection.php");

// Fetch job data from the database
$sql = "SELECT * FROM careers";
$result = $conn->query($sql);

// Display jobs
if ($result->num_rows > 0) {
    echo '<div class="job-container">';
    while ($row = $result->fetch_assoc()) {
        echo '<div class="job">';
        echo '<h2>' . $row['title'] . '</h2>';
        echo '<p>' . $row['description'] . '</p>';
        echo '<p>Job Location: ' . $row['location'] . '</p>';
        echo '<p>Employment Status: ' . $row['employmentStatus'] . '</p>';
        echo '<p>If Short Listed Applicant can expect communication by: ' . $row['date'] . '</p>';
        echo '<p>Closing Date: ' . $row['closeDate'] . '</p>';
        echo '<p>Experience: years (Minimum: ' . $row['experience'] . ' years)</p>';
        echo '<p>Not Older than: ' . $row['age'] . '</p>';
        echo '<a href="apply.php?title=' . urlencode($row['title']) . '" class="apply-button">Apply</a>';
        echo '</div>';
    }
    echo '</div>';
} else {
    echo "No jobs found";
}

// Close connection
$conn->close();
?>
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="lib/wow/wow.min.js"></script>
        <script src="lib/easing/easing.min.js"></script>
        <script src="lib/waypoints/waypoints.min.js"></script>
        <script src="lib/owlcarousel/owl.carousel.min.js"></script>

        <!-- Template Javascript -->
        <script src="js/main.js"></script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

