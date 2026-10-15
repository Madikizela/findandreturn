<?php
session_start();
include('connection.php');

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

$role = $_SESSION['role'] ?? '';
$school = $_SESSION['school'] ?? '';
$name_session = $_SESSION['user_name'];
$email_session = $_SESSION['socialWorkerEmail'];
$logoPath = "img/Find&R.png"; // Default logo

// Determine theme and title based on role
if ($role == 'superUser') {
    $themeColor = "#dc3545"; // Red for Super Admin
    $pageTitle = "All Children List";
    $homeLink = "s_dashboard.php";
} else {
    // For School Admins, fetch their school theme
    $sql_theme = "SELECT theme_color, logo_path FROM admin_settings WHERE email = ?";
    $result_theme = db_query($conn, $sql_theme, "s", [$email_session]);
    
    if ($result_theme && $result_theme->num_rows > 0) {
        $row_theme = $result_theme->fetch_assoc();
        $themeColor = $row_theme["theme_color"];
        if (!empty($row_theme["logo_path"])) {
            $logoPath = $row_theme["logo_path"];
        }
    } else {
        $themeColor = "#007bff"; // Default blue
    }
    
    $pageTitle = "Learners List - " . ($school ?: 'My School');
    $homeLink = "user_dashboard.php";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo h($pageTitle); ?> - Find and Return</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <meta name="robots" content="all,follow">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'header_css.php'; ?>
    <style>
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .qr-img {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 2px;
            background: white;
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
                <header class="bg-white shadow-sm px-4 py-3 z-index-20">
                    <div class="container-fluid px-0">
                        <h2 class="mb-0 p-1"><i class="fas fa-child me-2"></i><?php echo h($pageTitle); ?></h2>
                    </div>
                </header>

                <!-- Breadcrumb-->
                <div class="bg-white">
                    <div class="container-fluid">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 py-3">
                                <li class="breadcrumb-item"><a class="fw-light" href="<?php echo h($homeLink); ?>">Home</a></li>
                                <li class="breadcrumb-item active fw-light" aria-current="page">Children List</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="container-fluid">
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>Parent Email</th>
                                        <th>Child Phone</th>
                                        <th>Parent Phone</th>
                                        <th>Address</th>
                                        <?php if ($role == 'superUser'): ?>
                                        <th>School</th>
                                        <?php endif; ?>
                                        <th>Grade</th>
                                        <th>QR Code</th>
                                        <th>Reg. Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    $school_id = $_SESSION['school_id'] ?? 0;
                                    
                                    // Build modernized query based on role
                                    if ($role == 'superUser') {
                                        // Modernized: Join with users and schools
                                        $sql = "SELECT c.*, u.email as parent_email, u.phone_no, u.alternative_phone_no, u.address, s.school_name 
                                                FROM children c 
                                                JOIN users u ON c.parent_id = u.id 
                                                JOIN schools s ON c.school_id = s.id 
                                                ORDER BY c.reg_date DESC";
                                        $params = [];
                                        $types = "";
                                    } else {
                                        // Modernized: Join with users for the specific school
                                        $sql = "SELECT c.*, u.email as parent_email, u.phone_no, u.alternative_phone_no, u.address, s.school_name 
                                                FROM children c 
                                                JOIN users u ON c.parent_id = u.id 
                                                JOIN schools s ON c.school_id = s.id 
                                                WHERE c.school_id = ? 
                                                ORDER BY c.reg_date DESC";
                                        $params = [$school_id];
                                        $types = "i";
                                    }
                                    
                                    $result = db_query($conn, $sql, $types, $params);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td class="fw-bold"><?php echo h($row["first_name"]); ?></td>
                                                <td><?php echo h($row["surname"]); ?></td>
                                                <td><small><?php echo h($row["parent_email"]); ?></small></td>
                                                <td><?php echo h($row["phone_no"] ?? 'N/A'); ?></td>
                                                <td><?php echo h($row["alternative_phone_no"] ?? 'N/A'); ?></td>
                                                <td><?php echo h($row["address"] ?? 'N/A'); ?></td>
                                                <?php if ($role == 'superUser'): ?>
                                                <td><span class="badge bg-light text-dark"><?php echo h($row["school_name"]); ?></span></td>
                                                <?php endif; ?>
                                                <td>Grade <?php echo h($row["grade"]); ?></td>
                                                <td>
                                                    <?php if($row["qr_image"]): ?>
                                                        <img src="<?php echo h($row["qr_image"]); ?>" width="45" height="45" class="qr-img" alt="QR">
                                                    <?php else: ?>
                                                        <span class="text-muted small">No QR</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><small><?php echo date('d M Y', strtotime($row["reg_date"])); ?></small></td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='11' class='text-center py-4 text-muted'>No children found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript files-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="js/front.js"></script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

