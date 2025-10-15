<?php

session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
  header("Location: ../login.html");
  exit();
}
require_once '../connect.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_id'], $_POST['new_status'])) {
  $staffID = $_POST['staff_id'];
  $newStatus = $_POST['new_status'];

  $updateSQL = "UPDATE staff SET account_status = ? WHERE staff_id = ?";
  $stmt = $conn->prepare($updateSQL);
  $stmt->bind_param("si", $newStatus, $staffID);
  $stmt->execute();
  $stmt->close();

  echo "<script>alert('Status updated successfully'); window.location.href='verify_staff.php';</script>";
  exit();
}

// Fetch all staff accounts
$sql = "SELECT staff_id, username, email, role, account_status FROM staff";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify Staff Accounts</title>
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

    h2 {
      color: #004aad;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background-color: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    th, td {
      padding: 14px;
      text-align: center;
      border-bottom: 1px solid #eee;
    }

    th {
      background-color: #004aad;
      color: white;
      font-weight: 600;
    }

    tr:nth-child(even) td {
      background-color: #f9f9f9;
    }

    tr:hover td {
      background-color: #eef3fc;
    }

    .status-active {
      color: green;
      font-weight: bold;
    }

    .status-inactive {
      color: red;
      font-weight: bold;
    }

    .action-btn {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      color: white;
    }

    .activate {
      background-color: #28a745;
    }

    .deactivate {
      background-color: #dc3545;
    }

    .action-btn:hover {
      opacity: 0.9;
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
      <h2>Staff Account Verification</h2>
      <table>
        <tr>
          <th>Staff ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['staff_id'] ?></td>
            <td><?= $row['username'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['role'] ?></td>
            <td class="<?= $row['account_status'] === 'Active' ? 'status-active' : 'status-inactive' ?>">
              <?= $row['account_status'] ?>
            </td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="staff_id" value="<?= $row['staff_id'] ?>">
                <input type="hidden" name="new_status" value="<?= $row['account_status'] === 'Active' ? 'Inactive' : 'Active' ?>">
                <button type="submit"
                    class="action-btn <?= $row['account_status'] === 'Active' ? 'deactivate' : 'activate' ?>"
                    onclick="return confirm('Confirm account <?= $row['account_status'] === 'Active' ? 'deactivation' : 'activation' ?>?');">
                    <?= $row['account_status'] === 'Active' ? 'Deactivate' : 'Activate' ?>
                </button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </div>
</body>
</html>
