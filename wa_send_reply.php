<?php
/**
 * AJAX endpoint — send a WhatsApp reply from the inbox chat view
 * and store it in whatsapp_chats.
 */
include_once 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit();
}

$role       = $_SESSION['role']      ?? '';
$school_id  = intval($_SESSION['school_id'] ?? 0);
$agent_id   = intval($_SESSION['user_id']   ?? 0);
$agent_name = $_SESSION['user_name'] ?? 'Agent';

if ($role !== 'user' && $role !== 'superUser') {
    echo json_encode(['success' => false, 'error' => 'Unauthorised.']);
    exit();
}

$phone   = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($phone) || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Phone and message are required.']);
    exit();
}

// Normalise SA number: 0xx → 27xx
if (strlen($phone) === 10 && strpos($phone, '0') === 0) {
    $phone = '27' . substr($phone, 1);
}
if (strlen($phone) < 10) {
    echo json_encode(['success' => false, 'error' => 'Invalid phone number.']);
    exit();
}

// ── Resolve enquiry_id + subject + parent name for this conversation ────
$last9           = substr($phone, -9);
$enquiry_id      = 0;
$enquiry_subject = '';
$parent_name     = '';

$chatRow = db_query($conn,
    "SELECT wc.enquiry_id, pe.subject, u.name AS parent_name
     FROM whatsapp_chats wc
     LEFT JOIN parent_enquiries pe ON pe.id = wc.enquiry_id
     LEFT JOIN users u ON pe.parent_id = u.id
     WHERE RIGHT(REPLACE(wc.contact_phone, '+', ''), 9) = ?
       AND wc.school_id = ?
       AND wc.enquiry_id IS NOT NULL AND wc.enquiry_id > 0
     ORDER BY wc.created_at DESC LIMIT 1",
    'si', [$last9, $school_id]
);
if ($chatRow && $chatRow->num_rows > 0) {
    $r               = $chatRow->fetch_assoc();
    $enquiry_id      = intval($r['enquiry_id']);
    $enquiry_subject = $r['subject']      ?? '';
    $parent_name     = $r['parent_name']  ?? '';
}

// Fallback: find the active assigned enquiry for this parent phone directly
if ($enquiry_id === 0) {
    $enqRow = db_query($conn,
        "SELECT pe.id, pe.subject, u.name AS parent_name
         FROM parent_enquiries pe
         LEFT JOIN users u ON pe.parent_id = u.id
         WHERE pe.school_id = ?
           AND pe.status = 'assigned'
           AND RIGHT(REPLACE(REPLACE(REPLACE(u.phone_no,' ',''),'-',''),'+',''), 9) = ?
         ORDER BY pe.created_at DESC LIMIT 1",
        'is', [$school_id, $last9]
    );
    if ($enqRow && $enqRow->num_rows > 0) {
        $r               = $enqRow->fetch_assoc();
        $enquiry_id      = intval($r['id']);
        $enquiry_subject = $r['subject']     ?? '';
        $parent_name     = $r['parent_name'] ?? '';
    }
}
// ─────────────────────────────────────────────────────────────────────────

// ── Send the message to the parent ──────────────────────────────────────
// Strategy 1: free-form text — works if parent messaged us within last 24 h
// Strategy 2: approved template — works anytime (outside 24 h window)
global $last_whatsapp_error;

$parentDisplay  = !empty($parent_name)     ? $parent_name     : 'there';
$subjectDisplay = !empty($enquiry_subject) ? $enquiry_subject : 'your enquiry';
$preview        = mb_strlen($message) > 100 ? mb_substr($message, 0, 97) . '...' : $message;

// Check if parent messaged us within the last 24 hours (service window open)
$windowOpen = false;
$windowRow  = db_query($conn,
    "SELECT id FROM whatsapp_chats
     WHERE RIGHT(REPLACE(contact_phone, '+', ''), 9) = ?
       AND direction = 'inbound'
       AND created_at >= NOW() - INTERVAL 24 HOUR
     LIMIT 1",
    's', [substr($phone, -9)]
);
if ($windowRow && $windowRow->num_rows > 0) {
    $windowOpen = true;
}

$sent = false;
if ($windowOpen) {
    // Free-form: parent will receive the agent's exact words
    $sent = send_whatsapp($phone, $message);
    error_log("wa_send_reply: free-form to $phone (window open): " . ($sent ? 'SENT' : 'FAILED — ' . ($last_whatsapp_error ?? 'unknown')));
}

if (!$sent) {
    // Template fallback: wraps the message so it delivers outside the 24 h window
    // Template: "Hello {{1}}! You have a new message from your agent {{2}}
    //            regarding "{{3}}": {{4}}"
    $sent = send_whatsapp(
        $phone, '',
        true, 'agent_message_parent',
        [$parentDisplay, $agent_name, $subjectDisplay, $preview],
        'en_US'
    );
    error_log("wa_send_reply: agent_message_parent template to $phone: " . ($sent ? 'SENT' : 'FAILED — ' . ($last_whatsapp_error ?? 'unknown')));
}

if (!$sent) {
    echo json_encode(['success' => false, 'error' => $last_whatsapp_error ?: 'WhatsApp API send failed.']);
    exit();
}
// ─────────────────────────────────────────────────────────────────────────

// Save outbound message to whatsapp_chats, linked to enquiry if known
if ($enquiry_id > 0) {
    db_query($conn,
        "INSERT INTO whatsapp_chats
            (school_id, contact_phone, direction, message_text, agent_id, enquiry_id, is_read)
         VALUES (?, ?, 'outbound', ?, ?, ?, 1)",
        'isiii', [$school_id, $phone, $message, $agent_id, $enquiry_id]
    );
} else {
    db_query($conn,
        "INSERT INTO whatsapp_chats
            (school_id, contact_phone, direction, message_text, agent_id, is_read)
         VALUES (?, ?, 'outbound', ?, ?, 1)",
        'isis', [$school_id, $phone, $message, $agent_id]
    );
}

error_log("wa_send_reply: outbound saved — school=$school_id, phone=$phone, agent=$agent_id, enquiry=$enquiry_id");

echo json_encode(['success' => true]);
