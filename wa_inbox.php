<?php
/**
 * WhatsApp Inbox — School Admin Chat Dashboard
 * Shows all conversations received via the business WhatsApp number.
 */
include_once 'connection.php';

$email    = $_SESSION['socialWorkerEmail'] ?? '';
$role     = $_SESSION['role'] ?? '';
$school_id = $_SESSION['school_id'] ?? 0;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login'); exit();
}
if ($role !== 'user' && $role !== 'superUser') {
    header('Location: login'); exit();
}

// Auto-create the whatsapp_chats table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS whatsapp_chats (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    school_id       INT NOT NULL DEFAULT 0,
    contact_phone   VARCHAR(30) NOT NULL,
    contact_name    VARCHAR(255) DEFAULT '',
    direction       ENUM('inbound','outbound') NOT NULL,
    message_text    TEXT NOT NULL,
    wa_message_id   VARCHAR(255) DEFAULT NULL,
    enquiry_id      INT DEFAULT NULL,
    agent_id        INT DEFAULT NULL,
    is_read         TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (contact_phone),
    INDEX idx_school (school_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$themeColor = '#007bff';
$logoPath   = 'img/Find&R.png';
$sRow = db_query($conn, 'SELECT logo_path, theme_color FROM admin_settings WHERE email = ?', 's', [$email]);
if ($sRow && $sRow->num_rows > 0) {
    $s = $sRow->fetch_assoc();
    $themeColor = $s['theme_color'] ?? $themeColor;
    $logoPath   = $s['logo_path']   ?? $logoPath;
}

// Fetch all unique conversations for this school, ordered by latest message
$convSql = "
    SELECT
        wc.contact_phone,
        wc.contact_name,
        MAX(wc.created_at)   AS last_message_time,
        SUM(wc.is_read = 0 AND wc.direction = 'inbound') AS unread_count,
        (SELECT message_text FROM whatsapp_chats
         WHERE contact_phone = wc.contact_phone AND school_id = ?
         ORDER BY created_at DESC LIMIT 1) AS last_message
    FROM whatsapp_chats wc
    WHERE wc.school_id = ?
    GROUP BY wc.contact_phone, wc.contact_name
    ORDER BY last_message_time DESC
";
$convResult = db_query($conn, $convSql, 'ii', [$school_id, $school_id]);
$conversations = [];
if ($convResult) {
    while ($row = $convResult->fetch_assoc()) {
        $conversations[] = $row;
    }
}

$totalUnread = array_sum(array_column($conversations, 'unread_count'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>WhatsApp Inbox - Find and Return</title>
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
        .inbox-container { max-width: 100%; }
        .conv-list { list-style: none; padding: 0; margin: 0; }
        .conv-item {
            display: flex; align-items: center; padding: 14px 16px;
            border-bottom: 1px solid #f0f0f0; cursor: pointer;
            transition: background 0.15s; text-decoration: none; color: inherit;
        }
        .conv-item:hover { background: #f8f9fa; }
        .conv-avatar {
            width: 46px; height: 46px; border-radius: 50%;
            background: #25D366; color: white; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0; margin-right: 14px;
        }
        .conv-body { flex: 1; min-width: 0; }
        .conv-phone { font-weight: 600; font-size: 0.95rem; margin-bottom: 2px; }
        .conv-name  { font-size: 0.8rem; color: #888; margin-bottom: 2px; }
        .conv-preview { font-size: 0.82rem; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conv-meta { text-align: right; flex-shrink: 0; margin-left: 10px; }
        .conv-time  { font-size: 0.75rem; color: #aaa; margin-bottom: 4px; }
        .unread-badge {
            background: #25D366; color: white; border-radius: 50%;
            width: 20px; height: 20px; font-size: 0.7rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            margin-left: auto;
        }
        .inbox-header {
            background: #25D366; color: white;
            padding: 16px 20px; border-radius: 10px 10px 0 0;
            display: flex; align-items: center; gap: 10px;
        }
        .inbox-card { border-radius: 10px; overflow: hidden; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .empty-inbox { text-align: center; padding: 60px 20px; color: #999; }
    </style>
</head>
<body>
<div class="page">
    <?php include 'main_header.php'; ?>
    <div class="page-content d-flex align-items-stretch">
        <?php include 'main_sidebar.php'; ?>
        <div class="content-inner w-100">
            <header class="bg-white shadow-sm px-4 py-3">
                <h2 class="mb-0">
                    <i class="fab fa-whatsapp me-2" style="color:#25D366;"></i>WhatsApp Inbox
                    <?php if ($totalUnread > 0): ?>
                        <span class="badge bg-success ms-2"><?php echo $totalUnread; ?> unread</span>
                    <?php endif; ?>
                </h2>
            </header>
            <div class="bg-white"><div class="container-fluid">
                <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 py-3">
                    <li class="breadcrumb-item"><a href="user_dashboard">Home</a></li>
                    <li class="breadcrumb-item active">WhatsApp Inbox</li>
                </ol></nav>
            </div></div>

            <div class="container-fluid py-4">
                <div class="inbox-container">
                    <div class="inbox-card">
                        <div class="inbox-header">
                            <i class="fab fa-whatsapp fa-lg"></i>
                            <span class="fw-bold">Conversations</span>
                            <span class="ms-auto small opacity-75"><?php echo count($conversations); ?> conversation(s)</span>
                        </div>

                        <?php if (empty($conversations)): ?>
                        <div class="empty-inbox">
                            <i class="fab fa-whatsapp fa-4x mb-3" style="color:#e0e0e0;"></i>
                            <p class="mb-1 fw-bold">No messages yet</p>
                            <small>When parents reply to your WhatsApp messages, their conversations will appear here.</small>
                        </div>
                        <?php else: ?>
                        <ul class="conv-list bg-white">
                            <?php foreach ($conversations as $conv): ?>
                            <li>
                                <a class="conv-item" href="wa_chat?phone=<?php echo urlencode($conv['contact_phone']); ?>">
                                    <div class="conv-avatar">
                                        <?php echo strtoupper(substr($conv['contact_phone'], -2)); ?>
                                    </div>
                                    <div class="conv-body">
                                        <div class="conv-phone"><?php echo h($conv['contact_phone']); ?></div>
                                        <?php if (!empty($conv['contact_name'])): ?>
                                        <div class="conv-name"><?php echo h($conv['contact_name']); ?></div>
                                        <?php endif; ?>
                                        <div class="conv-preview"><?php echo h($conv['last_message']); ?></div>
                                    </div>
                                    <div class="conv-meta">
                                        <div class="conv-time"><?php echo date('d M H:i', strtotime($conv['last_message_time'])); ?></div>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                        <div class="d-flex justify-content-end">
                                            <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
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
