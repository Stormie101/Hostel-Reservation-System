<?php

session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
  header("Location: ../login.html");
  exit();
}

require_once '../connect.php';

$roomId = $_GET['room_id'] ?? null;
if (!$roomId) exit('Invalid room ID');

$sql = "SELECT room_id, room_number, room_type, is_occupied FROM rooms WHERE room_id = '$roomId' LIMIT 1";
$result = mysqli_query($conn, $sql);
$room = mysqli_fetch_assoc($result);
$studentSql = "SELECT s.student_id, s.full_name, s.program, s.email, s.phone
               FROM reservations r
               JOIN students s ON r.student_id = s.student_id
               WHERE r.room_id = '$roomId' AND r.status = 'Success'
               LIMIT 1";

$studentResult = mysqli_query($conn, $studentSql);
$student = mysqli_fetch_assoc($studentResult);

if (!$room) exit('Room not found');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Room Details</title>
  <style>
    /* Reuse your new design standard */
    * { box-sizing: border-box; margin: 0; padding: 0; }
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
    .header img { height: 70px; width: auto; }
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
    .logout-btn:hover { background-color: #e0e0e0; }
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
    .menu-item:hover { background-color: #d0d3d8; }
    .main-content {
      flex-grow: 1;
      padding: 40px;
    }
    .section-title {
      font-size: 22px;
      font-weight: bold;
      margin-bottom: 20px;
      color: #004aad;
    }
    .room-card {
      background-color: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      max-width: 600px;
    }
    .room-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 16px;
      font-size: 16px;
    }
    .room-label {
      font-weight: bold;
      color: #555;
    }
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 13px;
      display: inline-block;
    }
    .status-occupied {
      background-color: #f8d7da;
      color: #721c24;
    }
    .status-available {
      background-color: #d4edda;
      color: #155724;
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
    <div class="header-title">ROOM DETAILS</div>
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
      <div class="section-title">Room Information</div>
      <div class="room-card">
        <div class="room-row">
          <div class="room-label">Room Number:</div>
          <div><?= $room['room_number'] ?></div>
        </div>
        <div class="room-row">
          <div class="room-label">Room Type:</div>
          <div><?= $room['room_type'] ?></div>
        </div>
        <div class="room-row">
          <div class="room-label">Occupancy Status:</div>
          <div>
            <span class="status-badge <?= $room['is_occupied'] ? 'status-occupied' : 'status-available' ?>">
              <?= $room['is_occupied'] ? 'Occupied' : 'Available' ?>
            </span>
          </div>
        </div>
        <?php if ($student): ?>
  <div class="section-title">Assigned Student</div>
  <div class="room-card">
    <div class="room-row">
      <div class="room-label">Student ID:</div>
      <div><?= $student['student_id'] ?></div>
    </div>
    <div class="room-row">
      <div class="room-label">Name:</div>
      <div><?= $student['full_name'] ?></div>
    </div>
    <div class="room-row">
      <div class="room-label">Program:</div>
      <div><?= $student['program'] ?></div>
    </div>
    <div class="room-row">
      <div class="room-label">Email:</div>
      <div><?= $student['email'] ?></div>
    </div>
    <div class="room-row">
      <div class="room-label">Phone:</div>
      <div><?= $student['phone'] ?></div>
    </div>
  </div>
<?php else: ?>
  <div class="section-title">Assigned Student</div>
  <p>No student is currently assigned to this room.</p>
<?php endif; ?>

      </div>
    </div>
  </div>
</body>
</html>
