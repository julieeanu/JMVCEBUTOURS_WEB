<?php
// Include database connection file
include 'includes/connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debugging to see if data is being received
    if (isset($_POST['username'], $_POST['email'], $_POST['password'])) {
        echo "Received data:<br>";
        echo "Username: " . htmlspecialchars($_POST['username']) . "<br>";
        echo "Email: " . htmlspecialchars($_POST['email']) . "<br>";
        echo "Password: " . htmlspecialchars($_POST['password']) . "<br>";
    } else {
        echo "Form data is not being received.";
        exit(); // Stop the script here for debugging
    }

    // Retrieve form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Admin_Users WHERE admin_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($emailCount);
    $stmt->fetch();
    $stmt->close();

    if ($emailCount > 0) {
        echo "Error: Email is already in use.";
        exit();
    }

    // Insert the admin user into the database
    $sql = "INSERT INTO Admin_Users (admin_user, admin_email, admin_password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }
    
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        // Redirect to login page after successful signup
        header("Location: login.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
    <div class="container">
        <!-- Left Side with Background Image -->
        <div class="left-panel"></div>
        
        <!-- Right Side with Form -->
        <div class="right-panel">
            <div class="form-container">
                <h1>Welcome!</h1>
                <p>Create your admin account to manage tours and bookings. Sign up now to get started!</p>
                <form action="signup.php" method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Sign Up</button>
                </form>

                <a href="login.html">Already have an account? Sign In</a>
            </div>
        </div>
    </div>
</body>
</html>
