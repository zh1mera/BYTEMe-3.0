<?php
// Mail configuration
require_once 'vendor/phpmailer/Exception.php';
require_once 'vendor/phpmailer/PHPMailer.php';
require_once 'vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function send_verification_email($to_email, $username, $verification_code) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'raggiezm3@gmail.com'; // Your Gmail address
        $mail->Password = 'ojey cqyx iwvr qrip'; // You need to generate an App Password from your Google Account
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('raggiezm3@gmail.com', 'BYTEMe');
        $mail->addAddress($to_email, $username);

        // Content
        $mail->isHTML(false);
        $mail->Subject = 'BYTEMe Email Verification';
        $mail->Body = "Welcome to BYTEMe!\n\n"
            . "Thank you for signing up. To complete your registration, please verify your email address.\n\n"
            . "Your verification code is: {$verification_code}\n\n"
            . "Please enter this code on the signup page to complete your registration.\n\n"
            . "Best regards,\nBYTEMe Team";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mail Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
