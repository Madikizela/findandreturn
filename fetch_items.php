<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'connection.php'; // Ensure database connection is included

    $child_name = $_POST['child_name'];
    $parent_email = $_POST['parent_email'];
    $status = "Registered";

    // Check if values are being passed correctly
    if (empty($child_name) || empty($parent_email)) {
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $query = "SELECT item FROM lost_items WHERE parent_email = ? AND tracking_status = ? AND child_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $parent_email, $status, $child_name);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row['item'];
    }

    // Return results as JSON
    if (empty($items)) {
        echo json_encode(['error' => 'No items found']);
    } else {
        echo json_encode($items);
    }
    exit;
}
?>

