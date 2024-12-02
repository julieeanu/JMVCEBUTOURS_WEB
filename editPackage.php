<?php
include 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $packageId = $_POST['package_id'];
    $title = $_POST['title'];
    $price = floatval($_POST['price']);
    $description = $_POST['description'];
    $itineraryItems = json_decode($_POST['itinerary_items']);
    $inclusionItems = json_decode($_POST['inclusion_items']);

    $itinerary = implode(', ', $itineraryItems);
    $inclusions = implode(', ', $inclusionItems);

    $mainImagePath = null;
    $existingMainImage = null;

    $result = $conn->query("SELECT main_image FROM AddedPackages WHERE package_id = $packageId");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $existingMainImage = $row['main_image'];
    }

    if (isset($_FILES['mainImage']) && $_FILES['mainImage']['error'] === UPLOAD_ERR_OK) {
        $mainImagePath = 'uploads/' . basename($_FILES['mainImage']['name']);
        if (!move_uploaded_file($_FILES['mainImage']['tmp_name'], $mainImagePath)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload main image.']);
            exit;
        }
    } else {
        $mainImagePath = $existingMainImage; // Use existing image if no new upload
    }

    $sql = "UPDATE AddedPackages SET title = ?, price = ?, description = ?, itinerary = ?, inclusions = ?, main_image = ? WHERE package_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sdssssi", $title, $price, $description, $itinerary, $inclusions, $mainImagePath, $packageId);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Package updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update package.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: Failed to prepare statement.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
