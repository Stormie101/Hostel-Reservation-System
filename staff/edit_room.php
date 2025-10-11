<?php
require_once '../connect.php';

$roomId = $_GET['room_id'] ?? null;
if (!$roomId) exit('Invalid room ID');

$sql = "SELECT room_number, room_type, is_occupied FROM rooms WHERE room_id = '$roomId' LIMIT 1";
$result = mysqli_query($conn, $sql);
$room = mysqli_fetch_assoc($result);
if (!$room) exit('Room not found');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $room_number = $_POST['room_number'];
  $room_type = $_POST['room_type'];
  $is_occupied = $_POST['is_occupied'];

  $updateSql = "UPDATE rooms SET 
                  room_number = '$room_number',
                  room_type = '$room_type',
                  is_occupied = '$is_occupied'
                WHERE room_id = '$roomId'";
  mysqli_query($conn, $updateSql);
  header("Location: manage_room.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Room</title>
  <style>
    /* Reuse your design standard */
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
    .form-card {
      background-color: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      max-width: 600px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      display: block;
      font-weight: bold;
      margin-bottom: 8px;
      color: #333;
    }
    input, select {
      width: 100%;
      padding: 10px 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }
    .submit-btn {
      background-color: #004aad;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }
    .submit-btn:hover {
      background-color: #003a8c;
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
    <div class="header-title">EDIT ROOM</div>
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
      <div class="section-title">Update Room Information</div>
      <div class="form-card">
        <form method="POST">
          <div class="form-group">
            <label for="room_number">Room Number</label>
            <input type="text" name="room_number" id="room_number" value="<?= $room['room_number'] ?>" required />
          </div>
          <div class="form-group">
            <label for="room_type">Room Type</label>
            <select name="room_type" id="room_type" required>
              <option value="Single" <?= $room['room_type'] === 'Single' ? 'selected' : '' ?>>Single</option>
              <option value="Double" <?= $room['room_type'] === 'Double' ? 'selected' : '' ?>>Double</option>
              <option value="Triple" <?= $room['room_type'] === 'Triple' ? 'selected' : '' ?>>Triple</option>
            </select>
          </div>
          <div class="form-group">
            <label for="is_occupied">Occupancy Status</label>
            <select name="is_occupied" id="is_occupied" required>
              <option value="0" <?= $room['is_occupied'] == 0 ? 'selected' : '' ?>>Available</option>
              <option value="1" <?= $room['is_occupied'] == 1 ? 'selected' : '' ?>>Occupied</option>
            </select>
          </div>
          <button type="submit" class="submit-btn">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
