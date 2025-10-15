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
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f7fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .container {
      background-color: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 350px;
      text-align: center;
    }
    input[type="text"] {
      padding: 10px;
      width: 100%;
      margin-top: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      padding: 10px 20px;
      background-color: #004aad;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background-color: #003a8c;
    }
    .error {
      color: red;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Enter Your 2FA Code</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
      <input type="text" name="code" placeholder="6-digit code" required />
      <button type="submit">Verify</button>
    </form>
  </div>
</body>
</html>
