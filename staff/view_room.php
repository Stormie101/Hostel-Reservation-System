<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
    header("Location: ../login.html");
    exit();
}

require_once '../connect.php';

$roomId = $_GET['room_id'] ?? null;
if (!$roomId) {
    exit('Invalid room ID');
}

// SECURE: Use prepared statements to prevent SQL Injection
$stmt = $conn->prepare("SELECT room_id, room_number, room_type, is_occupied FROM rooms WHERE room_id = ? LIMIT 1");
$stmt->bind_param("i", $roomId);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();
$stmt->close();

if (!$room) {
    exit('Room not found');
}

// SECURE: Fetch student details using prepared statement
$studentSql = "SELECT s.student_id, s.full_name, s.program, s.email, s.phone
               FROM reservations r
               JOIN students s ON r.student_id = s.student_id
               WHERE r.room_id = ? AND r.status = 'Success'
               LIMIT 1";
$stmt = $conn->prepare($studentSql);
$stmt->bind_param("i", $roomId);
$stmt->execute();
$studentResult = $stmt->get_result();
$student = $studentResult->fetch_assoc();
$stmt->close();

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
      background-color: #73acf7ff;
      color: white;
      padding: 20px 40px;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 80px; /* Set consistent height */
    }
    .header img { 
        height: 40px; /* <-- LOGO FIX */
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
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 20px;
      color: #004aad;
    }
    
    /* NEW: Card styles for displaying info */
    .info-card {
      background-color: white;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      max-width: 700px;
      margin-bottom: 30px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: 200px 1fr; /* Label column and Value column */
        gap: 16px;
    }
    
    .info-item .label {
      font-weight: 600;
      color: #555;
      font-size: 15px;
    }
    
    .info-item .value {
      font-weight: 500;
      color: #111;
      font-size: 16px;
    }

    /* Re-using status badge styles */
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
    
    .back-btn {
        display: inline-block;
        background-color: #6c757d;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        margin-top: 20px;
    }
    .back-btn:hover {
        background-color: #5a6268;
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
      <div class="info-card">
        <div class="info-grid">
            <div class="info-item label">Room Number:</div>
            <div class="info-item value"><?= htmlspecialchars($room['room_number']) ?></div>

            <div class="info-item label">Room Type:</div>
            <div class="info-item value"><?= htmlspecialchars($room['room_type']) ?></div>

            <div class="info-item label">Occupancy Status:</div>
            <div class="info-item value">
                <span class="status-badge <?= $room['is_occupied'] ? 'status-occupied' : 'status-available' ?>">
                  <?= $room['is_occupied'] ? 'Occupied' : 'Available' ?>
                </span>
            </div>
        </div>
      </div>

      <!-- Conditionally show student info -->
      <?php if ($student): ?>
        <div class="section-title">Assigned Student</div>
        <div class="info-card">
            <div class="info-grid">
                <div class="info-item label">Student ID:</div>
                <div class="info-item value"><?= htmlspecialchars($student['student_id']) ?></div>
                
                <div class="info-item label">Name:</div>
                <div class="info-item value"><?= htmlspecialchars($student['full_name']) ?></div>
                
                <div class="info-item label">Program:</div>
                <div class="info-item value"><?= htmlspecialchars($student['program']) ?></div>
                
                <div class="info-item label">Email:</div>
                <div class="info-item value"><?= htmlspecialchars($student['email']) ?></div>
                
                <div class="info-item label">Phone:</div>
                <div class="info-item value"><?= htmlspecialchars($student['phone']) ?></div>
            </div>
        </div>
      <?php else: ?>
        <div class="section-title">Assigned Student</div>
        <div class="info-card">
            <p style="color: #555;">No student is currently assigned to this room.</p>
        </div>
      <?php endif; ?>
      
      <a href="manage_room.php" class="back-btn">Back to Room List</a>
      
    </div>
  </div>
</body>
</html>