<?php
/**
 * WhatsApp Chat Thread — View & Reply to a conversation
 */
include_once 'connection.php';

$email     = $_SESSION['socialWorkerEmail'] ?? '';
$role      = $_SESSION['role'] ?? '';
$school_id = $_SESSION['school_id'] ?? 0;
$agent_name = $_SESSION['user_name'] ?? 'Agent';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login'); exit();
}
if ($role !== 'user' && $role !== 'superUser') {
    header('Location: login'); exit();
}

$contactPhone = preg_replace('/[^0-9+]/', '', $_GET['phone'] ?? '');
if (empty($contactPhone)) {
    header('Location: wa_inbox'); exit();
}

// Mark all inbound messages from this contact as read
db_query($conn,
    "UPDATE whatsapp_chats SET is_read = 1 WHERE contact_phone = ? AND school_id = ? AND direction = 'inbound'",
    'si', [$contactPhone, $school_id]
);

// Fetch all messages in this conversation
$msgResult = db_query($conn,
    "SELECT * FROM whatsapp_chats WHERE contact_phone = ? AND school_id = ? ORDER BY created_at ASC",
    'si', [$contactPhone, $school_id]
);
$messages = [];
$contactName = $contactPhone;
if ($msgResult) {
    while ($row = $msgResult->fetch_assoc()) {
        $messages[] = $row;
        if (!empty($row['contact_name'])) $contactName = $row['contact_name'];
    }
}

// Try to find user name from users table
$userRow = db_query($conn,
    "SELECT CONCAT(name, ' ', surname) as full_name FROM users WHERE REPLACE(REPLACE(phone_no, ' ', ''), '-', '') LIKE ?",
    's', ['%' . substr($contactPhone, -9) . '%']
);
if ($userRow && $userRow->num_rows > 0) {
    $contactName = $userRow->fetch_assoc()['full_name'];
}

