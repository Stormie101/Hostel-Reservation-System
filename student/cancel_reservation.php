<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $roomID = $_POST['room_id'] ?? '';
  $studentID = $_POST['student_id'] ?? '';

  // Delete reservation
  $sqlDelete = "DELETE FROM reservations WHERE room_id = ? AND student_id = ? AND status = 'Pending'";
  $stmtDelete = $conn->prepare($sqlDelete);
  $stmtDelete->bind_param("ss", $roomID, $studentID);
  $stmtDelete->execute();
  $stmtDelete->close();

  // Mark room as available
  $sqlUpdate = "UPDATE rooms SET is_occupied = 0 WHERE room_id = ?";
  $stmtUpdate = $conn->prepare($sqlUpdate);
  $stmtUpdate->bind_param("s", $roomID);
  $stmtUpdate->execute();
  $stmtUpdate->close();

  echo "<script>alert('Reservation cancelled'); window.location.href='student_index.php';</script>";
}
?>
