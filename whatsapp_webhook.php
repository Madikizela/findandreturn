<?php
/**
 * WhatsApp Webhook Endpoint
 * Handles Meta's verification requests and incoming message notifications.
 */

// Set headers before any includes to prevent "headers already sent" errors
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Security-Policy: frame-ancestors *;");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include('connection.php');

// ── Debug logger (writes to a file so you can tail it on the live server) ──
function wa_log(string $msg): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    error_log($line);
    // Also write to a dedicated file in the project root for easy live-server inspection
    @file_put_contents(__DIR__ . '/wa_webhook_debug.log', $line, FILE_APPEND | LOCK_EX);
}

// 1. Handle Verification (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $verifyToken = $_ENV['WHATSAPP_WEBHOOK_VERIFY_TOKEN'] ?? getenv('WHATSAPP_WEBHOOK_VERIFY_TOKEN');

    $mode      = $_GET['hub_mode']         ?? '';
    $token     = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge']    ?? '';

    if ($mode && $token) {
        if ($mode === 'subscribe' && $token === $verifyToken) {
            wa_log("Verification successful.");
            echo $challenge;
            exit;
        } else {
            wa_log("Verification FAILED. Mode=$mode, token received='$token'");
            http_response_code(403);
            echo "Verification failed";
            exit;
        }
    }
    echo "WhatsApp Webhook Endpoint is active.";
    exit;
}

