<?php
session_start();
$username = "";
$password = "";
date_default_timezone_set("Asia/Manila");
require_once "../includes/database_connection.php";


function timeStampuser($conn, $accID, $name, $userName, $hashedPassword, $admin_adviser, $date)
{
  $timeStampUser = $conn->prepare("INSERT INTO userloginaccount (account_ID, Teacher_Name, Access, username, password, Date_Time) VALUES (?,?,?,?,?,?)");
  $timeStampUser->bind_param("isssss", $accID, $name, $admin_adviser, $userName, $hashedPassword, $date);
  $timeStampUser->execute();
  $timeStampUser->close();

}

// verify account
$messageType = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $username = $_POST['username'];
  $password = $_POST['password'];
  $date = date("Y-m-d H:i:s");

  $checkAccount = $conn->prepare("SELECT id, name, username, password, status, admin_adviser FROM admin_account WHERE username = ?");
  $checkAccount->bind_param("s", $username);
  $checkAccount->execute();
  $checkAccount->store_result();

  if ($checkAccount->num_rows === 1) {
    $checkAccount->bind_result($accID, $name, $userName, $hashedPassword, $status, $admin_adviser);
    $checkAccount->fetch();

    if (password_verify($password, $hashedPassword)) {

      if ($status === "Approved" && $admin_adviser === "Admin") {
        timeStampuser($conn, $accID, $name, $userName, $hashedPassword, $admin_adviser, $date);
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = $admin_adviser;
        $messageType = "success_admin";

      } elseif ($status === "Approved" && $admin_adviser === "Adviser") {
        timeStampuser($conn, $accID, $name, $userName, $hashedPassword, $admin_adviser, $date);
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = $admin_adviser;
        $messageType = "success_adviser";

      } elseif ($status === "Pending") {
        $messageType = "pending_account";

      } elseif ($status === "Deactivated") {
        $messageType = "deactivated_account";

      } else {
        $messageType = "error";
      }
    } else {
      $messageType = "error";

    }

  } else {
    $messageType = "notfound";
  }
  $checkAccount->close();
  $conn->close();



}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="icon" href="../pictures/tcnhs_logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />

  <title>Login | Tangub City NHS</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #2D11E9;
      --secondary: #5B4CFF;
      --hover: #0F5FD7;
      --border: #e2e8f0;
      --bg: #0f172a;
      --card: #ffffff;
      --text-dark: #1e293b;
      --text-light: #64748b;
    }

    .success,
    .error {
      font-size: 13px;
      margin-bottom: 16px;
      padding: 12px 16px;
      border-radius: 10px;
      display: none;
      align-items: center;
      justify-content: center;
      color: #fff;
      gap: 8px;
      font-weight: 500;
    }

    .success {
      background: linear-gradient(135deg, #34d399, #10b981);
      border: 1.5px solid rgba(255,255,255,0.15);
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    }

    .error {
      background: linear-gradient(135deg, #f87171, #ef4444);
      border: 1.5px solid rgba(255,255,255,0.15);
      box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
    }

    .success i,
    .error i {
      font-size: 16px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: var(--bg);
      overflow: hidden;
    }

    .image {
      position: fixed;
      inset: 0;
      background: url("../pictures/bg.jpg") center/cover no-repeat;
      filter: blur(8px) brightness(0.35);
      z-index: -2;
      transform: scale(1.05);
    }

    .image::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(15, 23, 42, 0.3) 0%, rgba(15, 23, 42, 0.7) 100%);
    }

    .container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      position: relative;
      z-index: 1;
    }

    .box {
      position: relative;
      background: rgba(255, 255, 255, 0.07);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 50px 36px 36px;
      max-width: 420px;
      width: 100%;
      text-align: center;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
      animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(24px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .top-back {
      position: absolute;
      top: 16px;
      right: 16px;
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 8px;
      color: rgba(255,255,255,0.5);
      font-size: 18px;
      cursor: pointer;
      width: 34px;
      height: 34px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .top-back:hover {
      color: #fff;
      background: rgba(255,255,255,0.14);
    }

    .box-logo {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      box-shadow: 0 8px 24px rgba(45, 17, 233, 0.4);
    }

    .box-logo img {
      width: 32px;
      height: 32px;
    }

    h3 {
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 6px;
      color: #fff;
      letter-spacing: -0.3px;
    }

    p {
      margin-top: 0;
      color: rgba(255, 255, 255, 0.45);
      margin-bottom: 28px;
      font-size: 13px;
      font-weight: 400;
    }

    .input-group {
      position: relative;
      margin-bottom: 18px;
    }

    .input-group input {
      width: 100%;
      padding: 13px 14px 13px 40px;
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: rgba(255, 255, 255, 0.06);
      font-size: 14px;
      color: #fff;
      outline: none;
      font-family: 'Inter', system-ui, sans-serif;
      transition: all 0.2s;
    }

    .input-group input::placeholder {
      color: transparent;
    }

    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear,
    input[type="password"]::-webkit-credentials-auto-fill-button {
      display: none;
    }

    .input-group input:focus {
      border-color: var(--secondary);
      box-shadow: 0 0 0 3px rgba(91, 76, 255, 0.25);
    }

    .input-group label {
      position: absolute;
      left: 40px;
      top: 14px;
      font-size: 14px;
      color: rgba(255, 255, 255, 0.35);
      background: transparent;
      padding: 0 6px;
      pointer-events: none;
      transition: 0.2s ease;
    }

    .input-group input:focus+label,
    .input-group input:not(:placeholder-shown)+label {
      top: -9px;
      font-size: 11px;
      color: var(--secondary);
      font-weight: 600;
      background: linear-gradient(180deg, rgba(15,23,42,0.7) 50%, transparent 50%);
    }

    .ph-user,
    .fa-unlock {
      position: absolute;
      top: 14px;
      left: 14px;
      color: rgba(255, 255, 255, 0.3);
      pointer-events: none;
      font-size: 15px;
    }

    .ph-eye,
    .ph-eye-slash {
      position: absolute;
      right: 14px;
      top: 14px;
      cursor: pointer;
      font-size: 17px;
      color: rgba(255, 255, 255, 0.3);
      display: none;
      transition: color 0.2s;
    }

    .ph-eye:hover,
    .ph-eye-slash:hover {
      color: rgba(255, 255, 255, 0.7);
    }

    .login-btn {
      width: 100%;
      padding: 13px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: #fff;
      box-shadow: 0 6px 20px rgba(45, 17, 233, 0.4);
      transition: all 0.25s ease;
      font-family: 'Inter', system-ui, sans-serif;
      margin-top: 6px;
    }

    .login-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 28px rgba(45, 17, 233, 0.5);
    }

    .login-btn:active {
      transform: translateY(0);
    }

    .login-btn i {
      margin-right: 6px;
    }

    .account {
      margin-top: 22px;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.4);
    }

    .account a {
      color: var(--secondary);
      font-weight: 600;
      text-decoration: none;
      transition: color 0.2s;
    }

    .account a:hover {
      color: #8b7dff;
    }

    @media (max-width: 480px) {
      .box {
        padding: 45px 24px 30px;
        margin: 0 12px;
      }
    }
  </style>
</head>

<body>
  <div class="image"></div>
  <main class="container">
    <section class="box">
      <button class="top-back" onclick="displayPage()"><i class="ph ph-x"></i></button>
      <div class="box-logo">
        <img src="../pictures/tcnhs_logo.png" alt="Logo">
      </div>
      <h3>Welcome Back</h3>
      <p>Log in to access your account</p>

      <div class="success" id="success"></div>
      <div class="error" id="error"></div>

      <form method="POST">
        <div class="input-group">
          <i class="ph ph-user"></i>
          <input type="text" id="username" name="username" placeholder=" "
            value="<?php echo htmlspecialchars($username) ?>" required>
          <label for="username">Username</label>
        </div>

        <div class="input-group">
          <i class="fa-solid fa-unlock"></i>
          <input type="password" id="password" name="password" placeholder=" " required>
          <label for="password">Password</label>
          <i class="ph ph-eye" id="show"></i>
          <i class="ph ph-eye-slash" id="donotshow"></i>
        </div>

        <button type="submit" class="login-btn">
          <i class="fa-solid fa-right-to-bracket"></i> Login
        </button>
      </form>

      <div class="account">
        Don't have an account? <a href="../auth/sign-up.php">Sign Up</a>
      </div>

    </section>
  </main>

  <script src="../javascript/page_message.js"></script>
  <script src="../javascript/login_inline.js"></script>
  <script>window.__PAGE_CONFIG__={messageType:"<?php echo isset($messageType) ? $messageType : ''; ?>"}</script>
</body>

</html>