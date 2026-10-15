
<?php
// Include your database connection script
include("connection.php");

// Check if grade and subject parameters are set and not empty
if (isset($_GET['grade']) && isset($_GET['subject'])) {
    // Use prepared statements to prevent SQL Injection
    $sql = "SELECT * FROM study WHERE grade = ? AND subject = ?";
    $result = db_query($conn, $sql, "ss", [$grade, $subject]);

    // Check if there are rows in the result
    if ($result && $result->num_rows > 0) {
        echo '<table>
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Subject</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>';

        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                <td>' . $row["grade"] . '</td>
                <td>' . $row["subject"] . '</td>
                <td><a href="' . $row["file_path"] . '" target="_blank">Open PDF</a></td>
              </tr>';
        }

        echo '</tbody></table>';
    } else {
        echo "0 results";
    }
} else {
    echo "Grade and subject parameters are required.";
}

// Close the database connection
$conn->close();
?>
