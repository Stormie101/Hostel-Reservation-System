<?php
include '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Check against staff table
  $sql = "SELECT * FROM staff WHERE username=? AND email=?";
$stmt = $conn->prepare($sql);
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

  session_start();
  $_SESSION['username'] = $username;
  $_SESSION['role'] = 'Staff';
  header("Location: ../staff/staff_index.php");
  exit();
}

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $username, $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
      session_start();
      $_SESSION['username'] = $username;
      $_SESSION['role'] = 'Staff';
      header("Location: ../staff/staff_index.php");
      exit();
    }
  }

  // If not staff, check against students table
  $sql = "SELECT * FROM students WHERE full_name=? AND email=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $username, $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (isset($row['password']) && password_verify($password, $row['password'])) {
      session_start();
      $_SESSION['username'] = $username;
      $_SESSION['role'] = 'Student';
      header("Location: ../student/student_index.php");
      exit();
    }
  }

  echo "<script>alert('Invalid login credentials'); window.location.href='../login.html';</script>";
  $stmt->close();
}

$conn->close();
?>
