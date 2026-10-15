<?php
include_once 'connection.php';

$email     = $_SESSION['socialWorkerEmail'] ?? '';
$role      = $_SESSION['role'] ?? '';
$school_id = $_SESSION['school_id'] ?? 0;

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login"); exit();
}
if ($role !== 'user' && $role !== 'superUser') {
    header("Location: login"); exit();
}

// Auto-create tables if needed
$conn->query("CREATE TABLE IF NOT EXISTS parent_enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY, parent_id INT NOT NULL, school_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL, message TEXT NOT NULL,
    status ENUM('pending','assigned','resolved') DEFAULT 'pending',
    assigned_agent_id INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_agent_id) REFERENCES school_agents(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$success = ''; $error = '';

// Assign agent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'assign') {
        $enq_id    = intval($_POST['enquiry_id'] ?? 0);
        $agent_id  = intval($_POST['agent_id'] ?? 0);

        // Get agent WhatsApp number
        $agentRow  = db_query($conn, "SELECT * FROM school_agents WHERE id = ? AND school_id = ?", "ii", [$agent_id, $school_id]);
        $enquiryRow = db_query($conn, "SELECT pe.*, u.name as parent_name, u.phone_no as parent_phone FROM parent_enquiries pe LEFT JOIN users u ON pe.parent_id = u.id WHERE pe.id = ? AND pe.school_id = ?", "ii", [$enq_id, $school_id]);

        if ($agentRow && $agentRow->num_rows > 0 && $enquiryRow && $enquiryRow->num_rows > 0) {
            $agent   = $agentRow->fetch_assoc();
            $enquiry = $enquiryRow->fetch_assoc();
            global $last_whatsapp_error;

            db_query($conn,
                "UPDATE parent_enquiries SET assigned_agent_id = ?, status = 'assigned' WHERE id = ? AND school_id = ?",
                "iii", [$agent_id, $enq_id, $school_id]
            );

            // Notify agent via WhatsApp using template
            // Template: "Hello {{1}}! A new enquiry has been assigned to you on Find and Return.
            //            Parent: {{2}}. Subject: {{3}}. Please contact the parent via WhatsApp: {{4}}"
            $agentPhone = preg_replace('/[^0-9]/', '', $agent['whatsapp_no']);
            if (strlen($agentPhone) === 10 && strpos($agentPhone, '0') === 0) {
                $agentPhone = '27' . substr($agentPhone, 1);
            }
            $agentWaSent = send_whatsapp($agentPhone, '', true, 'enquiry_assigned_agent', [
                $agent['name'],
                $enquiry['parent_name'],
                $enquiry['subject'],
                'just reply to this message to respond to the parent'
            ], 'en_US');
            error_log("Manage Enquiries - Assign Agent WhatsApp to agent: number={$agentPhone}, sent=" . ($agentWaSent ? 'YES' : 'NO') . ", error=" . ($last_whatsapp_error ?? 'none'));

            // Notify parent via WhatsApp using template
            // Template: "Hello {{1}}! Your enquiry "{{2}}" has been assigned to {{3}}.
            //            They will contact you shortly via WhatsApp."
            if (!empty($enquiry['parent_phone'])) {
                $parentPhone = preg_replace('/[^0-9]/', '', $enquiry['parent_phone']);
                if (strlen($parentPhone) === 10 && strpos($parentPhone, '0') === 0) {
                    $parentPhone = '27' . substr($parentPhone, 1);
                }
                $parentWaSent = send_whatsapp($parentPhone, '', true, 'enquiry_assigned_parent', [
                    $enquiry['parent_name'],
                    $enquiry['subject'],
                    $agent['name']
                ], 'en_US');
                error_log("Manage Enquiries - Notify Parent WhatsApp: number={$parentPhone}, sent=" . ($parentWaSent ? 'YES' : 'NO') . ", error=" . ($last_whatsapp_error ?? 'none'));

                // Record the outbound template message in whatsapp_chats so the
                // parent's reply can be matched to this school when it arrives via webhook.
                $templatePreview = "Enquiry assigned: \"{$enquiry['subject']}\" — Agent: {$agent['name']}";
                $parentName = $enquiry['parent_name'] ?? '';
                db_query($conn,
                    "INSERT INTO whatsapp_chats
                        (school_id, contact_phone, contact_name, direction, message_text, enquiry_id, agent_id, is_read)
                     VALUES (?, ?, ?, 'outbound', ?, ?, ?, 1)
                     ON DUPLICATE KEY UPDATE id=id",
                    'isssii',
                    [$school_id, $parentPhone, $parentName, $templatePreview, $enq_id, $agent_id]
                );
            }

            $success = "Enquiry assigned to {$agent['name']} — both parties notified via WhatsApp.";
        } else {
            $error = 'Invalid agent or enquiry.';
        }
    }

    if ($_POST['action'] === 'resolve') {
        $enq_id = intval($_POST['enquiry_id'] ?? 0);
        db_query($conn,
            "UPDATE parent_enquiries SET status = 'resolved', resolved_at = NOW() WHERE id = ? AND school_id = ?",
            "ii", [$enq_id, $school_id]
        );
        $success = 'Enquiry marked as resolved.';
    }
}

