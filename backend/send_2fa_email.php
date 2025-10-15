


<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // or manual includes if not using Composer

function send2FACode($recipientEmail, $recipientName, $code) {
  $mail = new PHPMailer(true);

  try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = 'stormie8work@gmail.com';
    $mail->Password   = 'ydtz gciv yicb haic'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('stormie8work@gmail.com', 'UPTM Hostel System');
    $mail->addAddress($recipientEmail, $recipientName);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your 2FA Verification Code';
    $mail->Body    = "<p>Hello <strong>$recipientName</strong>,<br>Your verification code is: <strong>$code</strong></p>";

    $mail->send();
    return true;
  } catch (Exception $e) {
    return false;
  }
}
