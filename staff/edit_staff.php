<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_username']) || !isset($_SESSION['staff_id'])) {
    header("Location: ../login.html");
    exit();
}
require_once '../connect.php';

$staffID = $_GET['staff_id'] ?? null;
if (!$staffID) exit('Invalid staff ID provided.');

// --- Handle POST Request (Form Submission for Update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_details'])) {
    $staffID = $_POST['staff_id'];
    $newUsername = $_POST['username'];
    $newEmail = $_POST['email'];
    $newRole = $_POST['role'];
    
    // Prepare the update statement
    $updateSQL = "UPDATE staff SET username = ?, email = ?, role = ? WHERE staff_id = ?";
    $stmt = $conn->prepare($updateSQL);
    $stmt->bind_param("sssi", $newUsername, $newEmail, $newRole, $staffID);
    
    if ($stmt->execute()) {
        echo "<script>alert('Staff details updated successfully.'); window.location.href='verify_staff.php';</script>";
    } else {
        echo "<script>alert('Error updating details: " . $conn->error . "'); window.location.href='verify_staff.php';</script>";
    }
    $stmt->close();
    exit();
}

// --- Handle GET Request (Fetch Existing Data) ---
$fetchSQL = "SELECT staff_id, username, email, role FROM staff WHERE staff_id = ?";
$stmt = $conn->prepare($fetchSQL);
$stmt->bind_param("i", $staffID);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();
$stmt->close();

if (!$staff) {
    exit('Staff member not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff Details</title>
    <style>
        /* Reusing styles from verify_staff.php */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f7fa; }
        .header { background-color: #73acf7ff; color: white; padding: 20px 40px; position: relative; display: flex; align-items: center; justify-content: space-between; }
        .header img { height: 70px; width: auto; }
        .header-title { position: absolute; left: 50%; transform: translateX(-50%); font-size: 22px; font-weight: bold; }
        .logout-btn { background-color: #fff; color: #004aad; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .dashboard { display: flex; min-height: calc(100vh - 80px); }
        .sidebar { width: 250px; background-color: #e4e6eb; color: black; padding: 30px 20px; display: flex; flex-direction: column; gap: 20px; }
        .menu-item { font-size: 16px; font-weight: 500; padding: 10px 16px; border-radius: 6px; transition: background-color 0.2s ease; }
        .main-content { flex-grow: 1; padding: 40px; }
        h2 { color: #004aad; margin-bottom: 20px; }
        .form-container { background-color: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); max-width: 500px; margin: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
        input[type="text"], input[type="email"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .submit-btn { background-color: #004aad; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .submit-btn:hover { background-color: #003a8c; }
    </style>
</head>
<body>
    <div class="header">
        <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
        <div class="header-title">EDIT STAFF ACCOUNT</div>
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
        
            <div class="form-container">
                <form method="POST">
                    <input type="hidden" name="staff_id" value="<?= htmlspecialchars($staffID) ?>">
                    <input type="hidden" name="update_details" value="1">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" value="<?= htmlspecialchars($staff['username']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($staff['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select name="role" id="role" required>
                            <option value="Admin" <?= ($staff['role'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="Staff" <?= ($staff['role'] == 'Staff') ? 'selected' : '' ?>>Staff</option>
                        </select>
                    </div>

                    <button type="submit" class="submit-btn">Save Changes</button>
                    <button type="button" class="submit-btn" style="background-color: #6c757d;" onclick="window.location.href='verify_staff.php'">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>