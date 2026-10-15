<?php
/**
 * WhatsApp Webhook Debug Log Viewer
 * Admin-only: shows the last N lines of wa_webhook_debug.log
 */
include_once 'connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login'); exit();
}
$role = $_SESSION['role'] ?? '';
if ($role !== 'user' && $role !== 'superUser') {
    header('Location: login'); exit();
}

$logFile = __DIR__ . '/wa_webhook_debug.log';
$lines   = 100;
$content = '';
$exists  = file_exists($logFile);

if ($exists) {
    $all     = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $content = implode("\n", array_slice($all, -$lines));
}

// Allow clearing the log
if ($_POST['action'] ?? '' === 'clear') {
    file_put_contents($logFile, '');
    header('Location: wa_debug_log'); exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>WA Webhook Debug Log</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #1e1e1e; color: #d4d4d4; font-family: 'Courier New', monospace; }
        .log-box { background: #111; padding: 20px; border-radius: 8px; white-space: pre-wrap; font-size: 0.8rem; line-height: 1.6; max-height: 80vh; overflow-y: auto; }
        .line-warn  { color: #ffc107; }
        .line-error { color: #f44336; }
        .line-saved { color: #4caf50; }
        .line-info  { color: #90caf9; }
    </style>
</head>
<body class="p-4">
    <div class="d-flex align-items-center gap-3 mb-3">
        <h4 class="text-white mb-0"><i class="fab fa-whatsapp me-2" style="color:#25D366;"></i>Webhook Debug Log</h4>
        <a href="wa_inbox" class="btn btn-sm btn-outline-light ms-auto">← Inbox</a>
        <form method="post" onsubmit="return confirm('Clear log?')">
            <input type="hidden" name="action" value="clear">
            <button class="btn btn-sm btn-outline-danger">Clear Log</button>
        </form>
        <button class="btn btn-sm btn-outline-info" onclick="location.reload()">Refresh</button>
    </div>

    <?php if (!$exists || empty($content)): ?>
    <div class="alert alert-warning">
        No log file found yet. The file <code>wa_webhook_debug.log</code> will be created the next time the webhook receives a POST from Meta.
    </div>
    <?php else: ?>
    <p class="text-muted small">Showing last <?php echo $lines; ?> lines. Newest at bottom.</p>
    <div class="log-box" id="logBox"><?php
        foreach (array_slice(explode("\n", $content), -$lines) as $line) {
            $escaped = htmlspecialchars($line);
            if (str_contains($line, 'WARNING') || str_contains($line, 'FAILED')) {
                echo "<span class='line-warn'>$escaped</span>\n";
            } elseif (str_contains($line, 'ERROR')) {
                echo "<span class='line-error'>$escaped</span>\n";
            } elseif (str_contains($line, 'SAVED')) {
                echo "<span class='line-saved'>$escaped</span>\n";
            } else {
                echo "<span class='line-info'>$escaped</span>\n";
            }
        }
    ?></div>
    <?php endif; ?>

    <script>
        // Auto-scroll to bottom
        const box = document.getElementById('logBox');
        if (box) box.scrollTop = box.scrollHeight;
        // Auto-refresh every 10s
        setTimeout(() => location.reload(), 10000);
    </script>
</body>
</html>
