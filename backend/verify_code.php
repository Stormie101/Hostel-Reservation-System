<?php
session_start();
include '../connect.php';

$step = 'verify'; // default step

// Handle code verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $enteredCode = $_POST['code'];
    if ($enteredCode == $_SESSION['reset_code']) {
        $step = 'reset'; // move to password reset step
    } else {
        echo "<script>alert('Invalid code');</script>";
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $email = $_SESSION['reset_email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match');</script>";
        $step = 'reset';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE staff SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed, $email);
        $stmt->execute();

        session_destroy();
        echo "<script>alert('Password reset successful'); window.location.href='../login.html';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Verify Code</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f2f2f2;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      background-color: #fff;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .title {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 20px;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
    }

    .btn {
      background-color: #000;
      color: #fff;
      padding: 12px;
      width: 100%;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 20px;
    }

    .btn:hover {
      background-color: #333;
    }
  </style>
</head>
<body>
  <div class="container">
    <?php if ($step === 'verify'): ?>
      <div class="title">Enter Verification Code</div>
      <form method="POST">
        <input type="text" name="code" placeholder="6-digit Code" required />
        <button type="submit" class="btn">Verify</button>
      </form>
    <?php elseif ($step === 'reset'): ?>
      <div class="title">Reset Your Password</div>
      <form method="POST">
        <input type="password" name="password" placeholder="New Password" required />
        <input type="password" name="confirm_password" placeholder="Confirm Password" required />
        <button type="submit" class="btn">Reset Password</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
