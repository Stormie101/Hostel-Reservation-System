<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
    header("Location: ../login.html");
    exit();
}
require_once '../connect.php';

// Use prepared statements for security
$reservationId = $_GET['reservation_id'] ?? null;
if (!$reservationId) {
    exit('Invalid reservation ID.');
}
$reservationId = filter_var($reservationId, FILTER_VALIDATE_INT);

// --- Handle POST Request (Room Update Logic) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_room_id'])) {
    $newRoomId = filter_var($_POST['new_room_id'], FILTER_VALIDATE_INT);
    $currentReservationId = filter_var($_POST['reservation_id'], FILTER_VALIDATE_INT);

    if ($newRoomId && $currentReservationId) {
        
        // 1. Get the old room ID associated with this reservation
        $oldRoomSql = "SELECT room_id FROM reservations WHERE reservation_id = ?";
        $stmt = $conn->prepare($oldRoomSql);
        $stmt->bind_param("i", $currentReservationId);
        $stmt->execute();
        $oldRoomResult = $stmt->get_result();
        $oldRoomData = $oldRoomResult->fetch_assoc();
        $oldRoomId = $oldRoomData['room_id'];
        $stmt->close();

        // Start Transaction
        $conn->begin_transaction();

        try {
            // 2. Update the reservation with the new room ID
            $updateBookingSql = "UPDATE reservations SET room_id = ? WHERE reservation_id = ?";
            $stmt = $conn->prepare($updateBookingSql);
            $stmt->bind_param("ii", $newRoomId, $currentReservationId);
            $stmt->execute();
            $stmt->close();
            
            // 3. Check current booking status before setting new room status
            $statusCheckSql = "SELECT status FROM reservations WHERE reservation_id = ?";
            $stmt = $conn->prepare($statusCheckSql);
            $stmt->bind_param("i", $currentReservationId);
            $stmt->execute();
            $statusData = $stmt->get_result()->fetch_assoc();
            $currentStatus = $statusData['status'];
            $stmt->close();
            
            if ($currentStatus === 'Success') {
                 // Set NEW room occupied
                $updateNewRoomSql = "UPDATE rooms SET is_occupied = 1 WHERE room_id = ?";
                $stmt = $conn->prepare($updateNewRoomSql);
                $stmt->bind_param("i", $newRoomId);
                $stmt->execute();
                $stmt->close();
            }

            // 4. Mark the OLD room as available (is_occupied = 0)
            if ($oldRoomId != $newRoomId) {
                $updateOldRoomSql = "UPDATE rooms SET is_occupied = 0 WHERE room_id = ?";
                $stmt = $conn->prepare($updateOldRoomSql);
                $stmt->bind_param("i", $oldRoomId);
                $stmt->execute();
                $stmt->close();
            }
            

            $conn->commit();
            echo "<script>alert('Room updated successfully for Reservation ID: {$currentReservationId}'); window.location.href='manage_student.php';</script>";
            exit();

        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            echo "<script>alert('Error updating room: " . $e->getMessage() . "'); window.location.href='manage_student.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid room selection.'); window.location.href='manage_student.php';</script>";
        exit();
    }
}
// --- END POST Request Handler ---


// --- Fetch Current Reservation Details ---
$currentBookingSql = "SELECT r.reservation_id, r.student_id, r.room_id, r.status, s.full_name AS student_name, rm.room_number AS current_room_number, rm.room_type AS current_room_type
                      FROM reservations r
                      JOIN students s ON r.student_id = s.student_id
                      JOIN rooms rm ON r.room_id = rm.room_id
                      WHERE r.reservation_id = ?";
$stmt = $conn->prepare($currentBookingSql);
$stmt->bind_param("i", $reservationId);
$stmt->execute();
$currentBookingData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$currentBookingData) {
    exit('Booking data not found.');
}

