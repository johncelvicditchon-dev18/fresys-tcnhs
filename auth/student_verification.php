<?php
session_start();
require_once "../includes/database_connection.php";
$messageType = "";
$code = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $code = $_POST['code'];
    $grade_level = $_POST['grade_level'];

    $verifyCode = $conn->prepare("SELECT id, first_name, middle_name, last_name, section, gender, vote_status FROM student_info WHERE verification_code = ? AND grade_level = ?");
    $verifyCode->bind_param("ss", $code, $grade_level);
    $verifyCode->execute();
    $verifyCode->store_result();

    if ($verifyCode->num_rows === 1) {
        $verifyCode->bind_result($idstudent, $firstname, $middlename, $lastname, $section, $gender, $VoteStatus);
        $verifyCode->fetch();

        if ($VoteStatus == "Available") {
            $messageType = "success";
            $fullname = $lastname . ", " . $firstname . " " . $middlename . ".";

            $_SESSION['student_id'] = $idstudent;
            $_SESSION['full_name'] = $fullname;
            $_SESSION['gender'] = $gender;
            $_SESSION['grade_level'] = $grade_level;
            $_SESSION['section'] = $section;
            $_SESSION['verification_code'] = $code;

            header('Location: ../pages/official_ballot.php');
            exit();

        }
        if ($VoteStatus == "Casted") {
            $messageType = "alreadycasted";
        }
    } else {
        $messageType = "error";
    }
    $verifyCode->close();
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
    <title>Verification | Tangub City NHS</title>
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
            margin-bottom: 24px;
            font-size: 13px;
            font-weight: 400;
        }

        .verify-switch {
            display: flex;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 24px;
        }

        .verify-switch button {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.35);
            font-family: 'Inter', system-ui, sans-serif;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .verify-switch button.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(45, 17, 233, 0.35);
        }

        .labelcon {
            align-items: left;
            justify-content: flex-start;
            display: flex;
            margin-bottom: 6px;
        }

        .labelforinput {
            font-size: 12px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .input-group {
            position: relative;
            margin-bottom: 18px;
        }

        .input-group input,
        .input-group select {
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
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .input-group select option {
            background: #1e293b;
            color: #fff;
        }

        .input-group input::placeholder {
            color: transparent;
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(91, 76, 255, 0.25);
        }

        .ph-fingerprint,
        .ph-star {
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
            font-size: 22px;
            color: rgba(255, 255, 255, 0.3);
            display: none;
            transition: color 0.2s;
        }

        .ph-eye:hover,
        .ph-eye-slash:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        .ph-caret-down {
            position: absolute;
            right: 14px;
            pointer-events: none;
            top: 14px;
            transition: all .3s ease;
            color: rgba(255, 255, 255, 0.3);
        }

        .input-group:focus-within .ph-caret-down {
            transform: rotate(180deg);
            color: var(--secondary);
        }

        .qr-box {
            height: 300px;
            padding: 10px;
            width: 100%;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.35);
            margin: 0 auto;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .qr-box i {
            font-size: 60px;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        #reader {
            width: 100%;
            height: 100%;
        }

        .ph-qr-code {
            margin-right: 8px;
        }

        #qrVerify {
            display: none;
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
            color: rgba(255, 255, 255, 0.35);
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

            <button class="top-back" onclick="displayPage()">
                <i class="ph ph-x"></i>
            </button>
            <div class="box-logo">
                <img src="../pictures/tcnhs_logo.png" alt="Logo">
            </div>
            <h3>Verification</h3>
            <p>Choose how you want to verify</p>


            <div class="verify-switch">
                <button type="button" class="active" id="manualBtn">Code</button>
                <button type="button" id="qrBtn"><i class="ph ph-qr-code"></i>QR Code</button>
            </div>


            <div class="success" id="success"></div>
            <div class="error" id="error"></div>


            <form method="POST" id="manualVerify">
                <div class="labelcon">
                    <label class="labelforinput">Verification Code</label>
                </div>
                <div class="input-group">
                    <i class="ph ph-fingerprint"></i>
                    <input type="text" name="code" required maxlength="20"
                        value="<?php echo htmlspecialchars($code); ?>">
                </div>

                <div class="labelcon">
                    <label class="labelforinput">Grade Level</label>
                </div>
                <div class="input-group">
                    <select name="grade_level" required>
                        <option value="" disabled selected>Select Grade Level</option>
                        <option value="Grade 7" <?php if (isset($grade_level) && $grade_level === "Grade 7")
                            echo "selected"; ?>>Grade 7</option>
                        <option value="Grade 8" <?php if (isset($grade_level) && $grade_level === "Grade 8")
                            echo "selected"; ?>>Grade 8</option>
                        <option value="Grade 9" <?php if (isset($grade_level) && $grade_level === "Grade 9")
                            echo "selected"; ?>>Grade 9</option>
                        <option value="Grade 10" <?php if (isset($grade_level) && $grade_level === "Grade 10")
                            echo "selected"; ?>>Grade 10</option>
                        <option value="Grade 11" <?php if (isset($grade_level) && $grade_level === "Grade 11")
                            echo "selected"; ?>>Grade 11</option>
                        <option value="Grade 12" <?php if (isset($grade_level) && $grade_level === "Grade 12")
                            echo "selected"; ?>>Grade 12</option>
                    </select>
                    <i class="ph ph-star"></i>
                    <i class="ph ph-caret-down"></i>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fa-solid fa-right-to-bracket"></i> Continue
                </button>
            </form>


            <div id="qrVerify">
                <div class="qr-box">
                    <div id="reader"></div>
                </div>
                <p style="margin-top:15px;font-size:13px">
                    Camera access will start automatically
                </p>
            </div>


            <div id="studentInfo">
                <div><input hidden id="student_name"></div>
                <div> <input hidden id="verification_code"></div>
                <div> <input hidden id="grade_level"></div>
                <div><input hidden id="section"></div>
            </div>

            <div class="account">
                Please contact your class adviser for student registration.
            </div>

        </section>
    </main>


    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="../javascript/qr_code_verification.js"></script>
    <script src="../javascript/page_message.js"></script>
    <script src="../javascript/student_verification_inline.js"></script>
    <script>window.__PAGE_CONFIG__={messageType:"<?php echo isset($messageType) ? $messageType : ''; ?>"}</script>
</body>

</html>