<?php
include 'db_connection.php';

$token = $_GET['token'] ?? '';
$decoded = explode('|', base64_decode($token));
$email = $decoded[0] ?? '';
$timestamp = $decoded[1] ?? 0;

// Optional: Check if token is expired (e.g. older than 15 minutes)
if (time() - $timestamp > 900) {
    die("Token expired. Please request a new reset link.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $email = $_POST['email'];

    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('Passwords do not match');</script>";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE staff SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();

        echo "<script>alert('Password reset successful'); window.location.href='login.html';</script>";
    }
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Reset Password</title>
</head>
<body>
  <form method="POST">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>" />
    <input type="password" name="password" placeholder="New Password" required />
    <input type="password" name="confirm_password" placeholder="Confirm Password" required />
    <button type="submit">Reset Password</button>
  </form>
</body>
</html>
