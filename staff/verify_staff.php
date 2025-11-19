<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
    header("Location: ../login.html");
    exit();
}
require_once '../connect.php';

// --- Handle POST requests (Update Status OR Delete Staff) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_id'])) {
    // Sanitize the staff ID input
    $staffID = filter_var($_POST['staff_id'], FILTER_VALIDATE_INT);
    if (!$staffID) {
        echo "<script>alert('Invalid Staff ID.'); window.location.href='verify_staff.php';</script>";
        exit();
    }

    // Check if this is a DELETE action
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        
        // Use prepared statement for secure deletion
        $deleteSQL = "DELETE FROM staff WHERE staff_id = ?";
        $stmt = $conn->prepare($deleteSQL);
        $stmt->bind_param("i", $staffID);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Staff member removed successfully'); window.location.href='verify_staff.php';</script>";
        exit();

    } elseif (isset($_POST['new_status'])) {
        
        // Use prepared statement for secure status update
        $newStatus = $_POST['new_status'];

        $updateSQL = "UPDATE staff SET account_status = ? WHERE staff_id = ?";
        $stmt = $conn->prepare($updateSQL);
        $stmt->bind_param("si", $newStatus, $staffID);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Status updated successfully'); window.location.href='verify_staff.php';</script>";
        exit();
    }
}
// --- END OF POST HANDLER ---


// Fetch all staff accounts
$sql = "SELECT staff_id, username, email, role, account_status FROM staff";
$result = $conn->query($sql);

// Initialize a display counter for the IDs
$displayID = 100;
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
            padding: 6px 10px; /* Reduced padding slightly for better fit */
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
        
        .edit {
            background-color: #007bff; /* Blue for Edit */
        }

        .remove {
            background-color: #8b0000; /* Dark Red */
        }

        .action-btn:hover {
            opacity: 0.9;
        }
        
        .action-group-flex {
            display: flex;
            justify-content: center;
            gap: 5px; /* Added gap to space out buttons */
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
                        <td><?= htmlspecialchars($displayID) ?></td> 
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td class="<?= $row['account_status'] === 'Active' ? 'status-active' : 'status-inactive' ?>">
                            <?= htmlspecialchars($row['account_status']) ?>
                        </td>
                        
                        <td>
                            <div class="action-group-flex">
                                <a href="edit_staff.php?staff_id=<?= htmlspecialchars($row['staff_id']) ?>" style="text-decoration: none;">
                                    <button type="button" class="action-btn edit">Edit Details</button>
                                </a>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="staff_id" value="<?= htmlspecialchars($row['staff_id']) ?>">
                                    <input type="hidden" name="new_status" value="<?= $row['account_status'] === 'Active' ? 'Inactive' : 'Active' ?>">
                                    <button type="submit"
                                        class="action-btn <?= $row['account_status'] === 'Active' ? 'deactivate' : 'activate' ?>"
                                        onclick="return confirm('Confirm account <?= $row['account_status'] === 'Active' ? 'deactivation' : 'activation' ?>?');">
                                        <?= $row['account_status'] === 'Active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="staff_id" value="<?= htmlspecialchars($row['staff_id']) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit"
                                        class="action-btn remove"
                                        onclick="return confirm('Are you sure you want to PERMANENTLY remove this staff member? This cannot be undone.');">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php 
                    // INCREMENT the display counter for the next row
                    $displayID++; 
                    endwhile; 
                ?>
            </table>
        </div>
    </div>
</body>
</html>