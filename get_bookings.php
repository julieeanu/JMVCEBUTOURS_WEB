<?php
include 'includes/connection.php'; // Ensure this path is correct

$query = "SELECT * FROM Bookings"; // Use 'Bookings' instead of 'bookings'
$result = $conn->query($query);

if ($result) {
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($bookings);
} else {
    echo json_encode(["error" => "Failed to retrieve bookings."]);
}

$conn->close();
?>
