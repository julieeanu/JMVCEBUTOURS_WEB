<?php
include('includes/connection.php'); // Ensure this path is correct

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch canceled bookings
$sql = "SELECT b.booking_id, b.user_id, b.car_id, b.package_id, b.departure_date, b.booking_date, b.status, 
                u.user_name AS user_name, c.car_name AS car_name, p.package_name AS package_name
        FROM Bookings b
        JOIN Users u ON b.user_id = u.user_id
        JOIN Cars c ON b.car_id = c.car_id
        JOIN Packages p ON b.package_id = p.package_id
        WHERE b.status = 'canceled' 
        ORDER BY b.departure_date ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canceled Bookings</title>
    <link rel="stylesheet" href="dashboard.css"> <!-- Include your CSS file here -->
</head>
<body>
    <h1>Canceled Bookings</h1>
    
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>User Name</th>
                    <th>Car Name</th>
                    <th>Package Name</th>
                    <th>Departure Date</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['booking_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['car_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['package_name']); ?></td>
                        <td><?php echo $row['departure_date']; ?></td>
                        <td><?php echo $row['booking_date']; ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No canceled bookings found.</p>
    <?php endif; ?>

    <?php
    // Close connection
    $conn->close();
    ?>
</body>
</html>
