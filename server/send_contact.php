<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoload and config files
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php'; // Make sure your config.php contains necessary credentials and other config

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();  // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // SMTP server
        $mail->SMTPAuth = true;  // Enable SMTP authentication
        $mail->Username = '';  // Your Gmail address
        $mail->Password = '';  // Your Gmail app password (NOT your Gmail password)
        $mail->SMTPSecure = 'tls';  // Enable TLS encryption
        $mail->Port = 587;  // TCP port for TLS
        
        // Recipients
        $mail->setFrom($_POST['email'], $_POST['name']);  // Sender info from form
        $mail->addAddress('ForsaDrive@gmail.com');  // Recipient email (where the form will be sent)

        // Content of the email
        $mail->isHTML(false);  // Set email format to plain text
        $mail->Subject = 'New Contact Form Submission';  // Subject of the email
        $mail->Body = "Name: {$_POST['name']}\nEmail: {$_POST['email']}\nMessage: {$_POST['message']}";  // Body of the email
        
        // Attempt to send the email
        $mail->send();
        header("Location: ../index.php?mailsent=1#contact");  // Redirect on success
    } catch (Exception $e) {
        // If there is an error, redirect with error message
        header("Location: ../index.php?mailsent=0&error=" . urlencode($e->getMessage()) . "#contact");
    }
    exit();  // End script
}
?>
