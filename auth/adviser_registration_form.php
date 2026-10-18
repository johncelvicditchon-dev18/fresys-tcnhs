<?php
require_once "../includes/database_connection.php";
$messageType = "";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {


  $fname = trim(strtoupper($_POST['firstname']));
  $mname = trim(strtoupper($_POST['middleinitial']));
  $lname = trim(strtoupper($_POST['lastname']));
  $gender = $_POST['gender'];
  $grade = $_POST['grade'];
  $section = $_POST['section'];
  $casted = "Available";

  // function to check and generate unique verification
  function checkUniqueCode($conn)
  {
    $count = 0;
    do {
      $code = "TCNHS" . random_int(1000000000, 9999999999);

      $checkCode = $conn->prepare("SELECT COUNT(*) FROM student_info WHERE verification_code = ?");
      $checkCode->bind_param("s", $code);
      $checkCode->execute();
      $checkCode->bind_result($count);
      $checkCode->fetch();
      $checkCode->close();

    } while ($count > 0);

    return $code;
  }
  $code = checkUniqueCode($conn);
  try {
    $addStudent = $conn->prepare("INSERT INTO student_info (verification_code, first_name, middle_name, 
    last_name, gender, grade_level, section, vote_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $addStudent->bind_param("ssssssss", $code, $fname, $mname, $lname, $gender, $grade, $section, $casted);

    if ($addStudent->execute()) {
      $messageType = "success";
    } else {
      $messageType = "try-again";
    }
  } catch (mysqli_sql_exception $duplicatecodeerror) {
    if ($duplicatecodeerror->getCode() == 1062) { /// catch the error duplicate
      $messageType = "try-again";
    }
  }

  $addStudent->close();
  $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../pictures/tcnhs_logo.png">
  <title>Student Registration | Tangub City NHS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <style>
    :root {
      --primary: #2D11E9;
      --primary-light: #5B4CFF;
      --hover: #0F5FD7;
      --success: #10b981;
      --red: #ef4444;
      --border: #e5e7eb;
      --bg: #f1f5f9;
      --text-dark: #1e293b;
      --text-light: #64748b;
      --dark: #0f172a;
      --card: #ffffff;
      --muted: #94a3b8;
      --radius-sm: 6px;
      --radius-md: 10px;
      --radius-lg: 14px;
      --radius-xl: 18px;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
      --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
      --shadow-lg: 0 10px 30px rgba(0,0,0,0.12);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
      transition: all 0.2s ease;
    }

    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: var(--bg);
    }

    body::-webkit-scrollbar {
      display: none;
    }

    .container {
      width: 100%;
      display: flex;
      justify-content: center;
      padding: 30px 20px;
    }

    .box {
      background: var(--card);
      border-radius: var(--radius-xl);
      width: 100%;
      max-width: 520px;
      box-shadow: var(--shadow-lg);
      overflow: hidden;
      animation: fade 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
      padding: 0;
      position: relative;
      border: 1px solid var(--border);
    }

    /* ============================================
       HEADER
       ============================================ */
    .header {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      padding: 20px 30px;
      color: white;
      font-weight: 700;
      font-size: 14px;
      letter-spacing: 1px;
      text-align: center;
      position: relative;
    }

    .header::after {
      content: '';
      position: absolute;
      bottom: -20px;
      left: 0;
      right: 0;
      height: 40px;
      background: var(--card);
      border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    }

    .form-body {
      padding: 16px 36px 36px;
    }

    h3 {
      font-size: 20px;
      color: var(--text-dark);
      margin: 0 0 6px;
      text-align: center;
      font-weight: 700;
      letter-spacing: -0.3px;
    }

    p {
      color: var(--text-light);
      margin-bottom: 24px;
      font-size: 13px;
      text-align: center;
    }

    hr {
      border: none;
      height: 1px;
      background: var(--border);
      margin-bottom: 24px;
    }

    /* ============================================
       ALERTS
       ============================================ */
    .success,
    .error {
      font-size: 13px;
      margin-bottom: 16px;
      padding: 12px 16px;
      border-radius: var(--radius-md);
      display: none;
      align-items: center;
      justify-content: center;
      color: #fff;
      opacity: 0;
      transform: translateY(-10px);
      animation: slideIn 0.4s forwards;
      gap: 8px;
      font-weight: 500;
    }

    .success {
      background: linear-gradient(135deg, #34d399, #10b981);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .error {
      background: linear-gradient(135deg, #f87171, #ef4444);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .success i,
    .error i {
      font-size: 16px;
    }

    @keyframes slideIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fade {
      0% {
        opacity: 0;
        transform: translateY(20px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ============================================
       FORM INPUTS
       ============================================ */
    .input-group {
      position: relative;
      flex: 1;
      min-width: 150px;
      margin-bottom: 16px;
    }

    .input-group label {
      font-weight: 500;
      color: var(--text-dark);
      font-size: 13px;
      margin-bottom: 6px;
      display: block;
      line-height: 1;
    }

    .input-group input {
      border-radius: var(--radius-md);
      outline: none;
      padding: 12px 14px 12px 40px;
      border: 1.5px solid var(--border);
      width: 100%;
      font-size: 14px;
      color: var(--text-dark);
      background: var(--card);
      transition: all 0.2s ease;
    }

    .input-group input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(45, 17, 233, 0.1);
    }

    .input-group input::placeholder {
      color: var(--muted);
    }

    .ph-user {
      position: absolute;
      left: 14px;
      bottom: 13px;
      font-size: 16px;
      color: var(--muted);
      pointer-events: none;
      line-height: 1;
    }

    .input-group input:focus ~ .ph-user {
      color: var(--primary);
    }

    /* ============================================
       SELECT INPUTS
       ============================================ */
    .select-group {
      position: relative;
      margin-bottom: 16px;
    }

    .select-group label {
      font-weight: 500;
      color: var(--text-dark);
      font-size: 13px;
      margin-bottom: 6px;
      display: block;
      line-height: 1;
    }

    .gender,
    .grade,
    .section {
      width: 100%;
      padding: 12px 40px 12px 40px;
      border-radius: var(--radius-md);
      border: 1.5px solid var(--border);
      outline: none;
      appearance: none;
      font-size: 14px;
      cursor: pointer;
      color: var(--text-dark);
      background: var(--card);
      transition: all 0.2s ease;
      line-height: 1;
    }

    .gender:focus,
    .grade:focus,
    .section:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(45, 17, 233, 0.1);
    }

    .ph-gender-intersex,
    .ph-star,
    .ph-graduation-cap,
    .ph-users-four {
      position: absolute;
      left: 14px;
      bottom: 13px;
      font-size: 16px;
      color: var(--muted);
      pointer-events: none;
      line-height: 1;
    }

    .ph-caret-down {
      position: absolute;
      right: 14px;
      bottom: 13px;
      pointer-events: none;
      color: var(--muted);
      transition: transform 0.2s ease;
      line-height: 1;
    }

    .select-group:focus-within .ph-caret-down {
      transform: rotate(180deg);
      color: var(--primary);
    }

    .select-group:focus-within .ph-gender-intersex,
    .select-group:focus-within .ph-star,
    .select-group:focus-within .ph-graduation-cap,
    .select-group:focus-within .ph-users-four {
      color: var(--primary);
    }

    /* ============================================
       FLEX ROWS
       ============================================ */
    .flex {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
    }

    .flex .input-group:first-child {
      flex: 3;
    }

    .flex .input-group:last-child {
      flex: 1;
    }

    /* ============================================
       BUTTONS
       ============================================ */
    .button-main-group {
      display: flex;
      gap: 12px;
      margin-top: 24px;
    }

    .button-main-group .register,
    .button-main-group .back {
      flex: 1;
    }

    .register {
      width: 100%;
      border: none;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      font-size: 14px;
      font-weight: 600;
      border-radius: var(--radius-md);
      padding: 12px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 2px 8px rgba(45, 17, 233, 0.3);
    }

    .register:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 14px rgba(45, 17, 233, 0.4);
    }

    .back {
      border: 1.5px solid var(--border);
      color: var(--text-dark);
      padding: 12px;
      outline: none;
      border-radius: var(--radius-md);
      background: var(--card);
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
    }

    .back:hover {
      background: #f1f5f9;
      border-color: var(--muted);
    }

    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 600px) {
      .box {
        border-radius: var(--radius-lg);
      }

      .form-body {
        padding: 16px 24px 28px;
      }

      .header {
        font-size: 12px;
        padding: 16px;
      }

      .flex {
        flex-direction: column;
      }

      .flex .input-group:first-child,
      .flex .input-group:last-child {
        flex: 1;
      }

      .button-main-group {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="box">
      <div class="header">TANGUB CITY NATIONAL HIGH SCHOOL</div>
      <div class="form-body">
        <h3>Student Registration</h3>
        <p>Fill in the following fields to register a student</p>
        <hr>
        <div class="success" id="success"></div>
        <div class="error" id="error"></div>

        <form action="" method="POST">
          <div class="flex">
            <div class="input-group">
              <label for="firstname">First Name</label>
              <input type="text" placeholder="Juan" id="firstname" name="firstname" maxlength="100" required>
              <i class="ph ph-user"></i>
            </div>
            <div class="input-group">
              <label for="middleinitial">Middle Initial</label>
              <input type="text" placeholder="C" id="middleinitial" name="middleinitial" maxlength="1">
              <i class="ph ph-user"></i>
            </div>
          </div>

          <div class="input-group">
            <label for="lastname">Last Name</label>
            <input type="text" placeholder="Dela Cruz" id="lastname" name="lastname" maxlength="100" required>
            <i class="ph ph-user"></i>
          </div>

          <div class="select-group">
            <label for="gender">Gender</label>
            <select class="gender" id="gender" name="gender" required>
              <option disabled selected value="">Select Gender</option>
              <option value="Male" <?php if (isset($gender) && $gender === "Male") echo "selected"; ?>>Male</option>
              <option value="Female" <?php if (isset($gender) && $gender === "Female") echo "selected"; ?>>Female</option>
            </select>
            <i class="ph ph-gender-intersex"></i>
            <i class="ph ph-caret-down"></i>
          </div>

          <div class="select-group">
            <label for="grade">Grade Level</label>
            <select class="grade" id="grade" name="grade" required>
              <option disabled selected value="">Select Grade Level</option>
              <?php foreach (['Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12'] as $g): ?>
                <option value="<?php echo $g; ?>" <?php if (isset($grade) && $grade === $g) echo "selected"; ?>><?php echo $g; ?></option>
              <?php endforeach; ?>
            </select>
            <i class="ph ph-graduation-cap"></i>
            <i class="ph ph-caret-down"></i>
          </div>

          <div class="select-group">
            <label for="section">Section</label>
            <select id="section" class="section" name="section" required
              data-selected-section="<?php echo htmlspecialchars($section ?? ''); ?>">
              <option disabled selected value="">Select Section</option>
            </select>
            <i class="ph ph-users-four"></i>
            <i class="ph ph-caret-down"></i>
          </div>

          <div class="button-main-group">
            <button type="submit" class="register"><i class="ph ph-user-plus"></i>Save</button>
            <button type="button" onclick="backPageRegister()" class="back">View Registered Students</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="../javascript/page_message.js"></script>
  <script src="../javascript/adviser_reg_inline.js"></script>
  <script>window.__PAGE_CONFIG__={messageType:"<?php echo isset($messageType) ? $messageType : ''; ?>"}</script>
  <script src="../javascript/get_regis_section.js"></script>

</body>

</html>