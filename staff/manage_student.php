<?php

session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
    header("Location: ../login.html");
    exit();
}
require_once '../connect.php';

// --- NEW: DELETE BOOKING LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_booking') {
    
    $reservationIdToDelete = $_POST['reservation_id'];
    
    // We MUST use a transaction to ensure data integrity
    // (delete reservation AND free up the room)
    $conn->begin_transaction();
    
    try {
        // 1. Find the room_id associated with this reservation
        $stmt_get_room = $conn->prepare("SELECT room_id FROM reservations WHERE reservation_id = ?");
        $stmt_get_room->bind_param("i", $reservationIdToDelete);
        $stmt_get_room->execute();
        $result_room = $stmt_get_room->get_result();
        $room_data = $result_room->fetch_assoc();
        $roomIdToUpdate = $room_data['room_id'] ?? null;
        $stmt_get_room->close();

        // 2. Delete the reservation
        $stmt_delete_res = $conn->prepare("DELETE FROM reservations WHERE reservation_id = ?");
        $stmt_delete_res->bind_param("i", $reservationIdToDelete);
        $stmt_delete_res->execute();
        $stmt_delete_res->close();

        // 3. If a room was associated, mark it as available (is_occupied = 0)
        if ($roomIdToUpdate) {
            $stmt_update_room = $conn->prepare("UPDATE rooms SET is_occupied = 0 WHERE room_id = ?");
            $stmt_update_room->bind_param("i", $roomIdToUpdate);
            $stmt_update_room->execute();
            $stmt_update_room->close();
        }

        // If all queries were successful, commit the changes
        $conn->commit();
        echo "<script>alert('Booking deleted successfully. The room is now marked as available.'); window.location.href='manage_student.php';</script>";

    } catch (mysqli_sql_exception $e) {
        // If any query failed, roll back all changes
        $conn->rollback();
        echo "<script>alert('Error deleting booking: " . $e->getMessage() . "'); window.location.href='manage_student.php';</script>";
    }
    exit();
}
// --- END DELETE LOGIC ---


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
      background-color: #73acf7ff;
      color: white;
      padding: 20px 40px;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 80px; /* Set a consistent height */
    }

    .header img {
      height: 40px; /* <-- LOGO FIX: Made logo smaller */
      width: auto;
      vertical-align: middle;
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
      min-height: calc(100vh - 80px); /* Adjusted for header height */
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
    
    /* --- NEW: DELETE BUTTON STYLE --- */
    .delete-btn { background-color: #dc3545; color: white; } 
    
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

    .decision-buttons {
      display: flex; /* Makes the forms inside it go side-by-side */
      gap: 6px;      /* Adds space between the Accept/Reject buttons */
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
            <div class="student-id"><?= htmlspecialchars($row['student_id']) ?></div>
            <div class="student-name"><?= htmlspecialchars($row['name']) ?></div>
            <div class="room-type">Room Type: <strong><?= htmlspecialchars($row['room_type']) ?></strong></div>
          </div>
          <div class="booking-right">
            <div class="status-badge <?= 
              $row['status'] === 'Pending' ? 'badge-hold' : 
              ($row['status'] === 'Success' ? 'badge-accepted' : 'badge-rejected') 
            ?>">
              <?= htmlspecialchars($row['status']) ?>
            </div>
            <div class="action-group">
              
              <?php // Logic to show buttons based on status ?>

              <?php if ($row['status'] === 'Pending'): ?>
                <div class="decision-buttons" data-id="<?= htmlspecialchars($row['reservation_id']) ?>">
                  <form method="POST" action="update_status.php" style="display:inline;">
                    <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($row['reservation_id']) ?>">
                    <input type="hidden" name="action" value="accept">
                    <button class="action-btn accept-btn" type="submit">Accept</button>
                  </form>
                  <form method="POST" action="update_status.php" style="display:inline;">
                    <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($row['reservation_id']) ?>">
                    <input type="hidden" name="action" value="reject">
                    <button class="action-btn reject-btn" type="submit">Reject</button>
                  </form>
                </div>
              
              <?php elseif ($row['status'] === 'Success'): ?>
                <div class="decision-buttons" data-id="<?= htmlspecialchars($row['reservation_id']) ?>">
                  <form method="POST" action="update_status.php" style="display:inline;">
                    <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($row['reservation_id']) ?>">
                    <input type="hidden" name="action" value="reject">
                    <button class="action-btn reject-btn" type="submit">Reject</button>
                  </form>
                </div>
              
              <?php elseif ($row['status'] === 'Failed'): ?>
                <div class="decision-buttons" data-id="<?= htmlspecialchars($row['reservation_id']) ?>">
                  <form method="POST" action="update_status.php" style="display:inline;">
                    <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($row['reservation_id']) ?>">
                    <input type="hidden" name="action" value="accept">
                    <button class="action-btn accept-btn" type="submit">Accept</button>
                  </form>
                </div>
              <?php endif; ?>

              <?php // These buttons show for all statuses ?>
              <a href="view_profile.php?student_id=<?= htmlspecialchars($row['student_id']) ?>">
                <button class="action-btn profile-btn">View Profile</button>
              </a>
              
              <a href="generate_report.php?reservation_id=<?= htmlspecialchars($row['reservation_id']) ?>" target="_blank">
                <button class="action-btn report-btn">Generate Report</button>
              </a>

              <form method="POST" action="manage_student.php" style="display:inline;">
                <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($row['reservation_id']) ?>">
                <input type="hidden" name="action" value="delete_booking">
                <button class="action-btn delete-btn" type="submit" 
                        onclick="return confirm('Are you sure you want to PERMANENTLY delete this booking for <?= htmlspecialchars(addslashes($row['name'])) ?>? This will also free up their room.');">
                    Delete
                </button>
              </form>
              </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

<script>
  // Get references to the elements
  const searchInput = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');
  const cards = document.querySelectorAll('.booking-card');

  // Create a single function to handle all filtering
  function filterCards() {
    const query = searchInput.value.toLowerCase();
    const selectedStatus = statusFilter.value;

    cards.forEach(card => {
      const textContent = card.textContent.toLowerCase();
      // Find the status badge text, trim whitespace
      const statusElement = card.querySelector('.status-badge');
      const status = statusElement ? statusElement.textContent.trim() : '';

      // Check both conditions
      const matchesQuery = textContent.includes(query);
      const matchesStatus = (selectedStatus === 'all' || status === selectedStatus);

      // Show or hide based on BOTH conditions being true
      if (matchesQuery && matchesStatus) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  // Add event listeners to both inputs, calling the same function
  searchInput.addEventListener('keyup', filterCards);
  statusFilter.addEventListener('change', filterCards);

  // Hide Accept/Reject buttons after submission
  document.querySelectorAll('.decision-buttons form').forEach(form => {
    form.addEventListener('submit', function () {
      const container = this.closest('.decision-buttons');
      if (container) {
          // Hide the button container to prevent double-click
          container.style.display = 'none';
      }
    });
  });
</script>
</body>
</html>