// 2. Handle Notifications (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = file_get_contents('php://input');
    $data    = json_decode($payload, true);

    wa_log("POST received. Raw payload: " . substr($payload, 0, 500));

    if (!is_array($data)) {
        wa_log("ERROR: payload is not valid JSON.");
        http_response_code(200);
        echo "EVENT_RECEIVED";
        exit;
    }

    $value = $data['entry'][0]['changes'][0]['value'] ?? null;
    if (!$value) {
        wa_log("No value in payload — ignoring.");
        http_response_code(200);
        echo "EVENT_RECEIVED";
        exit;
    }

    // Status updates (delivered/read receipts) — acknowledge and skip
    if (isset($value['statuses']) && !isset($value['messages'])) {
        wa_log("Status update received (no messages) — skipping.");
        http_response_code(200);
        echo "EVENT_RECEIVED";
        exit;
    }

    if (!isset($value['messages'][0])) {
        wa_log("No messages array in value — ignoring. Keys: " . implode(', ', array_keys($value)));
        http_response_code(200);
        echo "EVENT_RECEIVED";
        exit;
    }

    $message = $value['messages'][0];
    $from    = $message['from'] ?? '';        // e.g. "27680043106"
    $wa_id   = $message['id']   ?? null;
    $type    = $message['type'] ?? 'unknown';

    // Extract text based on message type
    switch ($type) {
        case 'text':
            $text = $message['text']['body'] ?? '';
            break;
        case 'image':
            $text = '[Image: ' . ($message['image']['caption'] ?? 'no caption') . ']';
            break;
        case 'audio':
            $text = '[Voice message]';
            break;
        case 'video':
            $text = '[Video: ' . ($message['video']['caption'] ?? 'no caption') . ']';
            break;
        case 'document':
            $text = '[Document: ' . ($message['document']['filename'] ?? 'file') . ']';
            break;
        case 'location':
            $lat = $message['location']['latitude']  ?? '';
            $lon = $message['location']['longitude'] ?? '';
            $text = "[Location: $lat, $lon]";
            break;
        case 'button':
            $text = $message['button']['text'] ?? '[Button reply]';
            break;
        case 'interactive':
            $text = $message['interactive']['button_reply']['title']
                 ?? $message['interactive']['list_reply']['title']
                 ?? '[Interactive reply]';
            break;
        default:
            $text = "[Unsupported message type: $type]";
    }

    // Get sender display name from contacts array
    $contacts    = $value['contacts'] ?? [];
    $contactName = '';
    foreach ($contacts as $c) {
        if (($c['wa_id'] ?? '') === $from) {
            $contactName = $c['profile']['name'] ?? '';
            break;
        }
    }

    wa_log("Inbound message | from=$from | name='$contactName' | type=$type | wa_id=$wa_id | text=" . substr($text, 0, 100));

    if (empty($from)) {
        wa_log("ERROR: 'from' is empty — cannot save message.");
        http_response_code(200);
        echo "EVENT_RECEIVED";
        exit;
    }

    // ── Check if sender is a known agent ────────────────────────────────────
    // Agents reply directly to the notification WhatsApp — we intercept here,
    // forward the message to the parent, and save it as an outbound record
    // so the full conversation stays in the system.
    $fromLast9  = substr(preg_replace('/[^0-9]/', '', $from), -9);
    wa_log("Agent check: from=$from, fromLast9=$fromLast9");
    $agentCheck = db_query($conn,
        "SELECT id, name, school_id, whatsapp_no FROM school_agents
         WHERE RIGHT(REPLACE(REPLACE(REPLACE(whatsapp_no, ' ', ''), '-', ''), '+', ''), 9) = ?
           AND is_active = 1
         LIMIT 1",
        's', [$fromLast9]
    );
    wa_log("Agent check result: " . ($agentCheck ? $agentCheck->num_rows . " row(s)" : "query failed"));

    // If no match, log all active agent numbers so we can see the format mismatch
    if (!$agentCheck || $agentCheck->num_rows === 0) {
        $allAgents = db_query($conn, "SELECT id, name, whatsapp_no FROM school_agents WHERE is_active = 1", null, []);
        if ($allAgents && $allAgents->num_rows > 0) {
            while ($a = $allAgents->fetch_assoc()) {
                $stored9 = substr(preg_replace('/[^0-9]/', '', $a['whatsapp_no']), -9);
                wa_log("  Active agent id={$a['id']} name='{$a['name']}' whatsapp_no='{$a['whatsapp_no']}' last9=$stored9");
            }
        } else {
            wa_log("  No active agents found in school_agents table.");
        }
    }

    if ($agentCheck && $agentCheck->num_rows > 0) {
        // ── This message is FROM an agent — route it to their assigned parent ──
        $agentData   = $agentCheck->fetch_assoc();
        $agentId     = intval($agentData['id']);
        $agentName   = $agentData['name'];
        $agentSchool = intval($agentData['school_id']);

        wa_log("Matched agent: id=$agentId, name='$agentName', school=$agentSchool, stored_wa='{$agentData['whatsapp_no']}'");

        // Find the most recent assigned enquiry for this agent
        $assignedEnq = db_query($conn,
            "SELECT pe.id AS enquiry_id, pe.subject,
                    u.name AS parent_name, u.phone_no AS parent_phone
             FROM parent_enquiries pe
             LEFT JOIN users u ON pe.parent_id = u.id
             WHERE pe.assigned_agent_id = ?
               AND pe.school_id = ?
               AND pe.status = 'assigned'
             ORDER BY pe.created_at DESC LIMIT 1",
            'ii', [$agentId, $agentSchool]
        );

        if (!$assignedEnq || $assignedEnq->num_rows === 0) {
            wa_log("Agent '$agentName' (id=$agentId) has no active assigned enquiry — cannot route message.");
            http_response_code(200);
            echo "EVENT_RECEIVED";
            exit;
        }

        $enq         = $assignedEnq->fetch_assoc();
        $enqId       = intval($enq['enquiry_id']);
        $parentName  = $enq['parent_name']  ?? '';
        $parentPhone = preg_replace('/[^0-9]/', '', $enq['parent_phone'] ?? '');
        if (strlen($parentPhone) === 10 && strpos($parentPhone, '0') === 0) {
            $parentPhone = '27' . substr($parentPhone, 1);
        }

        wa_log("Routing agent reply to parent: phone=$parentPhone, name='$parentName', enquiry=$enqId");

        if (empty($parentPhone)) {
            wa_log("Parent has no phone number — cannot forward agent reply.");
            http_response_code(200);
            echo "EVENT_RECEIVED";
            exit;
        }

        // Forward the agent's message to the parent using smart fallback:
        // - Free-form if parent messaged within last 24 h (they get exact words)
        // - Template otherwise (always delivers)
        global $last_whatsapp_error;
        $subjectDisplay = !empty($enq['subject']) ? $enq['subject'] : 'your enquiry';
        $preview        = mb_strlen($text) > 100 ? mb_substr($text, 0, 97) . '...' : $text;
        $parentDisplay  = !empty($parentName) ? $parentName : 'there';

        // Check 24 h service window
        $windowRow = db_query($conn,
            "SELECT id FROM whatsapp_chats
             WHERE RIGHT(REPLACE(contact_phone, '+', ''), 9) = ?
               AND direction = 'inbound'
               AND created_at >= NOW() - INTERVAL 24 HOUR
             LIMIT 1",
            's', [substr($parentPhone, -9)]
        );
        $windowOpen = ($windowRow && $windowRow->num_rows > 0);

        $forwarded = false;
        if ($windowOpen) {
            $forwarded = send_whatsapp($parentPhone, $text);
            wa_log("Forward (free-form, window open) to $parentPhone: " . ($forwarded ? 'SENT' : 'FAILED — ' . ($last_whatsapp_error ?? 'unknown')));
        }

        if (!$forwarded) {
            $forwarded = send_whatsapp(
                $parentPhone, '',
                true, 'agent_message_parent',
                [$parentDisplay, $agentName, $subjectDisplay, $preview],
                'en_US'
            );
            wa_log("Forward (template fallback) to $parentPhone: " . ($forwarded ? 'SENT' : 'FAILED — ' . ($last_whatsapp_error ?? 'unknown')));
        }

        // Save as outbound in whatsapp_chats so it appears in the inbox thread
        $dedupCheck = db_query($conn,
            "SELECT id FROM whatsapp_chats WHERE wa_message_id = ? LIMIT 1",
            's', [$wa_id]
        );
        if (!$dedupCheck || $dedupCheck->num_rows === 0) {
            db_query($conn,
                "INSERT INTO whatsapp_chats
                    (school_id, contact_phone, contact_name, direction, message_text, wa_message_id, enquiry_id, agent_id, is_read)
                 VALUES (?, ?, ?, 'outbound', ?, ?, ?, ?, 1)",
                'isssiii',
                [$agentSchool, $parentPhone, $parentName, $text, $wa_id, $enqId, $agentId]
            );
            wa_log("Saved agent-originated outbound — school=$agentSchool, to=$parentPhone, enquiry=$enqId, agent=$agentId");
        }

        // Send delivery confirmation back to the agent
        if ($forwarded) {
            send_whatsapp($from, "✅ Your message was delivered to {$parentName}.");
            wa_log("Delivery confirmation sent back to agent $from.");
        }

        http_response_code(200);
        echo "EVENT_RECEIVED";
        exit;
    }
    // ── End agent detection — fall through to normal inbound parent handling ─

    // ── Determine school_id + enquiry context ───────────────────────────────
    // Use last 9 digits for flexible phone matching (handles +27/27/0 prefixes).
    $last9            = substr(preg_replace('/[^0-9]/', '', $from), -9);
    $school_id        = 0;
    $enquiry_id       = 0;
    $agent_id         = 0;
    $enquiry_subject  = '';

    // Strategy 1: previous chat history seeded when enquiry was assigned —
    // also pulls enquiry_id and agent_id so we can notify the right agent.
    $chatRow = db_query($conn,
        "SELECT wc.school_id, wc.enquiry_id, wc.agent_id, pe.subject AS enquiry_subject
         FROM whatsapp_chats wc
         LEFT JOIN parent_enquiries pe ON pe.id = wc.enquiry_id
         WHERE RIGHT(REPLACE(wc.contact_phone, '+', ''), 9) = ?
           AND wc.school_id > 0
         ORDER BY wc.created_at DESC LIMIT 1",
        's', [$last9]
    );
    if ($chatRow && $chatRow->num_rows > 0) {
        $r                = $chatRow->fetch_assoc();
        $school_id        = intval($r['school_id']);
        $enquiry_id       = intval($r['enquiry_id'] ?? 0);
        $agent_id         = intval($r['agent_id']   ?? 0);
        $enquiry_subject  = $r['enquiry_subject']   ?? '';
        wa_log("school_id=$school_id, enquiry_id=$enquiry_id, agent_id=$agent_id resolved from whatsapp_chats (last9=$last9)");
    }

    // Strategy 2: look up parent in users table
    if ($school_id === 0) {
        $userRow = db_query($conn,
            "SELECT school_id FROM users
             WHERE RIGHT(REPLACE(REPLACE(REPLACE(phone_no, ' ', ''), '-', ''), '+', ''), 9) = ?
             LIMIT 1",
            's', [$last9]
        );
        if ($userRow && $userRow->num_rows > 0) {
            $school_id = intval($userRow->fetch_assoc()['school_id']);
            wa_log("school_id=$school_id resolved from users table (last9=$last9)");
        }
    }

    // Strategy 3: look up enquiry by parent phone if we still have no agent
    if ($agent_id === 0 && $school_id > 0) {
        $enqRow = db_query($conn,
            "SELECT pe.id AS enquiry_id, pe.assigned_agent_id, pe.subject,
                    sa.whatsapp_no AS agent_wa, sa.name AS agent_name
             FROM parent_enquiries pe
             LEFT JOIN users u ON pe.parent_id = u.id
             LEFT JOIN school_agents sa ON pe.assigned_agent_id = sa.id
             WHERE pe.school_id = ?
               AND pe.status = 'assigned'
               AND RIGHT(REPLACE(REPLACE(REPLACE(u.phone_no,' ',''),'-',''),'+',''), 9) = ?
             ORDER BY pe.created_at DESC LIMIT 1",
            'is', [$school_id, $last9]
        );
        if ($enqRow && $enqRow->num_rows > 0) {
            $enqData         = $enqRow->fetch_assoc();
            $enquiry_id      = intval($enqData['enquiry_id']);
            $agent_id        = intval($enqData['assigned_agent_id'] ?? 0);
            $enquiry_subject = $enquiry_subject ?: ($enqData['subject'] ?? '');
            wa_log("enquiry_id=$enquiry_id, agent_id=$agent_id resolved from parent_enquiries (last9=$last9)");
        }
    }

    if ($school_id === 0) {
        wa_log("WARNING: could not resolve school_id for from=$from (last9=$last9) — stored with school_id=0");
    }
    // ───────────────────────────────────────────────────────────────────────

    // Avoid duplicate messages
    $existing = db_query($conn,
        "SELECT id FROM whatsapp_chats WHERE wa_message_id = ? LIMIT 1",
        's', [$wa_id]
    );
    if ($existing && $existing->num_rows > 0) {
        wa_log("Duplicate message skipped — wa_id=$wa_id");
    } else {
        $enqIdInsert = $enquiry_id > 0 ? $enquiry_id : null;
        // Build INSERT dynamically — only include enquiry_id column when we have one,
        // because bind_param cannot bind NULL with type 'i'.
        if ($enqIdInsert !== null) {
            $result = db_query($conn,
                "INSERT INTO whatsapp_chats
                    (school_id, contact_phone, contact_name, direction, message_text, wa_message_id, enquiry_id, is_read)
                 VALUES (?, ?, ?, 'inbound', ?, ?, ?, 0)",
                'issssi', [$school_id, $from, $contactName, $text, $wa_id, $enqIdInsert]
            );
        } else {
            $result = db_query($conn,
                "INSERT INTO whatsapp_chats
                    (school_id, contact_phone, contact_name, direction, message_text, wa_message_id, is_read)
                 VALUES (?, ?, ?, 'inbound', ?, ?, 0)",
                'issss', [$school_id, $from, $contactName, $text, $wa_id]
            );
        }
        if ($result) {
            wa_log("SAVED inbound message — school_id=$school_id, from=$from, enquiry_id=$enquiry_id, insert_id=" . $conn->insert_id);
        } else {
            wa_log("ERROR: INSERT failed — " . $conn->error);
        }
    }

    // ── Notify assigned agent that the parent replied ────────────────────
    // Uses approved template 'parent_replied_agent' — works outside the 24-hour window.
    // Template body: "Hello {{1}}! The parent {{2}} has replied to enquiry "{{3}}": "{{4}}".
    //                 Log in to Find & Return to respond."
    if ($agent_id > 0) {
        $agentRow = db_query($conn,
            "SELECT name, whatsapp_no FROM school_agents WHERE id = ? LIMIT 1",
            'i', [$agent_id]
        );
        if ($agentRow && $agentRow->num_rows > 0) {
            $agent      = $agentRow->fetch_assoc();
            $agentPhone = preg_replace('/[^0-9]/', '', $agent['whatsapp_no'] ?? '');
            if (strlen($agentPhone) === 10 && strpos($agentPhone, '0') === 0) {
                $agentPhone = '27' . substr($agentPhone, 1);
            }

            if (!empty($agentPhone)) {
                $parentDisplay  = !empty($contactName) ? $contactName : $from;
                $subjectDisplay = !empty($enquiry_subject) ? $enquiry_subject : 'your enquiry';
                $preview        = mb_strlen($text) > 100 ? mb_substr($text, 0, 97) . '...' : $text;

                global $last_whatsapp_error;
                $notified = send_whatsapp(
                    $agentPhone, '',
                    true, 'parent_replied_agent',
                    [$agent['name'], $parentDisplay, $subjectDisplay,
                     $preview . ' — Reply via: https://findandreturn.co.za/wa_inbox'],
                    'en_US'
                );
                wa_log("Agent template notification to $agentPhone (agent_id=$agent_id): "
                    . ($notified ? 'SENT' : 'FAILED — ' . ($last_whatsapp_error ?? 'unknown')));

                // Store a system record in the thread so the inbox shows the agent was alerted
                if ($notified) {
                    $sysMsg = "[System] Agent {$agent['name']} was notified of this reply.";
                    if ($enquiry_id > 0) {
                        db_query($conn,
                            "INSERT INTO whatsapp_chats
                                (school_id, contact_phone, contact_name, direction, message_text, enquiry_id, agent_id, is_read)
                             VALUES (?, ?, ?, 'outbound', ?, ?, ?, 1)",
                            'isssii',
                            [$school_id, $from, $contactName, $sysMsg, $enquiry_id, $agent_id]
                        );
                    } else {
                        db_query($conn,
                            "INSERT INTO whatsapp_chats
                                (school_id, contact_phone, contact_name, direction, message_text, agent_id, is_read)
                             VALUES (?, ?, ?, 'outbound', ?, ?, 1)",
                            'isssi',
                            [$school_id, $from, $contactName, $sysMsg, $agent_id]
                        );
                    }
                }
            } else {
                wa_log("Agent id=$agent_id has no valid whatsapp_no — skipping notification.");
            }
        }
    } else {
        wa_log("No assigned agent found for this conversation — skipping agent notification.");
    }
    // ────────────────────────────────────────────────────────────────────

    http_response_code(200);
    echo "EVENT_RECEIVED";
    exit;
}

// Default response for other methods
http_response_code(405);
echo "Method Not Allowed";
