<?php
session_start();

// Redirect if no pending 2FA session
if (!isset($_SESSION['pending_2fa'])) {
    header("Location: ../login.html");
    exit();
}

$pending = $_SESSION['pending_2fa'];
$userType = $pending['user_type'];
$expectedCode = $pending['code'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredCode = $_POST['code'];

    if ($enteredCode == $expectedCode) {
        // Finalize login
        session_regenerate_id(true);

        if ($userType === 'Staff') {
            $_SESSION['staff_id'] = $pending['id'];
            $_SESSION['staff_username'] = $pending['username'];
            $_SESSION['account_status'] = $pending['account_status'];
            $redirectPath = '../staff/staff_index.php';
        } else {
            $_SESSION['student_id'] = $pending['id'];
            $_SESSION['student_username'] = $pending['username'];
            $redirectPath = '../student/student_index.php';
        }

        unset($_SESSION['pending_2fa']);
        header("Location: $redirectPath");
        exit();
    } else {
        $error = "Invalid code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>2FA Verification</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f7fa;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      background-color: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .container img {
        width: 150px;
        margin-bottom: 25px;
    }

    .container h2 {
        font-size: 24px;
        color: #333;
        margin-bottom: 15px;
    }
    
    .container p {
        font-size: 15px;
        color: #555;
        margin-bottom: 25px;
    }

    input[type="text"] {
      padding: 14px;
      width: 100%;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
      text-align: center;
      letter-spacing: 2px; /* Makes code entry feel more like code */
    }

    button {
      padding: 14px 20px;
      background-color: #004aad;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
      font-weight: bold;
      transition: background-color 0.2s ease;
    }

    button:hover {
      background-color: #003a8c;
    }

    .error {
      color: #d9534f; /* Red color */
      font-weight: bold;
      background-color: #f8d7da; /* Light red background */
      border: 1px solid #f5c6cb;
      border-radius: 8px;
      padding: 10px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <img src="../IMG/uptm logo.png" alt="UPTM Logo" />
    <h2>2-Factor Verification</h2>
    <p>A 6-digit code has been sent to your email. Please enter it below.</p>

    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    
    <form method="POST">
      <input type="text" name="code" placeholder="6-digit code" required maxlength="6" />
      <button type="submit">Verify</button>
    </form>
  </div>
</body>
</html>