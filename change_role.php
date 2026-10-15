<?php
session_start();
include('connection.php');

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

$id = $name = $surname = $role = $school_id = "";
$name_session = $_SESSION['user_name'];
$email_session = $_SESSION['socialWorkerEmail'];

// Fetch schools for dropdown
$schools_list = [];
$schools_res = $conn->query("SELECT id, school_name FROM schools ORDER BY school_name ASC");
if ($schools_res) {
    while ($row = $schools_res->fetch_assoc()) {
        $schools_list[] = $row;
    }
}

// Default SuperAdmin theme
$themeColor = "#dc3545";
$logoPath = "img/Find&R.png";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $email = $_POST['email'];
        // Modernized: Use users table
        $sql = "SELECT * FROM users WHERE email = ?";
        $result = db_query($conn, $sql, "s", [$email]);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = $row['id'];
            $name = $row['name'];
            $surname = $row['surname'];
            $role = $row['role'];
            $school_id = $row['school_id'];
        }
    }

    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $role = $_POST['role'];
        $school_id = !empty($_POST['school_id']) ? $_POST['school_id'] : null;

        // Modernized: Update users table with school_id
        $sql = "UPDATE users SET name = ?, surname = ?, role = ?, school_id = ? WHERE id = ?";
        $res = db_query($conn, $sql, "sssii", [$name, $surname, $role, $school_id, $id]);

        if ($res) {
            echo "<script>alert('Record updated successfully');</script>";
        } else {
            echo "<script>alert('Error updating record.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Change User Role - Super Admin</title>
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
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .section-header {
            border-bottom: 2px solid #f8f9fa;
            margin-bottom: 25px;
            padding-bottom: 10px;
            color: var(--primary-color);
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
                        <h2 class="mb-0 p-1"><i class="fas fa-user-cog me-2"></i>Change User Role</h2>
                    </div>
                </header>

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="form-container">
                                <h3 class="h4 section-header">Search User</h3>
                                <form class="form-horizontal mb-5" method="POST">
                                    <div class="row gy-2 mb-4 align-items-end">
                                        <div class="col-sm-9">
                                            <label class="form-label">Search by Email</label>
                                            <input class="form-control" name="email" type="email" placeholder="Enter user email..." required>
                                        </div>
                                        <div class="col-sm-3">
                                            <button class="btn btn-primary w-100" type="submit" name="search">
                                                <i class="fas fa-search me-1"></i> Search
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <?php if($id): ?>
                                <h3 class="h4 section-header">Update User Details</h3>
                                <form class="form-horizontal" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                                    
                                    <div class="row mb-3">
                                        <label class="col-sm-3 form-label">Name</label>
                                        <div class="col-sm-9">
                                            <input class="form-control" type="text" name="name" value="<?php echo h($name); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <label class="col-sm-3 form-label">Surname</label>
                                        <div class="col-sm-9">
                                            <input class="form-control" type="text" name="surname" value="<?php echo h($surname); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 form-label">Role</label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="role" required>
                                                <option value="user" <?php echo $role == 'user' ? 'selected' : ''; ?>>School Admin (user)</option>
                                                <option value="parent" <?php echo $role == 'parent' ? 'selected' : ''; ?>>Parent</option>
                                                <option value="teacher" <?php echo $role == 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                                <option value="superUser" <?php echo $role == 'superUser' ? 'selected' : ''; ?>>Super Admin</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 form-label">School</label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="school_id">
                                                <option value="">No School</option>
                                                <?php foreach ($schools_list as $s): ?>
                                                    <option value="<?php echo $s['id']; ?>" <?php echo ($school_id == $s['id']) ? 'selected' : ''; ?>>
                                                        <?php echo h($s['school_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-sm-9 ms-auto">
                                            <button class="btn btn-success" type="submit" name="update">
                                                <i class="fas fa-save me-1"></i> Update Role
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <?php endif; ?>
                            </div>
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

