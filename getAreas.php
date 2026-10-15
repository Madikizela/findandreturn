<?php
include('connection.php');
// Assuming $conn is your database connection
$municipality = $_POST['municipality'];

$sql = "SELECT area FROM province WHERE municipality = '$municipality'";
$result = $conn->query($sql);

$output = '<option value="">Select Area</option>';

while ($row = $result->fetch_assoc()) {
    $output .= "<option value='" . $row['area'] . "'>" . $row['area'] . "</option>";
}

echo $output;
?>

