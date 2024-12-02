
<?php
session_start();
include('includes/connection.php'); // Database connection
require 'includes/PHPMailer/src/PHPMailer.php'; // Include PHPMailer's classes
require 'includes/PHPMailer/src/SMTP.php';
require 'includes/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("SELECT * FROM Admin_Users WHERE admin_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password (assuming password is hashed)
        if (password_verify($password, $user['admin_password'])) {
            // Generate a verification code
            $verification_code = mt_rand(100000, 999999); // Random 6-digit code
            $_SESSION['verification_code'] = $verification_code;
            $_SESSION['admin_email'] = $email;

            // Update the user's verification code in the database
            $update_stmt = $conn->prepare("UPDATE Admin_Users SET verification_code = ? WHERE admin_email = ?");
            $update_stmt->bind_param("is", $verification_code, $email);
            $update_stmt->execute();

            // Send verification email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'joro.igot.swu@phinmaed.com'; // Your email
                $mail->Password ='fenyrabfkapdxjgl'; // Your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('joro.igot.swu@phinmaed.com', 'JMV Tours Cebu'); // Sender
                $mail->addAddress($email); // Recipient

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your Verification Code';
                $mail->Body = 'Your verification code is: <strong>' . $verification_code . '</strong>';

                $mail->send();

                // Redirect to verify.php
                header("Location: verify.php");
                exit();
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Invalid email or password.";
        }
    } else {
        echo "No user found with this email.";
    }
}
?>
