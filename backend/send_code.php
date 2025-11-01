<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Adjust path if needed
include '../connect.php'; // Your DB connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if email exists and is active
    $stmt = $conn->prepare("SELECT * FROM staff WHERE email = ? AND account_status = 'Active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Generate 6-digit code
        $code = rand(100000, 999999);

        // Store in session
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_code'] = $code;

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'stormie8work@gmail.com'; 
            $mail->Password = 'eyvj bsdh ihmk zypf';    
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('stormie8work@gmail.com', 'UPTM Hostel System');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset Code';
            $mail->Body = "<p>Your verification code is: <strong>$code</strong></p>";

            $mail->send();
            header("Location: verify_code.php");
            exit;
        } catch (Exception $e) {
            echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "<script>alert('Email not found or inactive.'); window.location.href='../forgot.html';</script>";
    }
}
?>
