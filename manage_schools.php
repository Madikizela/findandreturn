<?php
session_start();
include('connection.php');

// Check if logged in and is superUser
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true || $_SESSION['role'] !== 'superUser') {
    header("Location: login");
    exit();
}

$name_session = $_SESSION['user_name'];
$email_session = $_SESSION['socialWorkerEmail'];

// Default SuperAdmin theme
$themeColor = "#dc3545";
$logoPath = "img/Find&R.png";

$message = "";
$error = "";

// Handle adding a new school
if (isset($_POST['add_school'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security token invalid. Please refresh and try again.";
    } else {
    $school_name    = trim($_POST['school_name']);
    $address        = trim($_POST['address']);
    $contact_email  = trim($_POST['contact_email']);
    $contact_phone  = trim($_POST['contact_phone']);
    $admin_name     = trim($_POST['admin_name'] ?? '');
    $admin_email    = trim($_POST['admin_email'] ?? '');
    $admin_phone    = trim($_POST['admin_phone'] ?? '');

    if (empty($school_name)) {
        $error = "School name is required.";
    } elseif (!empty($admin_email) && !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid admin email address.";
    } else {
        $check_result = db_query($conn, "SELECT id FROM schools WHERE school_name = ?", "s", [$school_name]);

        if ($check_result && $check_result->num_rows > 0) {
            $error = "A school with this name already exists.";
        } else {
            $sql = "INSERT INTO schools (school_name, address, contact_email, contact_phone) VALUES (?, ?, ?, ?)";
            $insertResult = db_query($conn, $sql, "ssss", [$school_name, $address, $contact_email, $contact_phone]);

            if ($insertResult) {
                $new_school_id = $conn->insert_id;
                $message = "School \"$school_name\" added successfully!";

                // Create school admin user if email provided
                if (!empty($admin_email)) {
                    $existingUser = db_query($conn, "SELECT id FROM users WHERE email = ?", "s", [$admin_email]);
                    if ($existingUser && $existingUser->num_rows > 0) {
                        $message .= " Note: Admin email already exists — no new user created.";
                    } else {
                        // Generate random password
                        $chars    = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#$!';
                        $password = '';
                        for ($i = 0; $i < 10; $i++) {
                            $password .= $chars[random_int(0, strlen($chars) - 1)];
                        }
                        $hashed  = password_hash($password, PASSWORD_BCRYPT);
                        $surname = '';
                        $role    = 'user';

                        $userInsert = db_query($conn,
                            "INSERT INTO users (name, surname, email, role, phone_no, school_id, password, reg_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                            "sssssss", [$admin_name ?: $school_name . ' Admin', $surname, $admin_email, $role, $admin_phone, $new_school_id, $hashed]
                        );

                        if ($userInsert) {
                            // Send credentials email
                            $subject     = "Find and Return - Your School Admin Account for $school_name";
                            $htmlContent = '
                            <div style="max-width:600px; margin:0 auto; padding:20px; font-family:Arial,sans-serif;">
                                <h2 style="color:#007bff;">Welcome to Find and Return!</h2>
                                <p>Hello <strong>' . htmlspecialchars($admin_name ?: 'School Admin') . '</strong>,</p>
                                <p>Your school admin account has been created for <strong>' . htmlspecialchars($school_name) . '</strong>.</p>
                                <div style="background:#f4f4f4; padding:20px; border-radius:8px; margin:20px 0;">
                                    <p style="margin:0;"><strong>Login URL:</strong> <a href="https://findandreturn.co.za/login">findandreturn.co.za/login</a></p>
                                    <p style="margin:10px 0 0;"><strong>Email:</strong> ' . htmlspecialchars($admin_email) . '</p>
                                    <p style="margin:10px 0 0;"><strong>Password:</strong> ' . htmlspecialchars($password) . '</p>
                                </div>
                                <p style="color:#dc3545;"><strong>Please log in and change your password immediately.</strong></p>
                                <p>Kind Regards,<br><strong>Find and Return Team</strong></p>
                            </div>';

                            send_email($admin_email, $subject, $htmlContent);

                            // WhatsApp if phone provided
                            if (!empty($admin_phone)) {
                                send_whatsapp($admin_phone, '', true, 'admin_account_ready', [$school_name, $admin_email, $password], 'en_US');
                            }

                            $message .= " Admin account created and login credentials sent to $admin_email.";
                        }
                    }
                }
            } else {
                $error = "Error adding school. Please try again.";
            }
        }
    }
} // end CSRF check
}

// Handle deleting a school
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Modernized: Check if school has users or children using db_query
    $users_res = db_query($conn, "SELECT id FROM users WHERE school_id = ?", "i", [$delete_id]);
    $children_res = db_query($conn, "SELECT id FROM children WHERE school_id = ?", "i", [$delete_id]);

    if (($users_res && $users_res->num_rows > 0) || ($children_res && $children_res->num_rows > 0)) {
        $error = "Cannot delete school. It is associated with existing users or children.";
    } else {
        if (db_query($conn, "DELETE FROM schools WHERE id = ?", "i", [$delete_id])) {
            $message = "School deleted successfully!";
        } else {
            $error = "Error deleting school.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Manage Schools - Super Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'header_css.php'; ?>
    <style>
        .form-container, .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .section-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #333;
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
                        <h2 class="mb-0 p-1"><i class="fas fa-school me-2"></i>Manage Schools</h2>
                    </div>
                </header>

                <!-- Breadcrumb-->
                <div class="bg-white">
                    <div class="container-fluid">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 py-3">
                                <li class="breadcrumb-item"><a class="fw-light" href="s_dashboard">Home</a></li>
                                <li class="breadcrumb-item active fw-light" aria-current="page">Manage Schools</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="container-fluid">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Add School Form -->
                        <div class="col-lg-4">
                            <div class="form-container">
                                <h3 class="h4 section-header">Add New School</h3>
                                <form method="POST" action="manage_schools">
                                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                    <div class="mb-3">
                                        <label class="form-label">School Name <span class="text-danger">*</span></label>
                                        <input type="text" name="school_name" class="form-control" placeholder="Enter school name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea name="address" class="form-control" rows="2" placeholder="School address"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Contact Email</label>
                                        <input type="email" name="contact_email" class="form-control" placeholder="school@example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="tel" name="contact_phone" class="form-control" placeholder="Phone number">
                                    </div>
                                    <hr>
                                    <p class="text-muted small mb-2"><i class="fas fa-user-shield me-1"></i>School Admin Account (optional) — fill in to auto-create login and send credentials</p>
                                    <div class="mb-3">
                                        <label class="form-label">Admin Full Name</label>
                                        <input type="text" name="admin_name" class="form-control" placeholder="e.g. Thabo Nkosi">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Admin Email <small class="text-muted">(login will be sent here)</small></label>
                                        <input type="email" name="admin_email" class="form-control" placeholder="admin@school.com">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Admin Phone <small class="text-muted">(WhatsApp credentials)</small></label>
                                        <input type="tel" name="admin_phone" class="form-control" placeholder="e.g. 0731234567">
                                    </div>
                                    <button type="submit" name="add_school" class="btn btn-primary w-100">
                                        <i class="fas fa-plus-circle me-1"></i> Add School
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Schools List -->
                        <div class="col-lg-8">
                            <div class="table-container">
                                <h3 class="h4 section-header">Existing Schools</h3>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>School Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Date Added</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 1;
                                            $schools_res = $conn->query("SELECT * FROM schools ORDER BY school_name ASC");
                                            if ($schools_res && $schools_res->num_rows > 0) {
                                                while ($row = $schools_res->fetch_assoc()) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $count++; ?></td>
                                                        <td class="fw-bold"><?php echo h($row['school_name']); ?></td>
                                                        <td><small><?php echo h($row['contact_email'] ?: 'N/A'); ?></small></td>
                                                        <td><small><?php echo h($row['contact_phone'] ?: 'N/A'); ?></small></td>
                                                        <td><small><?php echo date('d M Y', strtotime($row['created_at'])); ?></small></td>
                                                        <td>
                                                            <a href="manage_schools.php?delete_id=<?php echo $row['id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger" 
                                                               onclick="return confirm('Are you sure you want to delete this school?')">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No schools found.</td></tr>";
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
        </div>
    </div>

    <!-- JavaScript files-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/front.js"></script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>
