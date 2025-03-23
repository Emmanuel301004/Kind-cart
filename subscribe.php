<?php
// Include the autoloader from Composer
require 'vendor/autoload.php';

// Import the necessary PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Validate email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Save the email to our subscribers list
        $file = fopen("subscribers.txt", "a");
        fwrite($file, $email . PHP_EOL);
        fclose($file);
        
        // Send confirmation email
        $mail = new PHPMailer(true);
        
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';        // Replace with your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'emman302004@gmail.com';  // Replace with your email
            $mail->Password   = 'exvv ydkl meid mmxl';    // Replace with your password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            //Recipients
            $mail->setFrom('emman302004@gmail.com', 'Kind Cart');
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Kind Cart Newsletter';
            $mail->Body    = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;">
                    <h2 style="color: #333;">Welcome to Kind Cart Newsletter!</h2>
                    <p>Thank you for subscribing to our newsletter. You will now receive updates about:</p>
                    <ul>
                        <li>New book arrivals</li>
                        <li>Special offers and discounts</li>
                        <li>Author events and book signings</li>
                    </ul>
                    <p>We\'re excited to have you as part of our community!</p>
                    <p>Best regards,<br>The Kind Cart Team</p>
                </div>
            ';
            $mail->AltBody = 'Thank you for subscribing to Kind Cart Newsletter. You will now receive updates about new books, special offers, and author events.';
            
            $mail->send();
            
            // Set success message in session and redirect to dashboard
            session_start();
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = 'You have successfully subscribed to our newsletter!';
            
        } catch (Exception $e) {
            // Set error message in session
            session_start();
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_message'] = 'Mailer Error: ' . $mail->ErrorInfo;
        }
    } else {
        // Invalid email
        session_start();
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = 'Invalid email address. Please try again.';
    }
} else {
    // Not a POST request
    session_start();
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_message'] = 'Invalid request method.';
}

// Redirect back to dashboard
header('Location: dashboard.php');
exit;
?>