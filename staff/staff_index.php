<?php

session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
  header("Location: ../login.html");
  exit();
}

require_once '../connect.php';


// Rooms Available
$sqlRooms = "SELECT COUNT(*) AS available FROM rooms WHERE is_occupied = 0";
$resultRooms = mysqli_query($conn, $sqlRooms);
$availableRooms = mysqli_fetch_assoc($resultRooms)['available'];

// Students On Hold
$sqlPending = "SELECT COUNT(*) AS pending FROM reservations WHERE status = 'Pending'";
$resultPending = mysqli_query($conn, $sqlPending);
$studentsOnHold = mysqli_fetch_assoc($resultPending)['pending'];

// Successful Reservations
$sqlSuccess = "SELECT COUNT(*) AS success FROM reservations WHERE status = 'Success'";
$resultSuccess = mysqli_query($conn, $sqlSuccess);
$successfulReservations = mysqli_fetch_assoc($resultSuccess)['success'];
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Dashboard</title>
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

       .stats-boxes {
        display: flex;
        gap: 30px;
        margin-bottom: 40px;
      }

      .stat-box {
        flex: 1;
        background-color: #f2f2f2;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
      }

      .stat-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #004aad;
      }

      .stat-value {
        font-size: 32px;
        font-weight: bold;
        color: #000;
      }
    </style>
  </head>
  <body>
    <div class="header">
      <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
      <div class="header-title">STAFF DASHBOARD</div>
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
        <!-- Replace the comment inside .main-content with this -->
        <div class="stats-boxes">
          <div class="stat-box">
            <div class="stat-title">Rooms Available</div>
            <div class="stat-value"><?= $availableRooms ?></div>
          </div>
          <div class="stat-box">
            <div class="stat-title">Students On Hold</div>
            <div class="stat-value"><?= $studentsOnHold ?></div>
          </div>
          <div class="stat-box">
            <div class="stat-title">Successful Reservations</div>
            <div class="stat-value"><?= $successfulReservations ?></div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
