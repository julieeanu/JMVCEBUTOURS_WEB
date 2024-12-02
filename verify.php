<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f6f8fa;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333333;
            text-align: center;
        }

        p {
            font-size: 14px;
            margin-bottom: 20px;
            color: #666666;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .code-inputs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .code-inputs input {
            width: 40px;
            height: 40px;
            margin: 0 5px;
            font-size: 24px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        button {
            width: 80%;
            padding: 12px;
            background-color: #6c5ce7;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #5b50c6;
        }

        .invalid {
            color: red;
            text-align: center;
            margin-top: 10px;
        }

        @media (max-width: 600px) {
            .container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify Your Account</h1>
        <p>Please enter the verification code sent to your email.</p>
        <form action="verify.php" method="POST">
            <div class="code-inputs">
                <!-- Creating separate boxes for the code -->
                <input type="text" maxlength="1" name="code1" required>
                <input type="text" maxlength="1" name="code2" required>
                <input type="text" maxlength="1" name="code3" required>
                <input type="text" maxlength="1" name="code4" required>
                <input type="text" maxlength="1" name="code5" required>
                <input type="text" maxlength="1" name="code6" required>
            </div>
            <button type="submit">Verify</button>
        </form>
        <?php
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $entered_code = $_POST['code1'] . $_POST['code2'] . $_POST['code3'] . $_POST['code4'] . $_POST['code5'] . $_POST['code6'];
            if (isset($entered_code) && $entered_code == $_SESSION['verification_code']) {
                // Mark user as verified
                include('includes/connection.php'); // Database connection
                $email = $_SESSION['admin_email'];
                $update_stmt = $conn->prepare("UPDATE Admin_Users SET is_verified = 1 WHERE admin_email = ?");
                $update_stmt->bind_param("s", $email);
                $update_stmt->execute();

                // Clear session verification data
                unset($_SESSION['verification_code']);
                unset($_SESSION['admin_email']);

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                echo "<p class='invalid'>Invalid verification code. Please try again.</p>";
            }
        }
        ?>
    </div>
</body>
</html>
