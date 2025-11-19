<?php
include '../connect.php';

// Add this line to ensure mysqli throws exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match'); window.location.href='signup.html';</script>";
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'Staff') {
        $accountStatus = 'Inactive';
        $sql = "INSERT INTO staff (username, email, password, role, account_status) VALUES (?, ?, ?, 'Staff', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $hashed, $accountStatus);

    } elseif ($role === 'Student') {
        // Collect student-specific fields
        $studentID = $_POST['student_id'] ?? '';
        $programme = $_POST['programme'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $status = 'Accepted'; // default status

        $sql = "INSERT INTO students (student_id, full_name, email, password, program, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $studentID, $username, $email, $hashed, $programme, $gender, $status);

    } else {
        echo "<script>alert('Invalid role selected'); window.location.href='signup.html';</script>";
        exit();
    }

    // --- MODIFIED SECTION ---
    try {
        if ($stmt->execute()) {
            echo "<script>alert('Account created successfully'); window.location.href='../login.html';</script>";
        } else {
            // This 'else' might catch non-exception failures
            echo "<script>alert('Signup failed'); window.location.href='../signup.html';</script>";
        }

    } catch (mysqli_sql_exception $e) {
        // Check if the error is the "Duplicate entry" error (code 1062)
        if ($e->getCode() == 1062) {
            echo "<script>alert('Signup failed: This email is already registered.'); window.location.href='../signup.html';</script>";
        } else {
            // For any other database error
            echo "<script>alert('Signup failed: An unexpected error occurred.'); window.location.href='../signup.html';</script>";
            // For debugging, you could log the actual error:
            // error_log("Signup Error: " . $e->getMessage());
        }
    }
    // --- END OF MODIFIED SECTION ---

    $stmt->close();
}
$conn->close();
?>