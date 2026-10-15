<?php
/**
 * Long-poll endpoint — returns new inbound messages since a given timestamp.
 * Called by wa_chat.php every 8 seconds via JS fetch.
 */
include_once 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['messages' => []]);
    exit();
}

$school_id = intval($_SESSION['school_id'] ?? 0);
$phone     = preg_replace('/[^0-9+]/', '', $_GET['phone'] ?? '');
$after     = $_GET['after'] ?? '2000-01-01 00:00:00';

// Validate timestamp format
if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $after)) {
    $after = '2000-01-01 00:00:00';
}

$result = db_query($conn,
    "SELECT id, message_text, direction, created_at FROM whatsapp_chats
     WHERE contact_phone = ? AND school_id = ? AND direction = 'inbound' AND created_at > ?
     ORDER BY created_at ASC LIMIT 20",
    'sis', [$phone, $school_id, $after]
);

$messages  = [];
$last_time = null;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Mark as read
        db_query($conn, "UPDATE whatsapp_chats SET is_read = 1 WHERE id = ?", 'i', [$row['id']]);
        $messages[] = [
            'message_text' => h($row['message_text']),
            'time'         => date('d M H:i', strtotime($row['created_at']))
        ];
        $last_time = $row['created_at'];
    }
}

$response = ['messages' => $messages];
if ($last_time !== null) {
    $response['last_time'] = $last_time;
}
echo json_encode($response);
