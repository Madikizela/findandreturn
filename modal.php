<?php
session_start();
include('connection.php');

// System theme
$themeColor = "#007bff";
$logoPath = "img/Find&R.png";

if (isset($_POST["submit"])) {
    $qrImage = $_SESSION['qrImage'] ?? '';
    // Query the children table (same as index4.php uses)
    $sql = "SELECT c.*, s.school_name as school, u.phone_no as parent_phoneNo, u.email as parent_email
            FROM children c
            LEFT JOIN schools s ON c.school_id = s.id
            LEFT JOIN users u ON c.parent_id = u.id
            WHERE (c.qr_image LIKE ? OR c.unique_reference LIKE ?) AND c.status = 'active'";
    $result = db_query($conn, $sql, "ss", ["%$qrImage%", "%$qrImage%"]);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $first_name     = $row['first_name'];
            $surname        = $row['surname'];
            $school         = $row['school'];        // from JOIN alias
            $grade          = $row['grade'];
            $parent_phoneNo = $row['parent_phoneNo']; // from JOIN alias
            $parent_email   = $row['parent_email'];   // from JOIN alias
            $school_item    = $_POST["school_item"];
            $box_number     = $_POST["box_number"];

            $to = $parent_email;
            $subject = "Item Found - Find and Return";
            $message = "Someone found a " . $school_item . " belonging to " . $first_name . " " . $surname . ". It has been dropped in Find and Return Box No " . $box_number . " at " . $school . ".";

            $header = "From: Find and Return <no-reply@findandreturn.co.za>\r\n";
            $header .= "Reply-To: no-reply@findandreturn.co.za\r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-type: text/html; charset=UTF-8\r\n";

            $retval = @mail($to, $subject, $message, $header);

            // WhatsApp Notification — template: item_found_box [item, childFullName, boxNumber, school]
            $wa_phone = preg_replace('/[^0-9]/', '', $parent_phoneNo ?? '');
            if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
                $wa_phone = '27' . substr($wa_phone, 1);
            }
            send_whatsapp($wa_phone, '', true, 'item_found_box', [$school_item, $first_name . ' ' . $surname, $box_number, $school], 'en_US');

            // ── Update new_lost_items so school admin can see it at the admin desk ──
            // The QR code is on the child, not a specific item. When scanned:
            // 1. Update any existing lost item matching the selected item name → found
            // 2. If none matched by name, update the most recent lost item → found
            // 3. If no lost items at all → create a new found record
            $child_id  = intval($row['id']);
            $school_id = intval($row['school_id']);

            // Try to match by item name first
            $matchedByName = db_query($conn,
                "SELECT id FROM new_lost_items
                 WHERE child_id = ? AND school_id = ? AND tracking_status = 'lost'
                   AND item_name LIKE ?
                 ORDER BY created_at DESC LIMIT 1",
                "iis", [$child_id, $school_id, "%$school_item%"]
            );

            if ($matchedByName && $matchedByName->num_rows > 0) {
                $match_id = intval($matchedByName->fetch_assoc()['id']);
                db_query($conn,
                    "UPDATE new_lost_items SET tracking_status = 'found' WHERE id = ?",
                    "i", [$match_id]
                );
            } else {
                // Fallback: update most recent lost item for this child
                $anyLost = db_query($conn,
                    "SELECT id FROM new_lost_items
                     WHERE child_id = ? AND school_id = ? AND tracking_status = 'lost'
                     ORDER BY created_at DESC LIMIT 1",
                    "ii", [$child_id, $school_id]
                );

                if ($anyLost && $anyLost->num_rows > 0) {
                    $any_id = intval($anyLost->fetch_assoc()['id']);
                    db_query($conn,
                        "UPDATE new_lost_items SET tracking_status = 'found', item_name = ? WHERE id = ?",
                        "si", [$school_item, $any_id]
                    );
                } else {
                    // No lost record exists — create new found record
                    $unique_id = 'FND-' . strtoupper(substr(md5(uniqid()), 0, 8));
                    db_query($conn,
                        "INSERT INTO new_lost_items (child_id, school_id, item_name, description, date_lost, unique_report_id, tracking_status)
                         VALUES (?, ?, ?, ?, NOW(), ?, 'found')",
                        "iisss",
                        [$child_id, $school_id, $school_item,
                         "Found in box $box_number at $school",
                         $unique_id]
                    );
                }
            }
            // ─────────────────────────────────────────────────────────────────

            echo "<script>alert('Thank you for finding and returning the " . addslashes($school_item) . "!'); window.location.href='index';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Error: Item information not found.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Return to Box - Find and Return</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7fa; }
        .return-card {
            max-width: 600px;
            margin: 60px auto;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header-custom {
            background: #28a745;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .card-body { padding: 40px; background: white; }
        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card return-card">
            <div class="card-header-custom">
                <div class="icon-box">
                    <i class="fas fa-box-open"></i>
                </div>
                <h2 class="h4 mb-0">Take Item to a Find and Return Box</h2>
            </div>
            <div class="card-body">
                <p class="text-center text-muted mb-4">
                    There are 5 boxes on this campus. Please provide the details below:
                </p>
                
                <form action="modal" method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Item Found:</label>
                        <select name="school_item" class="form-select form-select-lg" required>
                            <option value="">Choose item...</option>
                            <option value="Shoes">Shoes</option>
                            <option value="Shirt">Shirt</option>
                            <option value="Tie">Tie</option>
                            <option value="Blazer">Blazer</option>
                            <option value="Cardigan">Cardigan</option>
                            <option value="Socks">Socks</option>
                            <option value="Jersey">Jersey</option>
                            <option value="Trouser">Trouser</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Box Number:</label>
                        <select name="box_number" class="form-select form-select-lg" required>
                            <option value="">Choose box number...</option>
                            <?php for($i=1; $i<=5; $i++) echo "<option value='$i'>Box $i</option>"; ?>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="submit" class="btn btn-success btn-lg">
                            Submit & Notify Parent <i class="fas fa-check-circle ms-2"></i>
                        </button>
                        <a href="massege" class="btn btn-link text-decoration-none text-muted">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>
          

