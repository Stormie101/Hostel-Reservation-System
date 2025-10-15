<?php

session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
  header("Location: ../login.html");
  exit();
}

require_once '../connect.php';

$student_id = $_GET['student_id'] ?? '';

$sql = "SELECT s.student_id, s.full_name, s.email, s.phone, s.gender, s.program, s.status AS student_status,
               r.room_id, r.status AS booking_status, rm.room_type
        FROM students s
        LEFT JOIN reservations r ON s.student_id = r.student_id
        LEFT JOIN rooms rm ON r.room_id = rm.room_id
        WHERE s.student_id = '$student_id'
        LIMIT 1";

$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Profile</title>
  <link rel="stylesheet" href="your_existing_styles.css" />
  <style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f7fa;
}

.header {
  background-color: #004aad;
  color: white;
  padding: 20px 40px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.header img {
  height: 70px;
  width: auto;
}

.header-title {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  font-size: 22px;
  font-weight: bold;
}

.logout-btn {
  background-color: #fff;
  color: #004aad;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

.logout-btn:hover {
  background-color: #e0e0e0;
}

.dashboard {
  display: flex;
  min-height: calc(100vh - 80px);
}

.sidebar {
  width: 250px;
  background-color: #e4e6eb;
  color: black;
  padding: 30px 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.menu-item {
  font-size: 16px;
  font-weight: 500;
  padding: 10px 16px;
  border-radius: 6px;
  transition: background-color 0.2s ease;
}

.menu-item:hover {
  background-color: #d0d3d8;
}

.main-content {
  flex-grow: 1;
  padding: 40px;
}


.profile-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;
  min-height: calc(100vh - 80px);
}

.profile-card {
  background-color: #fff;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  max-width: 700px;
  background-color: #f9f9f9;
  width: 100%;
  transition: box-shadow 0.3s ease;
}

.profile-card:hover {
  box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

.profile-title {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 30px;
  color: #004aad;
  text-align: center;
  border-bottom: 2px solid #eee;
  padding-bottom: 10px;
}

.profile-info {
  font-size: 16px;
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid #f0f0f0;
  padding-bottom: 8px;
}

.label {
  font-weight: 600;
  color: #333;
}

.value {
  color: #555;
  text-align: right;
}

  </style>
</head>
<body>
  <div class="header">
    <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
    <div class="header-title">STUDENT PROFILE</div>
    <button class="logout-btn" onclick="window.location.href='logout.php'">LOG OUT</button>
  </div>

  <div class="dashboard">
    <div class="sidebar">
      <a href="staff_index.php" style="text-decoration: none; color: black;"><div class="menu-item">DASHBOARD</div></a>
      <a href="manage_student.php" style="text-decoration: none; color: black;"><div class="menu-item">STUDENT BOOKING</div></a>
      <a href="manage_room.php" style="text-decoration: none; color: black;"><div class="menu-item">MANAGE ROOM</div></a>
      <a href="verify_staff.php" style="text-decoration: none; color: black;"><div class="menu-item">MANAGE STAFF</div></a>
    </div>

    <div class="main-content">
      <div class="profile-container">
        <div class="profile-card">
          <div class="profile-title">Student Details</div>

          <div class="profile-info"><span class="label">Student ID:</span> <?= $data['student_id'] ?></div>
          <div class="profile-info"><span class="label">Full Name:</span> <?= $data['full_name'] ?></div>
          <div class="profile-info"><span class="label">Email:</span> <?= $data['email'] ?></div>
          <div class="profile-info"><span class="label">Phone:</span> <?= $data['phone'] ?></div>
          <div class="profile-info"><span class="label">Gender:</span> <?= $data['gender'] ?></div>
          <div class="profile-info"><span class="label">Program:</span> <?= $data['program'] ?></div>
          <div class="profile-info"><span class="label">Application Status:</span> <?= $data['student_status'] ?></div>

          <hr style="margin: 20px 0;" />

          <div class="profile-title">Room & Booking Info</div>
          <div class="profile-info"><span class="label">Room ID:</span> <?= $data['room_id'] ?? 'Not Assigned' ?></div>
          <div class="profile-info"><span class="label">Room Type:</span> <?= $data['room_type'] ?? 'N/A' ?></div>
          <div class="profile-info"><span class="label">Booking Status:</span> <?= $data['booking_status'] ?? 'No Booking' ?></div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
