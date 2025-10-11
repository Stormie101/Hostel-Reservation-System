<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['reservation_id'];
    $action = $_POST['action'];

    $newStatus = $action === 'accept' ? 'Success' : 'Failed';

    $sql = "UPDATE reservations SET status = ? WHERE reservation_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $newStatus, $id);
    mysqli_stmt_execute($stmt);
}

header("Location: manage_student.php"); // redirect back to booking page
exit;
