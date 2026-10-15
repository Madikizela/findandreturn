<?php
include('connection.php');

// System theme
$themeColor = "#007bff";
$logoPath = "img/Find&R.png";

if (isset($_POST["submit"])) {
    $qrImage    = $_SESSION['qrImage'] ?? '';
    $school_item = $_POST["school_item"] ?? '';

    $sql = "SELECT c.*, s.school_name as school, u.phone_no as parent_phoneNo, u.email as parent_email
            FROM children c
            LEFT JOIN schools s ON c.school_id = s.id
            LEFT JOIN users u ON c.parent_id = u.id
            WHERE (c.qr_image LIKE ? OR c.unique_reference LIKE ?) AND c.status = 'active'";
    $result = db_query($conn, $sql, "ss", ["%$qrImage%", "%$qrImage%"]);

    if ($result && $result->num_rows > 0) {
        $row            = $result->fetch_assoc();
        $first_name     = $row['first_name'];
        $surname        = $row['surname'];
        $school         = $row['school'];
        $grade          = $row['grade'];
        $parent_email   = $row['parent_email'];
        $parent_phoneNo = $row['parent_phoneNo'];
        $child_id       = intval($row['id']);
        $school_id      = intval($row['school_id']);

        // Email notification
        $to      = $parent_email;
        $subject = "Item Found - Find and Return";
        $message = "Good news! A $school_item belonging to $first_name (Grade $grade at $school) has been found and will be dropped off at their classroom.";
        @mail($to, $subject, $message, "From: Find and Return <no-reply@findandreturn.co.za>\r\nContent-type: text/html; charset=UTF-8\r\n");

        // WhatsApp notification
        $wa_phone = preg_replace('/[^0-9]/', '', $parent_phoneNo ?? '');
        if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
            $wa_phone = '27' . substr($wa_phone, 1);
        }
        send_whatsapp($wa_phone, '', true, 'item_found_class', [$school_item, $first_name, $grade, $school], 'en_US');

        // Update new_lost_items — mark as found
        $matchedByName = db_query($conn,
            "SELECT id FROM new_lost_items WHERE child_id = ? AND school_id = ? AND tracking_status = 'lost' AND item_name LIKE ? ORDER BY created_at DESC LIMIT 1",
            "iis", [$child_id, $school_id, "%$school_item%"]
        );
        if ($matchedByName && $matchedByName->num_rows > 0) {
            $match_id = intval($matchedByName->fetch_assoc()['id']);
            db_query($conn, "UPDATE new_lost_items SET tracking_status = 'found' WHERE id = ?", "i", [$match_id]);
        } else {
            $anyLost = db_query($conn,
                "SELECT id FROM new_lost_items WHERE child_id = ? AND school_id = ? AND tracking_status = 'lost' ORDER BY created_at DESC LIMIT 1",
                "ii", [$child_id, $school_id]
            );
            if ($anyLost && $anyLost->num_rows > 0) {
                $any_id = intval($anyLost->fetch_assoc()['id']);
                db_query($conn, "UPDATE new_lost_items SET tracking_status = 'found', item_name = ? WHERE id = ?", "si", [$school_item, $any_id]);
            } else {
                $unique_id = 'FND-' . strtoupper(substr(md5(uniqid()), 0, 8));
                db_query($conn,
                    "INSERT INTO new_lost_items (child_id, school_id, item_name, description, date_lost, unique_report_id, tracking_status) VALUES (?, ?, ?, ?, NOW(), ?, 'found')",
                    "iisss", [$child_id, $school_id, $school_item, "Found and delivered to Grade $grade class at $school", $unique_id]
                );
            }
        }

        echo "<script>alert('Parent notified! " . addslashes($school_item) . " for " . addslashes($first_name) . " will be delivered to Grade $grade.'); window.location.href = 'index';</script>";
        exit();
    } else {
        echo "<script>alert('Child not found. Please try scanning again.'); window.location.href = 'massege';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Return to Class - Find and Return</title>
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
            background: #17a2b8;
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
                    <i class="fas fa-school"></i>
                </div>
                <h2 class="h4 mb-0">Take the Item to the learner's class</h2>
            </div>
            <div class="card-body">
                <?php $grade = $_SESSION['grade'] ?? 'Unknown'; ?>
                <p class="text-center text-muted mb-4">
                    Please deliver the item to the <strong>Grade <?php echo htmlspecialchars($grade); ?></strong> classroom.
                </p>
                
                <form action="class" method="post">
                    <div class="mb-4">
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
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="submit" class="btn btn-info btn-lg text-white">
                            Submit & Notify Parent <i class="fas fa-paper-plane ms-2"></i>
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
