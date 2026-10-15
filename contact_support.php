<?php
include_once 'connection.php';

$parent_id  = $_SESSION['user_id'] ?? 0;
$parent_email = $_SESSION['socialWorkerEmail'] ?? '';
$parent_name  = $_SESSION['user_name'] ?? '';
$school_id    = $_SESSION['school_id'] ?? 0;

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true || $_SESSION['role'] !== 'parent') {
    header("Location: login");
    exit();
}

// Auto-create table
$conn->query("CREATE TABLE IF NOT EXISTS parent_enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    school_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending','assigned','resolved') DEFAULT 'pending',
    assigned_agent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_agent_id) REFERENCES school_agents(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$success = '';
$error   = '';

// Submit enquiry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_enquiry'])) {
    $subject          = trim($_POST['subject'] ?? '');
    $message          = trim($_POST['message'] ?? '');
    $selected_school  = intval($_POST['school_id'] ?? 0);

    if (empty($subject) || empty($message) || $selected_school === 0) {
        $error = 'Please select a school and provide subject and message.';
    } else {
        $insert = db_query($conn,
            "INSERT INTO parent_enquiries (parent_id, school_id, subject, message) VALUES (?, ?, ?, ?)",
            "iiss", [$parent_id, $selected_school, $subject, $message]
        );
        if ($insert) {
            $success = 'Enquiry submitted! The school will assign an agent to assist you via WhatsApp.';
        } else {
            $error = 'Failed to submit enquiry. Please try again.';
        }
    }
}

// Fetch parent's schools (schools where they have children)
$parent_schools = [];
$schoolsRes = db_query($conn,
    "SELECT DISTINCT s.id, s.school_name 
     FROM children c
     JOIN schools s ON c.school_id = s.id
     WHERE c.parent_id = ?
     ORDER BY s.school_name ASC",
    "i", [$parent_id]
);
if ($schoolsRes && $schoolsRes->num_rows > 0) {
    while ($row = $schoolsRes->fetch_assoc()) {
        $parent_schools[] = $row;
    }
}

// Fetch parent's enquiries (with school name)
$enquiries = [];
$enqRes = db_query($conn,
    "SELECT pe.*, s.school_name, sa.name as agent_name, sa.whatsapp_no as agent_wa
     FROM parent_enquiries pe
     LEFT JOIN schools s ON pe.school_id = s.id
     LEFT JOIN school_agents sa ON pe.assigned_agent_id = sa.id
     WHERE pe.parent_id = ?
     ORDER BY pe.created_at DESC",
    "i", [$parent_id]
);
if ($enqRes && $enqRes->num_rows > 0) {
    while ($row = $enqRes->fetch_assoc()) {
        $enquiries[] = $row;
    }
}

$themeColor = "#007bff";
$logoPath   = "img/Find&R.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Contact Support - Find and Return</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <link rel="stylesheet" href="css/style.default.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'header_css.php'; ?>
    <style>
        .enquiry-card { border-radius: 12px; border: 1px solid #e0e0e0; margin-bottom: 16px; padding: 16px; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-pending   { background: #fff3cd; color: #856404; }
        .status-assigned  { background: #d1ecf1; color: #0c5460; }
        .status-resolved  { background: #d4edda; color: #155724; }
        .wa-agent-btn { background: #25D366; color: #fff; padding: 8px 16px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 14px; }
        .wa-agent-btn:hover { background: #1ebe5d; color: #fff; }
    </style>
</head>
<body>
<div class="page">
    <?php include 'main_header.php'; ?>
    <div class="page-content d-flex align-items-stretch">
        <?php include 'main_sidebar.php'; ?>
        <div class="content-inner w-100">
            <header class="bg-white shadow-sm px-4 py-3">
                <h2 class="mb-0"><i class="fab fa-whatsapp me-2" style="color:#25D366;"></i>Contact Support</h2>
            </header>
            <div class="bg-white">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 py-3">
                            <li class="breadcrumb-item"><a href="parent_dashboard">Home</a></li>
                            <li class="breadcrumb-item active">Contact Support</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="container-fluid py-4">
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Submit Enquiry Form -->
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><i class="fas fa-comment-dots me-2"></i>Submit New Enquiry</h5>
                                <p class="text-muted small mb-3">A school agent will be assigned to assist you via WhatsApp.</p>
                                <?php if (empty($parent_schools)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>You don't have any children registered yet. <a href="add_child">Add a child</a> first to contact school support.
                                </div>
                                <?php else: ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Select School <span class="text-danger">*</span></label>
                                        <select name="school_id" class="form-select" required>
                                            <option value="">Choose a school...</option>
                                            <?php foreach ($parent_schools as $school): ?>
                                            <option value="<?php echo $school['id']; ?>"><?php echo htmlspecialchars($school['school_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Only schools where you have registered children are shown.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                                        <input type="text" name="subject" class="form-control" placeholder="e.g. Lost item query, Payment issue" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                                        <textarea name="message" class="form-control" rows="5" placeholder="Describe your enquiry in detail..." required></textarea>
                                    </div>
                                    <button type="submit" name="submit_enquiry" class="btn btn-success w-100">
                                        <i class="fab fa-whatsapp me-2"></i>Submit Enquiry
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- My Enquiries -->
                    <div class="col-lg-7">
                        <h5 class="mb-3"><i class="fas fa-history me-2"></i>My Enquiries</h5>
                        <?php if (empty($enquiries)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-4x mb-3" style="opacity:0.3;"></i>
                            <p>No enquiries yet. Submit your first enquiry above.</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($enquiries as $enq): ?>
                        <div class="enquiry-card bg-white">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($enq['subject']); ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-school me-1"></i><?php echo htmlspecialchars($enq['school_name'] ?? 'Unknown'); ?>
                                        &bull; <i class="far fa-clock me-1"></i><?php echo date('M d, Y H:i', strtotime($enq['created_at'])); ?>
                                    </small>
                                </div>
                                <span class="status-badge status-<?php echo $enq['status']; ?>">
                                    <?php echo ucfirst($enq['status']); ?>
                                </span>
                            </div>
                            <p class="text-muted mb-2"><?php echo nl2br(htmlspecialchars($enq['message'])); ?></p>
                            <?php if ($enq['status'] === 'assigned' && $enq['agent_wa']): ?>
                            <div class="mt-3 p-2 bg-light rounded">
                                <small class="d-block mb-2 fw-semibold text-success"><i class="fas fa-user-check me-1"></i>Assigned to: <?php echo htmlspecialchars($enq['agent_name']); ?></small>
                                <a href="https://wa.me/<?php echo htmlspecialchars($enq['agent_wa']); ?>?text=Hi!%20Regarding%20my%20enquiry:%20<?php echo urlencode($enq['subject']); ?>" target="_blank" class="wa-agent-btn">
                                    <i class="fab fa-whatsapp"></i> Chat with Agent
                                </a>
                            </div>
                            <?php elseif ($enq['status'] === 'resolved'): ?>
                            <small class="text-success d-block mt-2"><i class="fas fa-check-double me-1"></i>Resolved on <?php echo date('M d, Y', strtotime($enq['resolved_at'])); ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/front.js"></script>
<?php include 'pwa-footer.php'; ?>
</body>
</html>
