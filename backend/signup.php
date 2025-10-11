<?php
include '../connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirm = $_POST['confirm_password'];
  $role = $_POST['role'];

  if ($password !== $confirm) {
    echo "<script>alert('Passwords do not match'); window.location.href='signup.html';</script>";
    exit();
  }

  $hashed = password_hash($password, PASSWORD_DEFAULT);

  if ($role === 'Staff') {
$accountStatus = 'Inactive';
$sql = "INSERT INTO staff (username, email, password, role, account_status) VALUES (?, ?, ?, 'Staff', ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $email, $hashed, $accountStatus);


  } elseif ($role === 'Student') {
    // Collect student-specific fields
    $studentID = $_POST['student_id'] ?? '';
    $programme = $_POST['programme'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $status = 'Accepted'; // default status

    $sql = "INSERT INTO students (student_id, full_name, email, password, program, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $studentID, $username, $email, $hashed, $programme, $gender, $status);

  } else {
    echo "<script>alert('Invalid role selected'); window.location.href='signup.html';</script>";
    exit();
  }

  if ($stmt->execute()) {
    echo "<script>alert('Account created successfully'); window.location.href='../login.html';</script>";
  } else {
    echo "<script>alert('Signup failed'); window.location.href='../signup.html';</script>";
  }

  $stmt->close();
}
$conn->close();
?>
