<?php
include('connection.php');

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];

// Use default Find and Return theme for parents
$themeColor = "#007bff";
$logoPath = "img/Find&R.png";

// Handle school selection
if (isset($_GET['school_id'])) {
    $selectedSchoolId = intval($_GET['school_id']);
    $_SESSION['school_id'] = $selectedSchoolId;
    
    // Fetch school name for session compatibility
    $stmt = $conn->prepare("SELECT school_name FROM schools WHERE id = ?");
    $stmt->bind_param("i", $selectedSchoolId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $_SESSION['school'] = $row['school_name'];
    }
    
    // Redirect to school page
    header("Location: school_list");
    exit();
}

// Modernized: Get all schools from the new schools table
$sql = "SELECT id, school_name FROM schools ORDER BY school_name ASC";
$schoolsResult = $conn->query($sql);

// Modernized: Get schools where user has children using the relational schema
$userSchoolsSql = "SELECT DISTINCT s.id 
                   FROM children c 
                   JOIN schools s ON c.school_id = s.id 
                   JOIN users u ON c.parent_id = u.id 
                   WHERE u.email = ?";
$userSchoolsResult = db_query($conn, $userSchoolsSql, "s", [$email]);
$userSchoolIds = array();
if ($userSchoolsResult && $userSchoolsResult->num_rows > 0) {
    while ($row = $userSchoolsResult->fetch_assoc()) {
        $userSchoolIds[] = $row['id'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>List of Schools - Find and Return</title>
    <meta name="description" content="Browse all schools using the Find and Return system">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <meta name="robots" content="all,follow">
    <!-- Google fonts - Poppins -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <!-- Choices CSS-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="css/custom.css">
    <!-- Favicon-->
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'header_css.php'; ?>
    <style>
        .schools-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .school-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .school-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }

        .school-card h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .school-card h3 i {
            margin-right: 10px;
            font-size: 1.2em;
        }

        .school-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-school {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-school:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-primary-school {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-primary-school:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-success-school {
            background-color: var(--success-color);
            color: white;
            border: none;
        }

        .btn-success-school:hover {
            background-color: #218838;
            color: white;
        }

        .btn-info-school {
            background-color: var(--info-color);
            color: white;
            border: none;
        }

        .btn-info-school:hover {
            background-color: #138496;
            color: white;
        }

        .user-school-badge {
            background-color: var(--success-color);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 10px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 12px;
            margin: 20px;
        }

        .page-header h1 {
            margin-bottom: 10px;
            font-weight: 700;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        .empty-state h4 {
            margin-bottom: 10px;
            color: #495057;
        }

        @media (max-width: 768px) {
            .schools-container {
                grid-template-columns: 1fr;
                padding: 15px;
            }
            
            .school-actions {
                flex-direction: column;
            }
            
            .btn-school {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <?php include 'main_header.php'; ?>
        
        <div class="page-content d-flex align-items-stretch">
            <?php include 'main_sidebar.php'; ?>
            
            <div class="content-inner w-100">
                <!-- Page Header-->
                <div class="page-header">
                    <h1><i class="fas fa-school me-3"></i>All Schools</h1>
                    <p>Browse and connect with schools using the Find and Return system</p>
                </div>
                
                <!-- Breadcrumb-->
                <div class="bg-white">
                    <div class="container-fluid">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 py-3">
                                <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                                <li class="breadcrumb-item active fw-light" aria-current="page">All Schools</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <div class="container-fluid">
                    <?php if ($schoolsResult && $schoolsResult->num_rows > 0): ?>
                        <div class="schools-container">
                            <?php while ($school = $schoolsResult->fetch_assoc()): ?>
                                <?php 
                                $schoolId = $school['id'];
                                $schoolName = $school['school_name'];
                                $isUserSchool = in_array($schoolId, $userSchoolIds);
                                ?>
                                <div class="school-card">
                                    <h3>
                                        <i class="fas fa-graduation-cap"></i>
                                        <?php echo htmlspecialchars($schoolName); ?>
                                        <?php if ($isUserSchool): ?>
                                            <span class="user-school-badge">Your School</span>
                                        <?php endif; ?>
                                    </h3>
                                    
                                    <div class="school-actions">
                                        <a href="list_of_schools.php?school_id=<?php echo $schoolId; ?>" 
                                           class="btn-school btn-primary-school">
                                            <i class="fas fa-eye"></i>
                                            View Details
                                        </a>
                                        
                                        <a href="school_marketplace?school_id=<?php echo $schoolId; ?>" 
                                           class="btn-school btn-success-school">
                                            <i class="fas fa-store"></i>
                                            School Store
                                        </a>
                                        
                                        <?php if ($isUserSchool): ?>
                                            <a href="view_lost_item.php?school_id=<?php echo $schoolId; ?>" 
                                               class="btn-school btn-info-school">
                                                <i class="fas fa-search"></i>
                                                Lost Items
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-school"></i>
                            <h4>No Schools Available</h4>
                            <p>There are currently no schools registered in the system.</p>
                            <a href="parent_dashboard" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript files-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <!-- Main File-->
    <script src="js/front.js"></script>
    <script>
        function injectSvgSprite(path) {
            var ajax = new XMLHttpRequest();
            ajax.open("GET", path, true);
            ajax.send();
            ajax.onload = function(e) {
                var div = document.createElement("div");
                div.className = 'd-none';
                div.innerHTML = ajax.responseText;
                document.body.insertBefore(div, document.body.childNodes[0]);
            }
        }
        // SVG sprite removed - external resource no longer available
    </script>
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

