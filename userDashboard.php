<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

$name = $_SESSION['user_name'];
$email = $_SESSION['socialWorkerEmail'];

// Get theme settings
$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#007bff";
    $logoPath = "img/default-logo.png";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'pwa-head.php'; ?>
    <title>Learning Dashboard - Find and Return</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="img/favicon.ico">
    <style>
        :root {
            --primary-color: <?php echo $themeColor; ?>;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: #333 !important;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }

        .dashboard-container {
            padding-top: 100px;
            padding-bottom: 50px;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 0;
        }

        .nav-tabs {
            border: none;
            margin-bottom: 30px;
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 15px;
            margin: 0 10px;
            padding: 15px 25px;
            font-weight: 600;
            color: #666;
            background: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .content-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-height: 400px;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 25px;
            text-align: center;
        }

        .grade-button {
            display: inline-block;
            padding: 15px 25px;
            font-size: 1.1rem;
            font-weight: 600;
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color)dd 100%);
            color: white;
            cursor: pointer;
            margin: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .grade-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
            color: white;
            text-decoration: none;
        }

        .grade-button.active {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-color: #28a745;
        }

        .subject-button {
            display: inline-block;
            padding: 12px 20px;
            font-size: 1rem;
            font-weight: 500;
            border: 2px solid #6c757d;
            border-radius: 10px;
            background: white;
            color: #333;
            cursor: pointer;
            margin: 6px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .subject-button:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
            text-decoration: none;
        }

        .subject-button.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .hidden {
            display: none;
        }

        .results-container {
            margin-top: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }

        .table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .btn-modern {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="<?php echo $logoPath; ?>" alt="Logo" width="50" height="50" class="d-inline-block align-text-top me-2" style="border-radius: 8px;">
                <i class="fas fa-graduation-cap me-2"></i>Learning Dashboard
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="user_dashboard">
                            <i class="fas fa-home me-1"></i>Main Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="school_marketplace">
                            <i class="fas fa-store me-1"></i>Marketplace
                        </a>
                    </li>
                    <li class="nav-item">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($name, 0, 1)); ?>
                            </div>
                            <span><?php echo $name; ?></span>
                            <a class="nav-link" href="Logout">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Card -->
            <div class="welcome-card">
                <h1 class="welcome-title">
                    <i class="fas fa-graduation-cap me-3"></i>Welcome to Your Learning Dashboard
                </h1>
                <p class="welcome-subtitle">
                    Explore educational resources, take quizzes, and enhance your knowledge
                </p>
            </div>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="video-tab" data-bs-toggle="tab" data-bs-target="#video" type="button" role="tab">
                        <i class="fas fa-video me-2"></i>Master Video Referral
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="material-tab" data-bs-toggle="tab" data-bs-target="#material" type="button" role="tab">
                        <i class="fas fa-book me-2"></i>View Study Material
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="quiz-tab" data-bs-toggle="tab" data-bs-target="#quiz" type="button" role="tab">
                        <i class="fas fa-question-circle me-2"></i>Take a Quiz Test
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="dashboardTabContent">
                <!-- Video Referral Section -->
                <div class="tab-pane fade show active" id="video" role="tabpanel">
                    <div class="content-card">
                        <h2 class="section-title">
                            <i class="fas fa-video me-3"></i>Master Video Referral
                        </h2>
                        
                        <div class="text-center mb-4">
                            <p class="lead">Select your grade to access educational videos:</p>
                        </div>

                        <div class="text-center">
                            <button class="grade-button" value="12">Grade 12</button>
                            <button class="grade-button" value="11">Grade 11</button>
                            <button class="grade-button" value="10">Grade 10</button>
                            <button class="grade-button" value="9">Grade 9</button>
                            <button class="grade-button" value="8">Grade 8</button>
                        </div>

                        <div id="subject-buttons" class="hidden text-center mt-4">
                            <h4 class="mb-3">Select a Subject:</h4>
                            <!-- Subject buttons will be generated here -->
                        </div>

                        <div id="results" class="results-container hidden">
                            <!-- Results will be displayed here -->
                        </div>
                    </div>
                </div>

                <!-- Study Material Section -->
                <div class="tab-pane fade" id="material" role="tabpanel">
                    <div class="content-card">
                        <h2 class="section-title">
                            <i class="fas fa-book me-3"></i>Study Material
                        </h2>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-file-pdf me-2"></i>Mathematics Notes
                                        </h5>
                                        <p class="card-text">Comprehensive notes covering all mathematical concepts.</p>
                                        <a href="#" class="btn btn-primary btn-modern">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-file-pdf me-2"></i>Science Notes
                                        </h5>
                                        <p class="card-text">Detailed notes for Physical and Life Sciences.</p>
                                        <a href="#" class="btn btn-primary btn-modern">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Section -->
                <div class="tab-pane fade" id="quiz" role="tabpanel">
                    <div class="content-card">
                        <h2 class="section-title">
                            <i class="fas fa-question-circle me-3"></i>Quiz Tests
                        </h2>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-center mb-3">
                                    <div class="card-body">
                                        <i class="fas fa-calculator fa-3x text-primary mb-3"></i>
                                        <h5 class="card-title">Mathematics Quiz</h5>
                                        <p class="card-text">Test your mathematical skills.</p>
                                        <a href="#" class="btn btn-success btn-modern">
                                            <i class="fas fa-play me-1"></i>Start Quiz
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center mb-3">
                                    <div class="card-body">
                                        <i class="fas fa-atom fa-3x text-info mb-3"></i>
                                        <h5 class="card-title">Science Quiz</h5>
                                        <p class="card-text">Challenge your scientific knowledge.</p>
                                        <a href="#" class="btn btn-success btn-modern">
                                            <i class="fas fa-play me-1"></i>Start Quiz
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center mb-3">
                                    <div class="card-body">
                                        <i class="fas fa-language fa-3x text-warning mb-3"></i>
                                        <h5 class="card-title">English Quiz</h5>
                                        <p class="card-text">Improve your language skills.</p>
                                        <a href="#" class="btn btn-success btn-modern">
                                            <i class="fas fa-play me-1"></i>Start Quiz
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to fetch subjects based on selected grade
        function fetchSubjects(grade) {
            // You need to implement fetching subjects from the database using AJAX
            // Here, I'm just simulating it with a hardcoded array
            const subjects = [
                "Mathematics",
                "Life Science",
                "Physical Science",
                "English",
                "History",
                "Geography",
                "Information Technology"
                // Add more subjects as needed
            ];

            return subjects;
        }

        // Function to create subject buttons
        function createSubjectButtons(subjects) {
            const subjectButtonsContainer = document.getElementById("subject-buttons");
            subjectButtonsContainer.innerHTML = ""; // Clear previous buttons

            subjects.forEach(subject => {
                const button = document.createElement("button");
                button.classList.add("subject-button");
                button.textContent = subject;
                subjectButtonsContainer.appendChild(button);
            });

            subjectButtonsContainer.classList.remove("hidden");
        }

        // Event listener for grade buttons
        const gradeButtons = document.querySelectorAll(".grade-button");
        gradeButtons.forEach(button => {
            button.addEventListener("click", () => {
                // Remove "active" class from all grade buttons
                gradeButtons.forEach(btn => {
                    btn.classList.remove("active");
                });

                // Add "active" class to the clicked grade button
                button.classList.add("active");

                const grade = button.value;
                const subjects = fetchSubjects(grade);
                createSubjectButtons(subjects);
            });
        });

        // Event listener for subject buttons (dynamically created)
        document.addEventListener("click", function(event) {
            if (event.target.classList.contains("subject-button")) {
                // Remove "active" class from all subject buttons
                document.querySelectorAll(".subject-button").forEach(btn => {
                    btn.classList.remove("active");
                });

                // Add "active" class to the clicked subject button
                event.target.classList.add("active");

                // Here you can implement the logic to fetch and display videos
                const subject = event.target.textContent;
                const activeGrade = document.querySelector(".grade-button.active").value;
                
                // Display results (you can replace this with actual video fetching)
                const resultsContainer = document.getElementById("results");
                resultsContainer.innerHTML = `
                    <h4><i class="fas fa-video me-2"></i>Videos for ${subject} - Grade ${activeGrade}</h4>
                    <p>Loading videos for ${subject} in Grade ${activeGrade}...</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Video content will be loaded here. Implement your video fetching logic.
                    </div>
                `;
                resultsContainer.classList.remove("hidden");
            }
        });
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>