// Fetch enquiries
$enquiries = [];
$enqRes = db_query($conn,
    "SELECT pe.*, u.name as parent_name, u.phone_no as parent_phone, u.email as parent_email,
            sa.name as agent_name, sa.whatsapp_no as agent_wa
     FROM parent_enquiries pe
     LEFT JOIN users u ON pe.parent_id = u.id
     LEFT JOIN school_agents sa ON pe.assigned_agent_id = sa.id
     WHERE pe.school_id = ?
     ORDER BY FIELD(pe.status,'pending','assigned','resolved'), pe.created_at DESC",
    "i", [$school_id]
);
if ($enqRes && $enqRes->num_rows > 0) {
    while ($row = $enqRes->fetch_assoc()) $enquiries[] = $row;
}

// Fetch active agents for this school
$agents = [];
$agentRes = db_query($conn, "SELECT * FROM school_agents WHERE school_id = ? AND is_active = 1", "i", [$school_id]);
if ($agentRes && $agentRes->num_rows > 0) {
    while ($row = $agentRes->fetch_assoc()) $agents[] = $row;
}

$themeColor = "#007bff";
$logoPath   = "img/Find&R.png";
$sRow = db_query($conn, "SELECT logo_path, theme_color FROM admin_settings WHERE email = ?", "s", [$email]);
if ($sRow && $sRow->num_rows > 0) { $s = $sRow->fetch_assoc(); $themeColor = $s['theme_color'] ?? $themeColor; $logoPath = $s['logo_path'] ?? $logoPath; }

