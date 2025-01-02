<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Retrieve POST data
$email = $_POST['email'] ?? '';
$fname = $_POST['first_name'] ?? '';
$mname = $_POST['middle_name'] ?? '';
$lname = $_POST['last_name'] ?? '';
$otp = $_POST['otp'] ?? '';

if (empty($email) || empty($fname) || empty($mname) || empty($lname) || empty($otp)) {
    echo "Error: Missing required fields.";
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
    $mail->SMTPAuth = true;
    $mail->Username = 'catalanwilfredo97@gmail.com'; // SMTP username
    $mail->Password = 'sykmmtpojmudqbik'; // SMTP password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('mr.daotz97@gmail.com', 'SmartSpot');
    $mail->addAddress($email, "$fname $mname $lname");

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'OTP Confirmation Code';
    $mail->Body = "
    Good day Mr/Mrs. $fname $mname $lname,<br><br>
    Your OTP code is: <b>$otp</b><br><br>
    <p style='font-size: 14px; color: #666;'>Please do not share this OTP with anyone. For your security, this code is intended for your use only and should remain confidential.</p>";

    $mail->send();
    echo 'OTP has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
