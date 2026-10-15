<?php
include_once 'connection.php';

// Auto-create school_agents table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS school_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone_no VARCHAR(20) DEFAULT '',
    email VARCHAR(255) DEFAULT '',
    whatsapp_no VARCHAR(20) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$email    = $_SESSION['socialWorkerEmail'] ?? '';
$name     = $_SESSION['user_name'] ?? '';
$role     = $_SESSION['role'] ?? '';
$school_id = $_SESSION['school_id'] ?? 0;

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

if ($role !== 'user' && $role !== 'superUser') {
    header("Location: login");
    exit();
}

// Get school name
$schoolRow = db_query($conn, "SELECT school_name FROM schools WHERE id = ?", "i", [$school_id]);
$schoolName = ($schoolRow && $schoolRow->num_rows > 0) ? $schoolRow->fetch_assoc()['school_name'] : 'Your School';

$themeColor = "#007bff";
$logoPath   = "img/Find&R.png";
$settingsRow = db_query($conn, "SELECT logo_path, theme_color FROM admin_settings WHERE email = ?", "s", [$email]);
if ($settingsRow && $settingsRow->num_rows > 0) {
    $s = $settingsRow->fetch_assoc();
    $themeColor = $s['theme_color'] ?? $themeColor;
    $logoPath   = $s['logo_path']   ?? $logoPath;
}

$success = '';
$error   = '';

// Handle Add Agent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add') {
        $agentName   = trim($_POST['agent_name'] ?? '');
        $agentPhone  = trim($_POST['phone_no'] ?? '');
        $agentEmail  = trim($_POST['agent_email'] ?? '');
        $agentWA     = preg_replace('/[^0-9]/', '', $_POST['whatsapp_no'] ?? '');

        // Auto-format SA number
        if (strlen($agentWA) === 10 && strpos($agentWA, '0') === 0) {
            $agentWA = '27' . substr($agentWA, 1);
        }

        if (empty($agentName) || empty($agentWA)) {
            $error = 'Agent name and WhatsApp number are required.';
        } else {
            $ins = db_query($conn,
                "INSERT INTO school_agents (school_id, name, phone_no, email, whatsapp_no) VALUES (?, ?, ?, ?, ?)",
                "issss", [$school_id, $agentName, $agentPhone, $agentEmail, $agentWA]
            );
            if ($ins) {
                $waSent = false;
                $waError = '';

                // Send agent_welcome template with name and school as parameters.
                // Using a template guarantees delivery regardless of conversation window state.
                // Template body: "Hello {{1}}! You have been added as a support agent for {{2}}
                //                  on Find and Return. Parents may contact you via WhatsApp for enquiries."
                global $last_whatsapp_error;
                $waSent = send_whatsapp($agentWA, '', true, 'agent_welcome', [$agentName, $schoolName], 'en_US');
                error_log("School Agents - agent_welcome template: number=$agentWA, sent=" . ($waSent ? 'YES' : 'NO') . ", error=" . ($last_whatsapp_error ?? 'none'));
                if (!$waSent) {
                    $waError = $last_whatsapp_error ?? 'WhatsApp notification failed';
                }

                $success = "Agent \"$agentName\" added" . ($waSent ? " and notified via WhatsApp." : " but WhatsApp notification failed: " . $waError);
            } else {
                $error = 'Failed to add agent. Please try again.';
            }
        }
    }

    if ($_POST['action'] === 'toggle') {
        $agentId    = intval($_POST['agent_id'] ?? 0);
        $newStatus  = intval($_POST['new_status'] ?? 0);
        db_query($conn, "UPDATE school_agents SET is_active = ? WHERE id = ? AND school_id = ?",
            "iii", [$newStatus, $agentId, $school_id]);
        $success = 'Agent status updated.';
    }

    if ($_POST['action'] === 'delete') {
        $agentId = intval($_POST['agent_id'] ?? 0);
        db_query($conn, "DELETE FROM school_agents WHERE id = ? AND school_id = ?", "ii", [$agentId, $school_id]);
        $success = 'Agent removed.';
    }
}

