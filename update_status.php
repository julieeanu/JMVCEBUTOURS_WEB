<?php
// Include PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure the path is correct

include('includes/connection.php'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    // Debugging line
    var_dump($status);

    // Update logic for status
    if ($status === 'confirmed') {
        $newStatus = 'upcoming';

        // Fetch user email to send confirmation
        $stmt = $conn->prepare("SELECT u.user_email FROM Bookings b JOIN Users u ON b.user_id = u.user_id WHERE b.booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $stmt->bind_result($user_email);
            if ($stmt->fetch()) {
                // Debugging line
                echo "User email: " . $user_email; // Debugging line
            } else {
                echo "No email found for this booking."; // Debugging line
            }
        } else {
            echo "Error fetching email: " . $stmt->error; // Debugging line
        }
        $stmt->close();

        // Check if user_email is not empty
        if (!empty($user_email)) {
            // Send confirmation email
            $mail = new PHPMailer(true);
            try {
                // Enable verbose debug output
                $mail->SMTPDebug = 2; 
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server to send through
                $mail->SMTPAuth   = true;
                $mail->Username   = 'joro.igot.swu@phinmaed.com'; // Your Gmail address
                $mail->Password   = 'fenyrabfkapdxjgl'; // Your Gmail password or app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('joro.igot.swu@phinmaed.com', 'JMV Tours'); // Sender
                $mail->addAddress($user_email); // Add recipient

                // Content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = 'Booking Confirmed';
                $mail->Body    = 'Dear User,<br><br>Your booking has been confirmed. Thank you for choosing JMV Tours!<br><br>Best Regards,<br>JMV Tours Team';

                // Debugging line
                echo "Preparing to send email..."; // Debugging line
                $mail->send();
                echo "Email sent successfully."; // Debugging line
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "No user email found for this booking.";
        }
    } else {
        $newStatus = $status; // Set to pending, completed, or canceled directly
    }

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE Bookings SET status = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $newStatus, $booking_id);

    if ($stmt->execute()) {
        echo "Booking status updated successfully.";
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();

// Redirect back to the bookings page
header("Location: Bookings.php");
exit();
?>
