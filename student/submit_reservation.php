<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id']) || !isset($_SESSION['student_username'])) {
  header("Location: ../login.html");
  exit();
}

require_once '../connect.php';

$studentID = $_SESSION['student_id'];
$studentName = $_SESSION['student_username'];
$roomID = $_POST['selected_room'] ?? '';
$checkIn = $_POST['check_in'] ?? '';
$checkOut = $_POST['check_out'] ?? '';

if (!$roomID || !$checkIn || !$checkOut) {
  echo "<script>alert('Missing reservation details'); window.location.href='make_reservation.php';</script>";
  exit();
}

// Get student_id
$sqlStudent = "SELECT student_id FROM students WHERE full_name = ?";
$stmtStudent = $conn->prepare($sqlStudent);
$stmtStudent->bind_param("s", $studentName);
$stmtStudent->execute();
$resultStudent = $stmtStudent->get_result();

if ($resultStudent->num_rows !== 1) {
  echo "<script>alert('Student not found'); window.location.href='make_reservation.php';</script>";
  exit();
}

$studentID = $resultStudent->fetch_assoc()['student_id'];
$stmtStudent->close();

// Insert reservation
$sqlInsert = "INSERT INTO reservations (student_id, room_id, check_in, check_out, status) VALUES (?, ?, ?, ?, 'Pending')";
$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bind_param("ssss", $studentID, $roomID, $checkIn, $checkOut);

if ($stmtInsert->execute()) {
  // Mark room as occupied
  $sqlUpdate = "UPDATE rooms SET is_occupied = 1 WHERE room_id = ?";
  $stmtUpdate = $conn->prepare($sqlUpdate);
  $stmtUpdate->bind_param("s", $roomID);
  $stmtUpdate->execute();
  $stmtUpdate->close();

  echo "<script>alert('Reservation submitted successfully'); window.location.href='student_index.php';</script>";
} else {
  echo "<script>alert('Reservation failed'); window.location.href='make_reservation.php';</script>";
}

$stmtInsert->close();
$conn->close();
?>