// Fetch agents
$agents = [];
$agentsResult = db_query($conn, "SELECT * FROM school_agents WHERE school_id = ? ORDER BY is_active DESC, created_at DESC", "i", [$school_id]);
if ($agentsResult && $agentsResult->num_rows > 0) {
    while ($row = $agentsResult->fetch_assoc()) {
        $agents[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>School Agents - Find and Return</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'header_css.php'; ?>
    <style>
        .agent-card {
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            transition: box-shadow 0.2s;
        }
        .agent-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .agent-avatar {
            width: 48px; height: 48px; border-radius: 50%;
            background: <?php echo $themeColor; ?>;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 20px; font-weight: bold;
        }
        .badge-active   { background: #d4edda; color: #155724; border-radius: 20px; padding: 4px 12px; font-size: 12px; }
        .badge-inactive { background: #f8d7da; color: #721c24; border-radius: 20px; padding: 4px 12px; font-size: 12px; }
        .wa-btn { background: #25D366; color: #fff; border: none; border-radius: 6px; padding: 5px 12px; font-size: 13px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .wa-btn:hover { background: #1ebe5d; color: #fff; }
        .form-section { background: #f8f9fa; border-radius: 12px; padding: 24px; }
    </style>
</head>
<body>
<div class="page">
    <?php include 'main_header.php'; ?>
    <div class="page-content d-flex align-items-stretch">
        <?php include 'school_sidebar.php'; ?>
        <div class="content-inner w-100">

            <header class="bg-white shadow-sm px-4 py-3">
                <h2 class="mb-0"><i class="fab fa-whatsapp me-2" style="color:#25D366;"></i>WhatsApp Support Agents</h2>
            </header>
            <div class="bg-white">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 py-3">
                            <li class="breadcrumb-item"><a href="s_dashboard">Home</a></li>
                            <li class="breadcrumb-item active">School Agents</li>
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

                    <!-- Add Agent Form -->
                    <div class="col-lg-4">
                        <div class="form-section">
                            <h5 class="mb-3"><i class="fas fa-user-plus me-2"></i>Add New Agent</h5>
                            <p class="text-muted small mb-3">Agents will be notified via WhatsApp and can support parent enquiries for <strong><?php echo htmlspecialchars($schoolName); ?></strong>.</p>
                            <form method="POST">
                                <input type="hidden" name="action" value="add">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="agent_name" class="form-control" placeholder="e.g. Thabo Nkosi" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold"><i class="fab fa-whatsapp text-success me-1"></i>WhatsApp Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="whatsapp_no" class="form-control" placeholder="e.g. 0731234567" required>
                                    <small class="text-muted">SA numbers auto-formatted (27xxxxxxxxx)</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Phone Number</label>
                                    <input type="tel" name="phone_no" class="form-control" placeholder="e.g. 0731234567">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email" name="agent_email" class="form-control" placeholder="agent@email.com">
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fab fa-whatsapp me-2"></i>Add Agent & Notify via WhatsApp
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Agents List -->
                    <div class="col-lg-8">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Agents for <?php echo htmlspecialchars($schoolName); ?></h5>
                            <span class="badge bg-primary"><?php echo count($agents); ?> agent<?php echo count($agents) !== 1 ? 's' : ''; ?></span>
                        </div>

                        <?php if (empty($agents)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fab fa-whatsapp fa-4x mb-3" style="color:#25D366; opacity:0.4;"></i>
                            <p>No agents added yet. Add your first agent to get started.</p>
                        </div>
                        <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($agents as $agent): ?>
                            <div class="col-12">
                                <div class="agent-card p-3 bg-white">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="agent-avatar">
                                                <?php echo strtoupper(substr($agent['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($agent['name']); ?></div>
                                                <div class="text-muted small">
                                                    <i class="fab fa-whatsapp text-success me-1"></i><?php echo htmlspecialchars($agent['whatsapp_no']); ?>
                                                    <?php if ($agent['email']): ?>
                                                    &nbsp;|&nbsp;<i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($agent['email']); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mt-1">
                                                    <?php if ($agent['is_active']): ?>
                                                    <span class="badge-active"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Active</span>
                                                    <?php else: ?>
                                                    <span class="badge-inactive"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Inactive</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="https://wa.me/<?php echo htmlspecialchars($agent['whatsapp_no']); ?>" target="_blank" class="wa-btn">
                                                <i class="fab fa-whatsapp"></i> Chat
                                            </a>
                                            <!-- Toggle active/inactive -->
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $agent['is_active'] ? 0 : 1; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $agent['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                                    <i class="fas <?php echo $agent['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                                    <?php echo $agent['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                            <!-- Delete -->
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Remove this agent?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
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
