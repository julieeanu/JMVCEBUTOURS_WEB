<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include 'includes/connection.php';

// Set JSON header
header('Content-Type: application/json');

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve POST data
    $packageId = $_POST['package_id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $description = $_POST['description'] ?? '';
    $itinerary_items = json_decode($_POST['itinerary_items'] ?? '[]');
    $inclusion_items = json_decode($_POST['inclusion_items'] ?? '[]');

    // Handle file uploads
    $uploadDir = 'uploads/';
    $mainImage = $_FILES['mainImage']['name'] ?? '';
    $subImage1 = $_FILES['subImage1']['name'] ?? '';
    $subImage2 = $_FILES['subImage2']['name'] ?? '';
    $subImage3 = $_FILES['subImage3']['name'] ?? '';

    // Prepare the SQL UPDATE statement
    $stmt = $conn->prepare("UPDATE AddedPackages SET title = ?, price = ?, description = ? WHERE package_id = ?");
    $stmt->bind_param("sdii", $title, $price, $description, $packageId);

    if ($stmt->execute()) {
        // Move uploaded files only if they are present
        // Repeat for each file as needed
        if (!empty($mainImage)) {
            move_uploaded_file($_FILES['mainImage']['tmp_name'], $uploadDir . basename($mainImage));
            $conn->query("UPDATE AddedPackages SET main_image = '$mainImage' WHERE package_id = $packageId");
        }

        // Clear existing itineraries and inclusions
        $conn->query("DELETE FROM Itineraries WHERE package_id = $packageId");
        $conn->query("DELETE FROM Inclusions WHERE package_id = $packageId");

        // Insert new itinerary items
        foreach ($itinerary_items as $item) {
            $stmt = $conn->prepare("INSERT INTO Itineraries (package_id, itinerary_item) VALUES (?, ?)");
            $stmt->bind_param("is", $packageId, $item);
            $stmt->execute();
        }

        // Insert new inclusion items
        foreach ($inclusion_items as $item) {
            $stmt = $conn->prepare("INSERT INTO Inclusions (package_id, inclusion_item) VALUES (?, ?)");
            $stmt->bind_param("is", $packageId, $item);
            $stmt->execute();
        }

        $response['success'] = true;
        $response['message'] = 'Package updated successfully';
    } else {
        $response['message'] = 'Failed to update package: ' . $stmt->error;
    }

    // Close statement
    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

// Close connection
$conn->close();

// Return JSON response
echo json_encode($response);
?>
