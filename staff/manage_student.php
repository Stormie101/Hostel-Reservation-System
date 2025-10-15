<?php

session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
  header("Location: ../login.html");
  exit();
}
require_once '../connect.php';

$sql = "SELECT r.reservation_id, r.student_id, r.room_id, r.status, s.full_name AS name, rm.room_type
        FROM reservations r
        JOIN students s ON r.student_id = s.student_id
        JOIN rooms rm ON r.room_id = rm.room_id
        ORDER BY r.reservation_id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Booking</title>
  <style>
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

    .section-title {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 20px;
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
    .booking-right {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 10px;
    }
    .status-badge {
      padding: 6px 14px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 13px;
      text-align: center;
      width: fit-content;
    }
    .badge-hold {
      background-color: #fff3cd;
      color: #856404;
    }
    .badge-accepted {
      background-color: #d4edda;
      color: #155724;
    }
    .badge-rejected {
      background-color: #f8d7da;
      color: #721c24;
    }
    .search-bar {
      margin-bottom: 30px;
      text-align: right;
    }
    #searchInput {
      padding: 10px 16px;
      width: 300px;
      max-width: 100%;
      border: 1px solid #ccc;
      border-radius: 20px;
      font-size: 14px;
    }
    .action-group {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      justify-content: center;
    }
    .action-btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }
    .accept-btn { background-color: green; color: white; }
    .reject-btn { background-color: red; color: white; }
    .profile-btn { background-color: #004aad; color: white; }
    .report-btn { background-color: #888; color: white; }
    .action-btn:hover { opacity: 0.9; }
    .filter-bar {
  margin-bottom: 20px;
  text-align: right;
}

#statusFilter {
  padding: 10px 16px;
  border-radius: 20px;
  border: 1px solid #ccc;
  font-size: 14px;
}

  </style>
</head>
<body>
  <div class="header">
    <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
    <div class="header-title">STUDENT BOOKING</div>
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
      <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search by name, ID, or room type..." />
      </div>

      <div class="filter-bar">
  <select id="statusFilter">
    <option value="all">Show All (Latest)</option>
    <option value="Success">Accepted Only</option>
    <option value="Pending">Pending Only</option>
    <option value="Failed">Rejected Only</option>
  </select>
</div>
      <div class="booking-cards">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="booking-card">
          <div class="booking-left">
            <div class="student-id"><?= $row['student_id'] ?></div>
            <div class="student-name"><?= $row['name'] ?></div>
            <div class="room-type">Room Type: <strong><?= $row['room_type'] ?></strong></div>
          </div>
          <div class="booking-right">
            <div class="status-badge <?= 
              $row['status'] === 'Pending' ? 'badge-hold' : 
              ($row['status'] === 'Success' ? 'badge-accepted' : 'badge-rejected') 
            ?>">
              <?= $row['status'] ?>
            </div>
<div class="action-group">
  <?php if ($row['status'] === 'Pending'): ?>
    <div class="decision-buttons" data-id="<?= $row['reservation_id'] ?>">
      <form method="POST" action="update_status.php">
        <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
        <input type="hidden" name="action" value="accept">
        <button class="action-btn accept-btn" type="submit">Accept</button>
      </form>
      <form method="POST" action="update_status.php">
        <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
        <input type="hidden" name="action" value="reject">
        <button class="action-btn reject-btn" type="submit">Reject</button>
      </form>
    </div>
  <?php endif; ?>

  <a href="view_profile.php?student_id=<?= $row['student_id'] ?>">
    <button class="action-btn profile-btn">View Profile</button>
  </a>
  <a href="generate_report.php?reservation_id=<?= $row['reservation_id'] ?>">
    <button class="action-btn report-btn">Generate Report</button>
  </a>
</div>

          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

<script>
  // Live search
  document.getElementById('searchInput').addEventListener('keyup', function () {
    const query = this.value.toLowerCase();
    const cards = document.querySelectorAll('.booking-card');

    cards.forEach(card => {
      const text = card.textContent.toLowerCase();
      card.style.display = text.includes(query) ? 'flex' : 'none';
    });
  });

  // Hide Accept/Reject buttons after submission (optional if PHP handles it)
  document.querySelectorAll('.decision-buttons form').forEach(form => {
    form.addEventListener('submit', function () {
      const container = this.closest('.decision-buttons');
      container.style.display = 'none';
    });
  });
  document.getElementById('statusFilter').addEventListener('change', function () {
  const selected = this.value;
  const cards = document.querySelectorAll('.booking-card');

  cards.forEach(card => {
    const status = card.querySelector('.status-badge')?.textContent.trim();
    if (selected === 'all' || status === selected) {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });
});

</script>
</body>
</html>