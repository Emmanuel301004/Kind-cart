<?php
// Include the autoloader from Composer
require 'vendor/autoload.php';

// Import the necessary PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Simple authentication to prevent unauthorized access
// In a real application, use proper authentication
$password = "your_secure_password";

if (!isset($_POST['password']) || $_POST['password'] !== $password) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Send Newsletter</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; }
            input[type="password"], input[type="text"], textarea { width: 100%; padding: 8px; box-sizing: border-box; }
            button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>Send Newsletter</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// If we're here, password was correct
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subject']) && isset($_POST['content'])) {
    // Get newsletter content from form
    $subject = $_POST['subject'];
    $content = $_POST['content'];
    
    // Get subscribers list
    $subscribers = file("subscribers.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Send email to each subscriber
    $mail = new PHPMailer(true);
    
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';        // Replace with your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'emman302004@gmail.com';  // Replace with your email
        $mail->Password   = 'exvv ydkl meid mmxl';           // Replace with your password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        //Sender
        $mail->setFrom('emman302004@gmail.com', 'Kind Cart');
        
        // Add all subscribers to BCC (better privacy)
        foreach ($subscribers as $email) {
            $mail->addBCC($email);
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;">
                <h2 style="color: #333;">Kind Cart Newsletter</h2>
                ' . $content . '
                <p style="font-size: 12px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px;">
                    You\'re receiving this email because you subscribed to the Kind Cart Newsletter.<br>
                    To unsubscribe, please <a href="https://yourdomain.com/unsubscribe.php?email=SUBSCRIBER_EMAIL">click here</a>.
                </p>
            </div>
        ';
        
        // Plain text version
        $mail->AltBody = strip_tags($content);
        
        $mail->send();
        $success = "Newsletter sent successfully to " . count($subscribers) . " subscribers!";
    } catch (Exception $e) {
        $error = "Error sending newsletter: " . $mail->ErrorInfo;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Newsletter</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        textarea { height: 300px; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .success { color: green; padding: 10px; background-color: #f0f9f0; border: 1px solid #cce6cc; margin-bottom: 15px; }
        .error { color: red; padding: 10px; background-color: #f9f0f0; border: 1px solid #e6cccc; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Send Newsletter</h1>
    
    <?php if (isset($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <input type="hidden" name="password" value="<?php echo htmlspecialchars($password); ?>">
        
        <div class="form-group">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>
        </div>
        
        <div class="form-group">
            <label for="content">Newsletter Content (HTML):</label>
            <textarea id="content" name="content" required></textarea>
        </div>
        
        <button type="submit">Send Newsletter</button>
    </form>
</body>
</html>