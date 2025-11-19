<?php
session_start();
require_once '../connect.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
    // Redirect to login if not logged in
    header("Location: ../login.html");
    exit();
}

// Only proceed if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $reservation_id = $_POST['reservation_id'] ?? null;
    $action = $_POST['action'] ?? null;

    // Check if we have the necessary data
    if (!$reservation_id || !$action) {
        echo "<script>alert('Error: Missing data.'); window.location.href='manage_student.php';</script>";
        exit();
    }

    $new_status = '';
    $new_occupied_status = -1; // Use -1 as a flag to ensure it gets set

    // Determine the new statuses based on the action
    if ($action === 'accept') {
        $new_status = 'Success';
        $new_occupied_status = 1; // 1 = Occupied
    } elseif ($action === 'reject') {
        $new_status = 'Failed';
        $new_occupied_status = 0; // 0 = Available
    } else {
        // Invalid action
        echo "<script>alert('Error: Invalid action.'); window.location.href='manage_student.php';</script>";
        exit();
    }

    // Start a transaction to ensure both tables are updated together
    $conn->begin_transaction();

    try {
        // 1. Get the room_id from the reservation
        $stmt_get_room = $conn->prepare("SELECT room_id FROM reservations WHERE reservation_id = ?");
        $stmt_get_room->bind_param("i", $reservation_id);
        $stmt_get_room->execute();
        $result = $stmt_get_room->get_result();
        $data = $result->fetch_assoc();
        $room_id = $data['room_id'] ?? null;
        $stmt_get_room->close();

        if (!$room_id) {
            // If no room is associated, we can't continue
            throw new Exception("This reservation is not linked to a valid room.");
        }

        // 2. Update the reservation status
        $stmt_update_res = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $stmt_update_res->bind_param("si", $new_status, $reservation_id);
        $stmt_update_res->execute();
        $stmt_update_res->close();

        // 3. Update the room's 'is_occupied' status
        $stmt_update_room = $conn->prepare("UPDATE rooms SET is_occupied = ? WHERE room_id = ?");
        $stmt_update_room->bind_param("ii", $new_occupied_status, $room_id);
        $stmt_update_room->execute();
        $stmt_update_room->close();
        
        // If both queries succeed, commit the transaction
        $conn->commit();
        echo "<script>alert('Booking status and room occupancy updated successfully!'); window.location.href='manage_student.php';</script>";

    } catch (Exception $e) {
        // If anything fails, roll back all changes
        $conn->rollback();
        echo "<script>alert('Error updating status: " . $e->getMessage() . "'); window.location.href='manage_student.php';</script>";
    }
    
    $conn->close();
    exit();

} else {
    // Redirect if someone tries to access this page directly
    header("Location: manage_student.php");
    exit();
}
?>