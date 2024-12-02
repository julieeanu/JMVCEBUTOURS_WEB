<?php
require 'includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $packageId = $_POST['package_id'];

    // Prepare the SQL DELETE statement
    $stmt = $conn->prepare("DELETE FROM AddedPackages WHERE package_id = ?");
    $stmt->bind_param("i", $packageId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Package deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting package.']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
