<?php
session_start();

if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
  header("Location: ../login.html");
  exit();
}

require_once '../connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $room_number = $_POST['room_number'];
  $room_type = $_POST['room_type'];
  $block = $_POST['block'];
  $is_occupied = 0;

  $check = $conn->prepare("SELECT room_id FROM rooms WHERE room_number = ?");
  $check->bind_param("s", $room_number);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    $error = "Room number already exists. Please choose a different number.";
  } else {
    $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, block, is_occupied) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $room_number, $room_type, $block, $is_occupied);
    $stmt->execute();

    echo "<script>alert('Room added successfully'); window.location.href='manage_room.php';</script>";
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add New Room</title>
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

    .section-title {
      font-size: 22px;
      font-weight: bold;
      margin-bottom: 20px;
      color: #004aad;
    }

    .form-container {
      background-color: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      max-width: 500px;
      margin: auto;
    }

    .form-container input,
    .form-container select {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
    }

    .submit-btn {
      background-color: #004aad;
      color: white;
      padding: 12px;
      width: 100%;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 20px;
    }

    .submit-btn:hover {
      background-color: #003080;
    }

    .error-msg {
      color: red;
      font-weight: bold;
      margin-bottom: 15px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
    <div class="header-title">ADD NEW ROOM</div>
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
      <div class="section-title">Create Room Entry</div>
      <div class="form-container">
        <?php if ($error): ?>
          <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
          <input type="text" name="room_number" placeholder="Room Number" required />
          <select name="room_type" required>
            <option value="">Select Room Type</option>
            <option value="Single">Single</option>
            <option value="Double">Double</option>
            <option value="Block">Block</option>
          </select>
          <input type="text" name="block" placeholder="Block (e.g. A, B, C)" required />
          <button type="submit" class="submit-btn">Add Room</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
