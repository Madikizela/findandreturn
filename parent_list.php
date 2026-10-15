<?php
session_start();
include('connection.php');

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

$name_session = $_SESSION['user_name'];
$email_session = $_SESSION['socialWorkerEmail'];

// Default SuperAdmin theme
$themeColor = "#dc3545";
$logoPath = "img/Find&R.png";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Parent List - Super Admin</title>
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
                        <h2 class="mb-0 p-1"><i class="fas fa-user-friends me-2"></i>Parent List</h2>
                    </div>
                </header>

                <!-- Breadcrumb-->
                <div class="bg-white">
                    <div class="container-fluid">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 py-3">
                                <li class="breadcrumb-item"><a class="fw-light" href="s_dashboard">Home</a></li>
                                <li class="breadcrumb-item active fw-light" aria-current="page">Parent List</li>
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
                                        <th>Email</th>
                                        <th>Alt. Email</th>
                                        <th>Phone</th>
                                        <th>Alt. Phone</th>
                                        <th>Address</th>
                                        <th>School</th>
                                        <th>Reg. Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    // Modernized: Use users table and join with schools
                                    $sql = "SELECT u.*, s.school_name 
                                            FROM users u 
                                            LEFT JOIN schools s ON u.school_id = s.id 
                                            WHERE u.role = 'parent'
                                            ORDER BY u.reg_date DESC";
                                    $result = $conn->query($sql);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td class="fw-bold"><?php echo h($row["name"]); ?></td>
                                                <td><?php echo h($row["surname"]); ?></td>
                                                <td><small><?php echo h($row["email"]); ?></small></td>
                                                <td><small><?php echo h($row["alternative_email"] ?: 'N/A'); ?></small></td>
                                                <td><?php echo h($row["phone_no"]); ?></td>
                                                <td><?php echo h($row["alternative_phone_no"] ?: 'N/A'); ?></td>
                                                <td><small><?php echo h($row["address"]); ?></small></td>
                                                <td><span class="badge bg-light text-dark"><?php echo h($row["school_name"] ?? 'N/A'); ?></span></td>
                                                <td><small><?php echo date('d M Y', strtotime($row["reg_date"])); ?></small></td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='10' class='text-center py-4 text-muted'>No parents found.</td></tr>";
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

