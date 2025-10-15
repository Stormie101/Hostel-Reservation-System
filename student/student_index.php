<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id']) || !isset($_SESSION['student_username'])) {
  header("Location: ../login.html");
  exit();
}

$studentID = $_SESSION['student_id'];
$studentName = $_SESSION['student_username'];

require_once '../connect.php';

// Get student_id from students table
$sqlID = "SELECT student_id FROM students WHERE full_name = ?";
$stmtID = $conn->prepare($sqlID);
$stmtID->bind_param("s", $studentName);
$stmtID->execute();
$resultID = $stmtID->get_result();

if ($resultID->num_rows === 1) {
  $studentID = $resultID->fetch_assoc()['student_id'];
} else {
  $studentID = null;
}
$stmtID->close();

// Get reservation details
$reservations = [];
if ($studentID) {
  $sqlRes = "SELECT room_id, check_in, check_out, status FROM reservations WHERE student_id = ?";
  $stmtRes = $conn->prepare($sqlRes);
  $stmtRes->bind_param("s", $studentID);
  $stmtRes->execute();
  $resultRes = $stmtRes->get_result();

  while ($row = $resultRes->fetch_assoc()) {
    $reservations[] = $row;
  }

  $stmtRes->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Dashboard</title>
  <style>
    /* Same styling as staff page */
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
    table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin-top: 20px;
  background-color: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

table th {
  background-color: #004aad;
  color: white;
  font-weight: 600;
  padding: 16px;
  font-size: 15px;
  text-align: center;
}

table td {
  padding: 14px;
  font-size: 14px;
  text-align: center;
  border-bottom: 1px solid #eee;
}

table tr:nth-child(even) td {
  background-color: #f9f9f9;
}

table tr:hover td {
  background-color: #eef3fc;
  transition: background-color 0.2s ease;
}

.cancel-btn {
  padding: 6px 12px;
  background-color: #d9534f;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
}

.cancel-btn:hover {
  background-color: #c9302c;
}

  </style>
</head>
<body>
  <div class="header">
    <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
    <div class="header-title">STUDENT DASHBOARD</div>
    <button class="logout-btn" onclick="window.location.href='logout.php'">LOG OUT</button>
  </div>

  <div class="dashboard">
    <div class="sidebar">
      <a href="student_index.php" style="text-decoration: none; color: black;"><div class="menu-item">DASHBOARD</div></a>
      <a href="reservation.php" style="text-decoration: none; color: black;"><div class="menu-item">MAKE RESERVATION</div></a>
    </div>
 <div class="main-content">
  <h2 style="color: #004aad; font-weight: bold;">Welcome <?= htmlspecialchars($studentName) ?>, to the Hostel Reservation System</h2>

  <div class="stats-boxes">
    <!-- You can add reservation status or other content here -->
  </div>

  <?php if (empty($reservations)): ?>
    <p>You have no reservation</p>
  <?php else: ?>
    <h2>Your Reservation Status</h2>
    <table style="width:100%; border-collapse: collapse; margin-top: 20px;">
      <tr style="background-color: #e4e6eb;">
        <th style="padding: 10px; border: 1px solid #ccc;">Room ID</th>
        <th style="padding: 10px; border: 1px solid #ccc;">Check-In</th>
        <th style="padding: 10px; border: 1px solid #ccc;">Check-Out</th>
        <th style="padding: 10px; border: 1px solid #ccc;">Status</th>
      </tr>
<?php foreach ($reservations as $res): ?>
  <tr>
    <td><?= $res['room_id'] ?></td>
    <td><?= $res['check_in'] ?></td>
    <td><?= $res['check_out'] ?></td>
    <td>
      <?= $res['status'] ?>
      <?php if ($res['status'] === 'Pending'): ?>
        <form method="POST" action="cancel_reservation.php" style="margin-top: 8px;">
          <input type="hidden" name="room_id" value="<?= $res['room_id'] ?>">
          <input type="hidden" name="student_id" value="<?= $studentID ?>">
          <button type="submit" class="cancel-btn">Cancel</button>
        </form>
      <?php endif; ?>
    </td>
  </tr>
<?php endforeach; ?>

    </table>
  <?php endif; ?>
</div>

  </div>
</body>
</html>
