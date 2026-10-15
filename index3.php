<?php
include('connection.php');
$qrImage = $_SESSION['qrImage'] ?? '';
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_item = $_POST["school_item"] ?? '';

    $sql = "SELECT c.*, s.school_name as school, u.phone_no as parent_phoneNo, u.email as parent_email
            FROM children c
            LEFT JOIN schools s ON c.school_id = s.id
            LEFT JOIN users u ON c.parent_id = u.id
            WHERE (c.qr_image LIKE ? OR c.unique_reference LIKE ?) AND c.status = 'active'";
    $result = db_query($conn, $sql, "ss", ["%$qrImage%", "%$qrImage%"]);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $parent_email   = $row['parent_email'];
            $parent_phoneNo = $row['parent_phoneNo'];
            $school         = $row['school'];
            $first_name     = $row['first_name'];

            // Send email notification
            $to      = $parent_email;
            $subject = "Find And Return Application.";
            $message = $school_item . " for your child " . $first_name . " is now at the Find and Return help desk. You can collect it or send someone on your behalf at " . $school;
            $header  = "From: Find and Return <no-reply@findandreturn.co.za>\r\n";
            @mail($to, $subject, $message, $header);

            // WhatsApp notification
            $wa_phone = preg_replace('/[^0-9]/', '', $parent_phoneNo ?? '');
            if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
                $wa_phone = '27' . substr($wa_phone, 1);
            }
            send_whatsapp($wa_phone, '', true, 'item_found_helpdesk', [$school_item, $first_name, $school], 'en_US');

            // ── Update new_lost_items so school admin can see it at the admin desk ──
            $child_id  = intval($row['id']);
            $school_id = intval($row['school_id']);

            $matchedByName = db_query($conn,
                "SELECT id FROM new_lost_items
                 WHERE child_id = ? AND school_id = ? AND tracking_status = 'lost'
                   AND item_name LIKE ?
                 ORDER BY created_at DESC LIMIT 1",
                "iis", [$child_id, $school_id, "%$school_item%"]
            );

            if ($matchedByName && $matchedByName->num_rows > 0) {
                $match_id = intval($matchedByName->fetch_assoc()['id']);
                db_query($conn, "UPDATE new_lost_items SET tracking_status = 'found' WHERE id = ?", "i", [$match_id]);
            } else {
                $anyLost = db_query($conn,
                    "SELECT id FROM new_lost_items
                     WHERE child_id = ? AND school_id = ? AND tracking_status = 'lost'
                     ORDER BY created_at DESC LIMIT 1",
                    "ii", [$child_id, $school_id]
                );
                if ($anyLost && $anyLost->num_rows > 0) {
                    $any_id = intval($anyLost->fetch_assoc()['id']);
                    db_query($conn, "UPDATE new_lost_items SET tracking_status = 'found', item_name = ? WHERE id = ?", "si", [$school_item, $any_id]);
                } else {
                    $unique_id = 'FND-' . strtoupper(substr(md5(uniqid()), 0, 8));
                    db_query($conn,
                        "INSERT INTO new_lost_items (child_id, school_id, item_name, description, date_lost, unique_report_id, tracking_status)
                         VALUES (?, ?, ?, ?, NOW(), ?, 'found')",
                        "iisss", [$child_id, $school_id, $school_item, "Found at helpdesk at $school", $unique_id]
                    );
                }
            }
            // ─────────────────────────────────────────────────────────────────

            echo "<script>window.location.href='index';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Item not found.'); window.location.href='index';</script>";
        exit();
    }
}

      
      

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Modal Auto-Open with Redirect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <meta name="robots" content="all,follow">
    <!-- Google fonts - Poppins -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom stylesheet -->
    <link rel="stylesheet" href="css/custom.css">
    <!-- Favicon -->
    <link rel="shortcut icon" href="img/favicon.ico">
</head>
<body>
    <!-- Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Select the Item You Are Confirming</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="cars">Select Item</label>
                            <select name="school_item" id="cars" class="form-control" required>
                                <option value="">Select Item</option>
                                <option value="Shoes">Shoes</option>
                                <option value="Shirts">Shirts</option>
                                <option value="Tie">Tie</option>
                                <option value="Blazer">Blazer</option>
                                <option value="Cardigan">Cardigan</option>
                                <option value="Socks">Socks</option>
                                <option value="Jersey">Jersey</option>
                                <option value="Trouser">Trouser</option>
                            </select>
                        </div>
                        <button type="submit" name="submit" class="btn btn-info">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Automatically open the modal when the page loads
            $('#confirmModal').modal('show');

            // Redirect to index2.php when the modal is hidden
            $('#confirmModal').on('hidden.bs.modal', function () {
                window.location.href = 'index2.php';
            });
        });
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