$pending  = array_filter($enquiries, fn($e) => $e['status'] === 'pending');
$assigned = array_filter($enquiries, fn($e) => $e['status'] === 'assigned');
$resolved = array_filter($enquiries, fn($e) => $e['status'] === 'resolved');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Enquiries - Find and Return</title>
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
        .enq-card { border-radius: 10px; border: 1px solid #e0e0e0; margin-bottom: 12px; padding: 16px; background: #fff; }
        .enq-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .status-badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-pending   { background: #fff3cd; color: #856404; }
        .status-assigned  { background: #d1ecf1; color: #0c5460; }
        .status-resolved  { background: #d4edda; color: #155724; }
        .wa-btn { background: #25D366; color: #fff; padding: 4px 10px; border-radius: 5px; font-size: 12px; text-decoration: none; }
        .wa-btn:hover { background: #1ebe5d; color: #fff; }
        .stat-box { border-radius: 10px; padding: 16px; text-align: center; }
    </style>
</head>
<body>
<div class="page">
    <?php include 'main_header.php'; ?>
    <div class="page-content d-flex align-items-stretch">
        <?php include 'main_sidebar.php'; ?>
        <div class="content-inner w-100">
            <header class="bg-white shadow-sm px-4 py-3">
                <h2 class="mb-0"><i class="fas fa-headset me-2"></i>Manage Enquiries</h2>
            </header>
            <div class="bg-white"><div class="container-fluid">
                <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 py-3">
                    <li class="breadcrumb-item"><a href="user_dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Manage Enquiries</li>
                </ol></nav>
            </div></div>

            <div class="container-fluid py-4">
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-4"><div class="stat-box" style="background:#fff3cd;"><div class="h3 mb-0 fw-bold"><?php echo count($pending); ?></div><small class="text-muted">Pending</small></div></div>
                    <div class="col-4"><div class="stat-box" style="background:#d1ecf1;"><div class="h3 mb-0 fw-bold"><?php echo count($assigned); ?></div><small class="text-muted">Assigned</small></div></div>
                    <div class="col-4"><div class="stat-box" style="background:#d4edda;"><div class="h3 mb-0 fw-bold"><?php echo count($resolved); ?></div><small class="text-muted">Resolved</small></div></div>
                </div>

                <?php if (empty($agents)): ?>
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No active agents found. <a href="school_agents">Add agents first</a> before assigning enquiries.</div>
                <?php endif; ?>

                <?php if (empty($enquiries)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-4x mb-3" style="opacity:0.3;"></i>
                    <p>No enquiries yet from parents.</p>
                </div>
                <?php else: ?>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="enqTabs">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#pending">Pending <span class="badge bg-warning text-dark"><?php echo count($pending); ?></span></a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#assigned">Assigned <span class="badge bg-info"><?php echo count($assigned); ?></span></a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#resolved">Resolved <span class="badge bg-success"><?php echo count($resolved); ?></span></a></li>
                </ul>
                <div class="tab-content">

                    <!-- PENDING -->
                    <div class="tab-pane fade show active" id="pending">
                        <?php if (empty($pending)): ?>
                        <p class="text-muted text-center py-4">No pending enquiries.</p>
                        <?php else: foreach ($pending as $enq): ?>
                        <div class="enq-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($enq['subject'] ?? ''); ?></h6>
                                    <small class="text-muted">From: <?php echo htmlspecialchars($enq['parent_name'] ?? ''); ?> &bull; <?php echo date('M d, Y H:i', strtotime($enq['created_at'])); ?></small>
                                </div>
                                <span class="status-badge status-pending">Pending</span>
                            </div>
                            <p class="text-muted mb-3 small"><?php echo nl2br(htmlspecialchars($enq['message'] ?? '')); ?></p>
                            <?php if (!empty($enq['parent_phone'])): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $enq['parent_phone']); ?>" target="_blank" class="wa-btn me-2"><i class="fab fa-whatsapp me-1"></i>WhatsApp Parent</a>
                            <?php endif; ?>
                            <?php if (!empty($agents)): ?>
                            <form method="POST" class="d-inline-flex align-items-center gap-2 mt-2">
                                <input type="hidden" name="action" value="assign">
                                <input type="hidden" name="enquiry_id" value="<?php echo $enq['id']; ?>">
                                <select name="agent_id" class="form-select form-select-sm" style="width:auto;" required>
                                    <option value="">Assign agent...</option>
                                    <?php foreach ($agents as $agent): ?>
                                    <option value="<?php echo $agent['id']; ?>"><?php echo htmlspecialchars($agent['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-user-check me-1"></i>Assign</button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>

                    <!-- ASSIGNED -->
                    <div class="tab-pane fade" id="assigned">
                        <?php if (empty($assigned)): ?>
                        <p class="text-muted text-center py-4">No assigned enquiries.</p>
                        <?php else: foreach ($assigned as $enq): ?>
                        <div class="enq-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($enq['subject'] ?? ''); ?></h6>
                                    <small class="text-muted">From: <?php echo htmlspecialchars($enq['parent_name'] ?? ''); ?> &bull; <?php echo date('M d, Y H:i', strtotime($enq['created_at'])); ?></small>
                                </div>
                                <span class="status-badge status-assigned">Assigned</span>
                            </div>
                            <p class="text-muted mb-2 small"><?php echo nl2br(htmlspecialchars($enq['message'] ?? '')); ?></p>
                            <small class="text-info d-block mb-2"><i class="fas fa-user me-1"></i>Agent: <?php echo htmlspecialchars($enq['agent_name'] ?? ''); ?></small>
                            <a href="https://wa.me/<?php echo htmlspecialchars($enq['agent_wa'] ?? ''); ?>" target="_blank" class="wa-btn me-2"><i class="fab fa-whatsapp me-1"></i>Agent</a>
                            <?php if (!empty($enq['parent_phone'])): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $enq['parent_phone']); ?>" target="_blank" class="wa-btn me-2"><i class="fab fa-whatsapp me-1"></i>Parent</a>
                            <?php endif; ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="resolve">
                                <input type="hidden" name="enquiry_id" value="<?php echo $enq['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check-double me-1"></i>Mark Resolved</button>
                            </form>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>

                    <!-- RESOLVED -->
                    <div class="tab-pane fade" id="resolved">
                        <?php if (empty($resolved)): ?>
                        <p class="text-muted text-center py-4">No resolved enquiries.</p>
                        <?php else: foreach ($resolved as $enq): ?>
                        <div class="enq-card" style="opacity:0.8;">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($enq['subject'] ?? ''); ?></h6>
                                    <small class="text-muted">From: <?php echo htmlspecialchars($enq['parent_name'] ?? ''); ?> &bull; <?php echo date('M d, Y', strtotime($enq['created_at'])); ?></small>
                                </div>
                                <span class="status-badge status-resolved">Resolved</span>
                            </div>
                            <?php if ($enq['agent_name']): ?><small class="text-muted">Agent: <?php echo htmlspecialchars($enq['agent_name'] ?? ''); ?></small><?php endif; ?>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>

                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/front.js"></script>
<?php include 'pwa-footer.php'; ?>
</body>
</html>
