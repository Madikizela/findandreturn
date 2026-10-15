<?php
include('connection.php');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: announcement');
    exit();
}

$school_id   = intval($_SESSION['school_id'] ?? 0);
$school      = trim($_POST['school']       ?? $_SESSION['school'] ?? '');

// If school name is empty, look it up from school_id
if (empty($school) && $school_id > 0) {
    $sr = db_query($conn, "SELECT school_name FROM schools WHERE id = ?", "i", [$school_id]);
    if ($sr && $sr->num_rows > 0) {
        $school = $sr->fetch_assoc()['school_name'];
        $_SESSION['school'] = $school;
    }
}
$title       = trim($_POST['title']        ?? '');
$content     = trim($_POST['content']      ?? '');
$author      = trim($_POST['author']       ?? $_SESSION['user_name'] ?? '');
$expiry_date = trim($_POST['expiry_date']  ?? '');

if (empty($title) || empty($content)) {
    header('Location: announcement?error=missing');
    exit();
}

// Save announcement
$sql  = "INSERT INTO announcements (school, title, content, author, currentDate, expiry_date) VALUES (?, ?, ?, ?, NOW(), ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("process_announcement prepare failed: " . $conn->error);
    header('Location: announcement?error=db');
    exit();
}
$stmt->bind_param("sssss", $school, $title, $content, $author, $expiry_date);
if (!$stmt->execute()) {
    error_log("process_announcement execute failed: " . $stmt->error);
    $stmt->close();
    header('Location: announcement?error=db');
    exit();
}
$stmt->close();

// ── Notify all parents at this school via email + WhatsApp ────────────
if ($school_id > 0) {
    $parents = db_query($conn,
        "SELECT DISTINCT u.name, u.email, u.phone_no
         FROM users u
         WHERE u.school_id = ? AND u.role = 'parent'",
        "i", [$school_id]
    );

    if ($parents && $parents->num_rows > 0) {
        $preview = mb_strlen($content) > 100 ? mb_substr($content, 0, 97) . '...' : $content;

        while ($parent = $parents->fetch_assoc()) {
            // Email
            $htmlContent = '
            <div style="max-width:600px;margin:0 auto;padding:20px;font-family:Arial,sans-serif;">
                <h2 style="color:#007bff;">📢 ' . htmlspecialchars($school) . ' Announcement</h2>
                <h3>' . htmlspecialchars($title) . '</h3>
                <div style="background:#f4f4f4;padding:15px;border-radius:8px;margin:20px 0;">
                    <p>' . nl2br(htmlspecialchars($content)) . '</p>
                </div>
                ' . (!empty($expiry_date) ? '<p><strong>Valid until:</strong> ' . htmlspecialchars($expiry_date) . '</p>' : '') . '
                <p>Kind Regards,<br><strong>' . htmlspecialchars($school) . '</strong></p>
            </div>';
            send_email($parent['email'], "📢 " . $school . ": " . $title, $htmlContent);

            // WhatsApp — free-form for now, will use template once approved
            $wa_phone = preg_replace('/[^0-9]/', '', $parent['phone_no'] ?? '');
            if (!empty($wa_phone)) {
                if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
                    $wa_phone = '27' . substr($wa_phone, 1);
                }
                $waMsg = "📢 Announcement from $school\n\n*$title*\n\n$preview";
                send_whatsapp($wa_phone, $waMsg);
            }
        }
    }
}
// ─────────────────────────────────────────────────────────────────────

header('Location: announcement?success=1');
exit();
