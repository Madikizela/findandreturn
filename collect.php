<?php
include('connection.php');

// Redirect if accessed directly without POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit'])) {
    header('Location: colecting_item');
    exit();
}

$parent_email        = $_POST['parent_email']        ?? '';
$first_name          = $_POST['first_name']          ?? '';
$surname             = $_POST['surname']             ?? '';
$child_phoneNo       = $_POST['child_phoneNo']       ?? '';
$parent_phoneNo      = $_POST['parent_phoneNo']      ?? '';
$p_address           = $_POST['p_address']           ?? '';
$school              = $_POST['school']              ?? '';
$grade               = $_POST['grade']               ?? '';
$collectedBy         = $_POST['collectedBy']         ?? '';
$collectedByContact  = $_POST['collectedByContact']  ?? '';
$item_id             = intval($_POST['item_id']      ?? 0);
$item_name           = $_POST['item_name']           ?? '';

if (empty($collectedBy) || empty($collectedByContact)) {
    header('Location: colecting_item');
    exit();
}

// 1. Record the collection
$query = "INSERT INTO collected_items
            (first_name, surname, parent_email, child_phoneNo, parent_phoneNo,
             p_address, school, grade, collectedBy, collectedByContact, collectedDate)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
db_query($conn, $query, "ssssssssss",
    [$first_name, $surname, $parent_email, $child_phoneNo, $parent_phoneNo,
     $p_address, $school, $grade, $collectedBy, $collectedByContact]
);

// 2. Mark the item as collected in new_lost_items
if ($item_id > 0) {
    db_query($conn,
        "UPDATE new_lost_items SET tracking_status = 'collected' WHERE id = ?",
        "i", [$item_id]
    );
}

// 3. WhatsApp notification to parent
$wa_phone = preg_replace('/[^0-9]/', '', $parent_phoneNo ?? '');
if (strlen($wa_phone) === 10 && strpos($wa_phone, '0') === 0) {
    $wa_phone = '27' . substr($wa_phone, 1);
}
send_whatsapp($wa_phone, '', true, 'item_collected', [$first_name, $collectedBy], 'en_US');

$_SESSION['parent_email'] = $parent_email;

$itemDisplay  = addslashes($item_name ?: 'Item');
$childDisplay = addslashes($first_name);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collection Confirmed</title>
</head>
<body>
<script>
    alert('Collection confirmed! <?php echo $itemDisplay; ?> for <?php echo $childDisplay; ?> has been marked as collected.');
    window.location.href = 'colecting_item';
</script>
</body>
</html>
