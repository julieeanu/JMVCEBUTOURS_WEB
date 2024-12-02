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

    // Check for upload errors
    if ($_FILES['mainImage']['error'] !== UPLOAD_ERR_OK || 
        $_FILES['subImage1']['error'] !== UPLOAD_ERR_OK || 
        $_FILES['subImage2']['error'] !== UPLOAD_ERR_OK || 
        $_FILES['subImage3']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Error uploading files.';
        echo json_encode($response);
        exit;
    }

    // Move uploaded files
    move_uploaded_file($_FILES['mainImage']['tmp_name'], $uploadDir . basename($mainImage));
    move_uploaded_file($_FILES['subImage1']['tmp_name'], $uploadDir . basename($subImage1));
    move_uploaded_file($_FILES['subImage2']['tmp_name'], $uploadDir . basename($subImage2));
    move_uploaded_file($_FILES['subImage3']['tmp_name'], $uploadDir . basename($subImage3));

    // Prepare SQL statement
    $query = "INSERT INTO AddedPackages (title, price, description, main_image, image1, image2, image3) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    // Bind parameters
    $stmt->bind_param("sdsssss", $title, $price, $description, $mainImage, $subImage1, $subImage2, $subImage3);

    // Execute statement
    if ($stmt->execute()) {
        $packageId = $stmt->insert_id;

        // Insert itinerary items
        if ($itinerary_items) {
            foreach ($itinerary_items as $item) {
                $stmt = $conn->prepare("INSERT INTO Itineraries (package_id, itinerary_item) VALUES (?, ?)");
                $stmt->bind_param("is", $packageId, $item);
                $stmt->execute();
            }
        }

        // Insert inclusion items
        if ($inclusion_items) {
            foreach ($inclusion_items as $item) {
                $stmt = $conn->prepare("INSERT INTO Inclusions (package_id, inclusion_item) VALUES (?, ?)");
                $stmt->bind_param("is", $packageId, $item);
                $stmt->execute();
            }
        }

        $response['success'] = true;
        $response['message'] = 'Package added successfully';
    } else {
        $response['message'] = 'Failed to add package: ' . $stmt->error;
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
