<?php
include '../connect.php';
require_once 'send_2fa_email.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  session_start();
  session_regenerate_id(true);

  // Check against staff table
  $sqlStaff = "SELECT * FROM staff WHERE username=? AND email=?";
  $stmt = $conn->prepare($sqlStaff);
  $stmt->bind_param("ss", $username, $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    if (!password_verify($password, $row['password'])) {
      echo "<script>alert('Invalid password'); window.location.href='../login.html';</script>";
      exit();
    }

    if ($row['account_status'] !== 'Active') {
      echo "<script>alert('Your account is not active. Please contact admin.'); window.location.href='../login.html';</script>";
      exit();
    }

    // Generate 2FA code and store in session
    $code = rand(100000, 999999);
    $_SESSION['pending_2fa'] = [
      'user_type' => 'Staff',
      'id' => $row['staff_id'],
      'username' => $row['username'],
      'email' => $row['email'],
      'account_status' => $row['account_status'],
      'code' => $code
    ];

    send2FACode($row['email'], $row['username'], $code);
    header("Location: 2fa.php");
    exit();
  }

  // Check against students table
  $sqlStudent = "SELECT * FROM students WHERE full_name=? AND email=?";
  $stmt = $conn->prepare($sqlStudent);
  $stmt->bind_param("ss", $username, $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    if (!isset($row['password']) || !password_verify($password, $row['password'])) {
      echo "<script>alert('Invalid password'); window.location.href='../login.html';</script>";
      exit();
    }

    // Generate 2FA code and store in session
    $code = rand(100000, 999999);
    $_SESSION['pending_2fa'] = [
      'user_type' => 'Student',
      'id' => $row['student_id'],
      'username' => $row['full_name'],
      'email' => $row['email'],
      'code' => $code
    ];

    send2FACode($row['email'], $row['full_name'], $code);
    header("Location: 2fa.php");
    exit();
  }

  // If no match found
  echo "<script>alert('Invalid login credentials'); window.location.href='../login.html';</script>";
  $stmt->close();
}

$conn->close();
?>
