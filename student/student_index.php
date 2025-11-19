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

// --- MODIFIED: Get student's details for the stat boxes ---
$studentDetails = null;
$sqlStudent = "SELECT program, gender, email FROM students WHERE student_id = ?";
$stmtStudent = $conn->prepare($sqlStudent);
$stmtStudent->bind_param("s", $studentID);
$stmtStudent->execute();
$resultStudent = $stmtStudent->get_result();

if ($resultStudent->num_rows === 1) {
    $studentDetails = $resultStudent->fetch_assoc();
}
$stmtStudent->close();
// --- End of MODIFIED ---


// Get reservation details
$reservations = [];
$sqlRes = "SELECT room_id, check_in, check_out, status FROM reservations WHERE student_id = ?";
$stmtRes = $conn->prepare($sqlRes);
$stmtRes->bind_param("s", $studentID);
$stmtRes->execute();
$resultRes = $stmtRes->get_result();

while ($row = $resultRes->fetch_assoc()) {
    $reservations[] = $row;
}
$stmtRes->close();

// --- NEW: Variable for reservation status ---
$reservationStatus = "No Reservation";
if (!empty($reservations)) {
    // This will show the status of their first reservation (e.g., "Pending" or "Active")
    $reservationStatus = htmlspecialchars($reservations[0]['status']);
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
      background-color: #73acf7ff;
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
      background-color: #ffffff; /* Changed to white */
      border: 1px solid #ddd; /* Lighter border */
      border-radius: 12px; /* More rounded */
      padding: 20px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); /* Softer shadow */
    }

    .stat-title {
      font-size: 16px; /* Slightly smaller */
      font-weight: 600; /* Bolded */
      margin-bottom: 10px;
      color: #004aad;
    }

    .stat-value {
      font-size: 28px; /* Adjusted size */
      font-weight: bold;
      color: #333;
    }

    /* --- NEW CSS for the "No Reservation" box --- */
    .no-reservation-box {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        margin-top: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .no-reservation-box h3 {
        font-size: 22px;
        color: #004aad;
        margin-bottom: 15px;
    }

    .no-reservation-box p {
        font-size: 16px;
        color: #555;
        margin-bottom: 25px;
    }

    .make-reservation-btn {
        display: inline-block;
        background-color: #004aad;
        color: #fff;
        padding: 12px 25px;
        font-size: 16px;
        font-weight: bold;
        text-decoration: none;
        border-radius: 8px;
        transition: background-color 0.2s ease;
    }

    .make-reservation-btn:hover {
        background-color: #003a8c;
    }
    /* --- End of NEW CSS --- */


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
      <h2 style="color: #004aad; font-weight: bold; margin-bottom: 30px;">Welcome <?= htmlspecialchars($studentName) ?>, to the Hostel Reservation System</h2>

      <div class="stats-boxes">
        <div class="stat-box">
            <div class="stat-title">Reservation Status</div>
            <div class="stat-value"><?= $reservationStatus ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-title">Student ID</div>
            <div class="stat-value"><?= htmlspecialchars($studentID) ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-title">Programme</div>
            <div class="stat-value">
                <?= $studentDetails ? htmlspecialchars($studentDetails['program']) : 'N/A' ?>
            </div>
        </div>
      </div>
      <?php if (empty($reservations)): ?>
        <div class="no-reservation-box">
            <h3>You don't have a reservation yet.</h3>
            <p>Ready to find your room? Click the button below to start the process.</p>
            <a href="reservation.php" class="make-reservation-btn">Make a Reservation</a>
        </div>
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
            <td><?= htmlspecialchars($res['room_id']) ?></td>
            <td><?= htmlspecialchars($res['check_in']) ?></td>
            <td><?= htmlspecialchars($res['check_out']) ?></td>
            <td>
              <?= htmlspecialchars($res['status']) ?>
              <?php if ($res['status'] === 'Pending'): ?>
                <form method="POST" action="cancel_reservation.php" style="margin-top: 8px;">
                  <input type="hidden" name="room_id" value="<?= htmlspecialchars($res['room_id']) ?>">
                  <input type="hidden" name="student_id" value="<?= htmlspecialchars($studentID) ?>">
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