$themeColor = '#007bff';
$logoPath   = 'img/Find&R.png';
$sRow = db_query($conn, 'SELECT logo_path, theme_color FROM admin_settings WHERE email = ?', 's', [$email]);
if ($sRow && $sRow->num_rows > 0) {
    $s = $sRow->fetch_assoc();
    $themeColor = $s['theme_color'] ?? $themeColor;
    $logoPath   = $s['logo_path']   ?? $logoPath;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Chat with <?php echo h($contactName); ?> - WhatsApp Inbox</title>
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
        .chat-wrapper { display: flex; flex-direction: column; height: calc(100vh - 180px); }
        .chat-header {
            background: #25D366; color: white; padding: 14px 20px;
            display: flex; align-items: center; gap: 12px;
            border-radius: 10px 10px 0 0; flex-shrink: 0;
        }
        .chat-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(255,255,255,0.3); display: flex;
            align-items: center; justify-content: center; font-weight: 700;
        }
        .chat-messages {
            flex: 1; overflow-y: auto; padding: 20px;
            background: #ece5dd;
            display: flex; flex-direction: column; gap: 10px;
        }
        .msg-bubble {
            max-width: 65%; padding: 10px 14px;
            border-radius: 12px; font-size: 0.88rem;
            position: relative; word-wrap: break-word;
        }
        .msg-inbound {
            background: white; align-self: flex-start;
            border-radius: 0 12px 12px 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .msg-outbound {
            background: #d9fdd3; align-self: flex-end;
            border-radius: 12px 0 12px 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .msg-time { font-size: 0.68rem; color: #999; margin-top: 4px; text-align: right; }
        .msg-sender { font-size: 0.72rem; color: #25D366; font-weight: 600; margin-bottom: 3px; }
        .chat-input-area {
            background: #f0f0f0; padding: 12px 16px;
            display: flex; align-items: flex-end; gap: 10px;
            border-top: 1px solid #ddd; flex-shrink: 0;
            border-radius: 0 0 10px 10px;
        }
        .chat-textarea {
            flex: 1; border: none; border-radius: 20px;
            padding: 10px 16px; resize: none; max-height: 120px;
            font-size: 0.9rem; outline: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .send-btn {
            width: 44px; height: 44px; border-radius: 50%; border: none;
            background: #25D366; color: white; font-size: 1.1rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; flex-shrink: 0; transition: background 0.2s;
        }
        .send-btn:hover { background: #1ebe5d; }
        .send-btn:disabled { background: #ccc; cursor: not-allowed; }
        .chat-card { border-radius: 10px; overflow: hidden; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .notice-banner {
            background: #fff3cd; border-left: 4px solid #ffc107;
            padding: 10px 16px; font-size: 0.82rem; color: #856404;
        }
        .empty-chat { text-align: center; padding: 40px; color: #999; }
    </style>
</head>
<body>
<div class="page">
    <?php include 'main_header.php'; ?>
    <div class="page-content d-flex align-items-stretch">
        <?php include 'main_sidebar.php'; ?>
        <div class="content-inner w-100">
            <header class="bg-white shadow-sm px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="wa_inbox" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Inbox
                    </a>
                    <h2 class="mb-0 h5">
                        <i class="fab fa-whatsapp me-2" style="color:#25D366;"></i>
                        <?php echo h($contactName); ?>
                        <small class="text-muted ms-2" style="font-size:0.75rem;"><?php echo h($contactPhone); ?></small>
                    </h2>
                </div>
            </header>

            <div class="container-fluid py-3">
                <div class="chat-card">
                    <div class="chat-header">
                        <div class="chat-avatar"><?php echo strtoupper(substr($contactName, 0, 1)); ?></div>
                        <div>
                            <div class="fw-bold"><?php echo h($contactName); ?></div>
                            <div style="font-size:0.78rem; opacity:0.85;"><?php echo h($contactPhone); ?></div>
                        </div>
                        <div class="ms-auto">
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contactPhone); ?>"
                               target="_blank" class="btn btn-sm btn-light">
                                <i class="fab fa-whatsapp me-1"></i>Open in WhatsApp
                            </a>
                        </div>
                    </div>

                    <div class="notice-banner">
                        <i class="fas fa-info-circle me-1"></i>
                        Replies are sent from your WhatsApp Business number. The parent must have messaged first (within 24 hours) for free-form replies to deliver.
                    </div>

                    <div class="chat-messages" id="chatMessages">
                        <?php if (empty($messages)): ?>
                        <div class="empty-chat">
                            <i class="fab fa-whatsapp fa-3x mb-2" style="color:#e0e0e0;"></i>
                            <p>No messages yet in this conversation.</p>
                        </div>
                        <?php else: foreach ($messages as $msg): ?>
                        <div class="msg-bubble <?php echo $msg['direction'] === 'inbound' ? 'msg-inbound' : 'msg-outbound'; ?>">
                            <?php if ($msg['direction'] === 'outbound'): ?>
                            <div class="msg-sender"><?php echo h($agent_name); ?></div>
                            <?php endif; ?>
                            <?php echo nl2br(h($msg['message_text'])); ?>
                            <div class="msg-time"><?php echo date('d M H:i', strtotime($msg['created_at'])); ?></div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>

                    <div class="chat-input-area">
                        <textarea
                            id="replyText"
                            class="chat-textarea"
                            placeholder="Type a message..."
                            rows="1"
                            onkeydown="handleEnter(event)"
                        ></textarea>
                        <button class="send-btn" id="sendBtn" onclick="sendReply()" title="Send">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

                <!-- Error/success feedback -->
                <div id="sendFeedback" class="mt-2" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/front.js"></script>
<script>
const contactPhone = <?php echo json_encode($contactPhone); ?>;
const schoolId     = <?php echo json_encode($school_id); ?>;

// Track the latest message timestamp so the poll always advances forward
let lastMessageTime = <?php echo json_encode(
    !empty($messages) ? end($messages)['created_at'] : '2000-01-01 00:00:00'
); ?>;

// Scroll to bottom on load
window.addEventListener('load', function() {
    const msgs = document.getElementById('chatMessages');
    msgs.scrollTop = msgs.scrollHeight;
});

// Send on Enter (Shift+Enter for newline)
function handleEnter(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendReply();
    }
}

// Auto-resize textarea
document.getElementById('replyText').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

function sendReply() {
    const text = document.getElementById('replyText').value.trim();
    if (!text) return;

    const btn = document.getElementById('sendBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('wa_send_reply.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ phone: contactPhone, message: text, school_id: schoolId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Append the outbound message bubble
            const msgs = document.getElementById('chatMessages');
            const now  = new Date();
            const time = now.toLocaleDateString('en-GB', {day:'2-digit', month:'short'})
                       + ' ' + now.toTimeString().slice(0,5);

            // Build a UTC-style timestamp string to advance the poll anchor
            const pad = n => String(n).padStart(2, '0');
            lastMessageTime = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate())
                            + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());

            const bubble = document.createElement('div');
            bubble.className = 'msg-bubble msg-outbound';
            bubble.innerHTML = '<div class="msg-sender"><?php echo h($agent_name); ?></div>'
                             + escapeHtml(text).replace(/\n/g, '<br>')
                             + '<div class="msg-time">' + time + '</div>';
            msgs.appendChild(bubble);
            msgs.scrollTop = msgs.scrollHeight;
            document.getElementById('replyText').value = '';
            document.getElementById('replyText').style.height = 'auto';

            document.getElementById('sendFeedback').style.display = 'none';
        } else {
            showFeedback('danger', data.error || 'Failed to send message.');
        }
    })
    .catch(() => showFeedback('danger', 'Network error. Please try again.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    });
}

function showFeedback(type, msg) {
    const fb = document.getElementById('sendFeedback');
    fb.style.display = 'block';
    fb.innerHTML = '<div class="alert alert-' + type + ' py-2 mb-0">' + msg + '</div>';
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

// Poll for new inbound messages every 8 seconds, always using the latest known timestamp
setInterval(function() {
    fetch('wa_poll.php?phone=' + encodeURIComponent(contactPhone)
        + '&school_id=' + schoolId
        + '&after='     + encodeURIComponent(lastMessageTime))
    .then(r => r.json())
    .then(data => {
        if (data.messages && data.messages.length > 0) {
            const msgs = document.getElementById('chatMessages');
            data.messages.forEach(function(m) {
                const bubble = document.createElement('div');
                bubble.className = 'msg-bubble msg-inbound';
                bubble.innerHTML = m.message_text.replace(/\n/g, '<br>')
                                 + '<div class="msg-time">' + m.time + '</div>';
                msgs.appendChild(bubble);
            });
            msgs.scrollTop = msgs.scrollHeight;

            // Advance the anchor so we don't re-fetch the same messages
            if (data.last_time) {
                lastMessageTime = data.last_time;
            }
        }
    })
    .catch(() => {});
}, 8000);
</script>
<?php include 'pwa-footer.php'; ?>
</body>
</html>
