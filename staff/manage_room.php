<?php

session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
  header("Location: ../login.html");
  exit();
}

require_once '../connect.php';

$sql = "SELECT room_id, room_number, room_type, is_occupied FROM rooms";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Room</title>
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

table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 10px;
}

th {
  background-color: #004aad;
  color: white;
  padding: 14px;
  font-size: 15px;
  border-radius: 6px 6px 0 0;
}

td {
  background-color: white;
  padding: 14px;
  font-size: 14px;
  text-align: center;
  border-bottom: 1px solid #ddd;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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

.action-group {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.action-btn {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
}

.view-btn {
  background-color: #004aad;
  color: white;
}

.edit-btn {
  background-color: #6c757d;
  color: white;
}

.action-btn:hover {
  opacity: 0.9;
}

  </style>
</head>
<body>
  <div class="header">
    <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
    <div class="header-title">MANAGE ROOM</div>
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
      <div class="section-title">Room Overview</div>

      <table>
        <thead>
          <tr>
            <th>Room Number</th>
            <th>Room Type</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
<?php while ($room = mysqli_fetch_assoc($result)): ?>
<tr>
  <td><?= $room['room_number'] ?></td>
  <td><?= $room['room_type'] ?></td>
  <td class="<?= $room['is_occupied'] ? 'status-occupied' : 'status-available' ?>">
    <?= $room['is_occupied'] ? 'Occupied' : 'Available' ?>
  </td>
  <td>
    <div class="action-group">
      <a href="view_room.php?room_id=<?= $room['room_id'] ?>">
        <button class="action-btn view-btn">View Details</button>
      </a>
      <a href="edit_room.php?room_id=<?= $room['room_id'] ?>">
        <button class="action-btn edit-btn">Update/Edit</button>
      </a>
    </div>
  </td>
</tr>
<?php endwhile; ?>

        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