// --- Fetch ALL Rooms and their Status ---
$allRoomsSql = "SELECT 
                    r.room_id, 
                    r.room_number, 
                    r.room_type, 
                    r.is_occupied
                FROM rooms r
                ORDER BY r.room_number ASC"; 

$allRoomsResult = $conn->query($allRoomsSql); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Booking</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f7fa; }
        
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
            height: 40px; /* <-- LOGO FIX */
            width: auto;
            vertical-align: middle;
        }

        .header-title { position: absolute; left: 50%; transform: translateX(-50%); font-size: 22px; font-weight: bold; }
        .logout-btn { background-color: #fff; color: #004aad; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .dashboard { display: flex; min-height: calc(100vh - 80px); }
        .sidebar { width: 250px; background-color: #e4e6eb; color: black; padding: 30px 20px; display: flex; flex-direction: column; gap: 20px; }
        .menu-item { font-size: 16px; font-weight: 500; padding: 10px 16px; border-radius: 6px; transition: background-color 0.2s ease; }
        .main-content { flex-grow: 1; padding: 40px; }
        .form-container { background-color: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); max-width: 600px; margin: auto; }
        h2 { color: #004aad; margin-bottom: 20px; }
        .info-box { background-color: #eef3fc; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .info-box p { margin: 5px 0; font-size: 15px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
        select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; background-color: #fff; }
        .submit-btn { background-color: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-right: 10px; }
        .cancel-btn { background-color: #6c757d; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .submit-btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="header">
        <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
        <div class="header-title">EDIT STUDENT BOOKING</div>
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
            <h2>Change Room Assignment</h2>
            
            <div class="form-container">
                <div class="info-box">
                    <p><strong>Reservation ID:</strong> <?= htmlspecialchars($currentBookingData['reservation_id']) ?></p>
                    <p><strong>Student:</strong> <?= htmlspecialchars($currentBookingData['student_name']) ?> (ID: <?= htmlspecialchars($currentBookingData['student_id']) ?>)</p>
                    <p><strong>Current Room:</strong> <?= htmlspecialchars($currentBookingData['current_room_number']) ?> (Type: <?= htmlspecialchars($currentBookingData['current_room_type']) ?>)</p>
                    <p><strong>Booking Status:</strong> <strong><?= htmlspecialchars($currentBookingData['status']) ?></strong></p>
                </div>

                <form method="POST" action="edit_booking.php?reservation_id=<?= htmlspecialchars($reservationId) ?>">
                    <input type="hidden" name="reservation_id" value="<?= htmlspecialchars($currentBookingData['reservation_id']) ?>">
                    
                    <div class="form-group">
                        <label for="new_room_id">Select New Room</label>
                        <select name="new_room_id" id="new_room_id" required>
                            <option value="">-- Choose a Room --</option>
                            <?php 
                            // This loop now shows ALL rooms and their status
                            while ($room = $allRoomsResult->fetch_assoc()): 
                                
                                $room_id = $room['room_id'];
                                $room_number = htmlspecialchars($room['room_number']);
                                $room_type = htmlspecialchars($room['room_type']);
                                $is_occupied = $room['is_occupied'];
                                
                                $status_text = '';
                                $is_current = ($room_id == $currentBookingData['room_id']);

                                if ($is_current) {
                                    $status_text = ' (CURRENTLY ASSIGNED)';
                                } elseif ($is_occupied) {
                                    // Room is occupied by someone ELSE
                                    $status_text = ' (OCCUPIED - Requires Action)'; 
                                } else {
                                    $status_text = ' (Available)';
                                }
                            ?>
                                <option 
                                    value="<?= $room_id ?>" 
                                    <?= $is_current ? 'selected' : '' ?> 
                                    <?= $is_occupied && !$is_current ? 'style="color: red; font-weight: bold;"' : '' ?>
                                >
                                    Room: <?= $room_number ?> (Type: <?= $room_type ?>) <?= $status_text ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" class="submit-btn" onclick="return confirm('Confirm room change? The old room will be marked available.');">Update Room</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='manage_student.php'">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>