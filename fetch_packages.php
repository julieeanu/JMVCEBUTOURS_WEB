<?php
include 'includes/connection.php';

header('Content-Type: application/json');
$base_url = 'https://honeydew-albatross-910973.hostingersite.com/uploads/';

function preparePackageResponse($row, $base_url) {
    return [
        'package_id' => (int)$row['package_id'],
        'title' => $row['title'],
        'price' => (float)$row['price'],
        'description' => $row['description'],
        'main_image' => $row['main_image'] ? $base_url . basename($row['main_image']) : null,
        'image1' => $row['image1'] ? $base_url . basename($row['image1']) : null,
        'image2' => $row['image2'] ? $base_url . basename($row['image2']) : null,
        'image3' => $row['image3'] ? $base_url . basename($row['image3']) : null,
        'itinerary' => $row['itinerary'],
        'inclusions' => $row['inclusions'],
    ];
}

// Fetch packages from the database
$stmt = $conn->prepare("
    SELECT 
        p.package_id, p.title, p.price, p.description, 
        p.main_image, p.image1, p.image2, p.image3,
        GROUP_CONCAT(DISTINCT i.itinerary_item SEPARATOR ', ') AS itinerary,
        GROUP_CONCAT(DISTINCT c.inclusion_item SEPARATOR ', ') AS inclusions
    FROM AddedPackages p
    LEFT JOIN Itineraries i ON p.package_id = i.package_id
    LEFT JOIN Inclusions c ON p.package_id = c.package_id
    GROUP BY p.package_id
");

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $packages[] = preparePackageResponse($row, $base_url);
    }

    // Return packages or empty array if none found
    echo json_encode($packages);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
}

$conn->close();
?>
