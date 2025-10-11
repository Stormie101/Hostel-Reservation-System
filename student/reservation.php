<?php
session_start();
require_once '../connect.php';

// Ensure only students can access
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Student') {
  header("Location: ../login.html");
  exit();
}

$studentName = $_SESSION['username'];

// Get student_id from students table
$sqlID = "SELECT student_id FROM students WHERE full_name = ?";
$stmtID = $conn->prepare($sqlID);
$stmtID->bind_param("s", $studentName);
$stmtID->execute();
$resultID = $stmtID->get_result();

$studentID = null;
if ($resultID->num_rows === 1) {
  $studentID = $resultID->fetch_assoc()['student_id'];
}
$stmtID->close();

// Fetch available rooms grouped by type
$availableRooms = [
  'Single' => [],
  'Double' => [],
  'Block' => []
];

$sqlRooms = "SELECT room_id, room_number, room_type, block FROM rooms WHERE is_occupied = 0";
$resultRooms = $conn->query($sqlRooms);

while ($row = $resultRooms->fetch_assoc()) {
  $type = $row['room_type'];
  if (isset($availableRooms[$type])) {
    $availableRooms[$type][] = $row;
  }
}

// Fetch reservation details for this student
$reservations = [];
if ($studentID) {
  $sqlRes = "SELECT r.room_id, r.check_in, r.check_out, r.status, rm.room_type 
             FROM reservations r 
             JOIN rooms rm ON r.room_id = rm.room_id 
             WHERE r.student_id = ?";
  $stmtRes = $conn->prepare($sqlRes);
  $stmtRes->bind_param("s", $studentID);
  $stmtRes->execute();
  $resultRes = $stmtRes->get_result();

  while ($row = $resultRes->fetch_assoc()) {
    $reservations[] = [
      'room_id' => $row['room_id'],
      'check_in' => $row['check_in'],
      'check_out' => $row['check_out'],
      'status' => $row['status'],
      'room_type' => $row['room_type']
    ];
  }

  $stmtRes->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reservation Page</title>
<style>
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #004aad;
  }

  .room-buttons {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
  }

  .room-buttons button {
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    background-color: #004aad;
    color: white;
    cursor: pointer;
  }

  .room-buttons button:hover {
    background-color: #003080;
  }

  .input-group {
    margin-bottom: 30px;
  }

  .input-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
  }

  .input-group input {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 6px;
  }

  .date-group {
    display: flex;
    gap: 20px;
  }

  .date-group .input-group {
    flex: 1;
  }
  .booking-cards {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.booking-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #fff;
  border-radius: 12px;
  padding: 20px 30px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: box-shadow 0.2s ease;
}

.booking-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.booking-left {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.student-id {
  font-weight: bold;
  color: #004aad;
  font-size: 16px;
}

.student-name {
  font-size: 18px;
  font-weight: 600;
}

.room-type {
  font-size: 14px;
  color: #555;
}

</style>

<body>
  <div class="header">
    <img src="../IMG/uptm logo.png" alt="UPTM Logo">
    <div class="header-title">RESERVATION PAGE</div>
    <button class="logout-btn" onclick="window.location.href='logout.php'">LOG OUT</button>
  </div>

  <div class="dashboard">
    <div class="sidebar">
      <a href="student_index.php" style="text-decoration: none; color: black;"><div class="menu-item">DASHBOARD</div></a>
      <a href="reservation.php" style="text-decoration: none; color: black;"><div class="menu-item">MAKE RESERVATION</div></a>
    </div>

    <div class="main-content">
      <!-- Your reservation form and content goes here -->
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
  <input type="text" id="searchInput" placeholder="Search by Room ID, Status..." style="padding: 10px; width: 60%; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;" />

  <select id="roomTypeFilter" style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;">
    <option value="all">Filter by Room Type</option>
    <option value="Single">Single</option>
    <option value="Double">Double</option>
    <option value="Block">Block</option>
  </select>
</div>

      <!-- table -->
<form action="submit_reservation.php" method="POST">
  <div class="section-title">Available Rooms</div>
  <div class="booking-cards">
    <?php foreach ($availableRooms as $type => $rooms): ?>
      <h3 style="margin-top: 20px; color: #004aad;"><?= $type ?> Rooms</h3>
      <?php if (empty($rooms)): ?>
        <p style="margin-bottom: 20px;">No available <?= strtolower($type) ?> rooms.</p>
      <?php else: ?>
        <?php foreach ($rooms as $room): ?>
          <label class="booking-card" data-room-type="<?= $room['room_type'] ?>" style="cursor: pointer;">
            <div class="booking-left">
              <div class="student-id">Room ID: <?= $room['room_id'] ?></div>
              <div class="student-name">Room Number: <?= $room['room_number'] ?></div>
              <div class="room-type">Block: <strong><?= $room['block'] ?></strong></div>
            </div>
            <div class="booking-right">
              <input type="radio" name="selected_room" value="<?= $room['room_id'] ?>" required />
            </div>
          </label>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
          <br>
  <div class="section-title">Confirmation Dates</div>
  <div class="date-group">
    <div class="input-group">
      <label>From</label>
      <input type="date" name="check_in" required />
    </div>
    <div class="input-group">
      <label>To</label>
      <input type="date" name="check_out" required />
    </div>
  </div>

  <button type="submit" style="margin-top: 30px; padding: 12px 24px; background-color: #004aad; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer;">Book Room</button>
</form>

    </div>
  </div>
  <script>
  const searchInput = document.getElementById('searchInput');
  const roomTypeFilter = document.getElementById('roomTypeFilter');
  const cards = document.querySelectorAll('.booking-card');

  function filterCards() {
    const query = searchInput.value.toLowerCase();
    const selectedType = roomTypeFilter.value;

    cards.forEach(card => {
      const text = card.textContent.toLowerCase();
      const type = card.getAttribute('data-room-type');

      const matchesSearch = text.includes(query);
      const matchesType = (selectedType === 'all') || (type === selectedType);

      card.style.display = (matchesSearch && matchesType) ? 'flex' : 'none';
    });
  }

  searchInput.addEventListener('input', filterCards);
  roomTypeFilter.addEventListener('change', filterCards);
</script>

</body>

</html>
