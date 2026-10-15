<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include('connection.php');
// Assuming $conn is your database connection
$district = $_POST['district'];

$sql = "SELECT DISTINCT municipality FROM province WHERE district = '$district'";
$result = $conn->query($sql);

$output = '<option value="">Select Municipality</option>';

while ($row = $result->fetch_assoc()) {
    $output .= "<option value='" . $row['municipality'] . "'>" . $row['municipality'] . "</option>";
}

echo $output;
?>

