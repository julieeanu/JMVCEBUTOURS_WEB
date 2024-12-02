<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection
include('includes/connection.php'); // Ensure this path is correct

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch form inputs
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $adminId = 5; // Assuming the admin is logged in and their admin_id is available (use session or other method in practice)

    // Validate that all fields are filled
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'All fields are required.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirmation password do not match.';
    } else {
        // Fetch the current password from the database
        $query = "SELECT admin_password FROM Admin_Users WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $adminId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);

        // Verify the current password
        if (password_verify($currentPassword, $admin['admin_password'])) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update the password in the database
            $updateQuery = "UPDATE Admin_Users SET admin_password = ? WHERE admin_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, 'si', $hashedPassword, $adminId);

            if (mysqli_stmt_execute($updateStmt)) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Error updating password. Please try again.';
            }
        } else {
            $error = 'Current password is incorrect.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            background-color: #ffffff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            padding-top: 20px;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #555;
            display: block;
        }
        .sidebar a:hover {
            background-color: #18264E;
            color: #fff;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
        }
        /* Similar to the table layout in Bookings Management */
        .table-container {
            margin-top: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            border-radius: 0;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 0;
        }
        .sidebar .active-link {
    background-color: #18264E;
    color: #fff;
}

.sidebar .active-link:hover {
    background-color: #18264E; /* Ensures it stays the same on hover */
}

    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <aside class="col-md-3 col-lg-2 sidebar p-3">
        <h2 class="text-center">JMV Tours</h2>
        <nav class="mt-4">
            <ul class="nav flex-column">
                <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li class="nav-item"><a href="packages.html" class="nav-link">Packages</a></li>
                <li class="nav-item"><a href="Bookings.php" class="nav-link">Bookings</a></li>
                <!-- Ensure this item has a special class to show it's active -->
                <li class="nav-item"><a href="ChangePass.php" class="nav-link active-link">Change Password</a></li>
                <li class="nav-item"><a href="login.html" class="nav-link">Logout</a></li>
            </ul>
        </nav>
    </aside>
</div>


    <!-- Main content -->
    <div class="content">
        <h2 class="my-4">Change Password</h2>

        <!-- Display errors or success messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Password change form inside table-like container -->
        <div class="table-container">
            <form method="POST" action="ChangePass.php">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>
