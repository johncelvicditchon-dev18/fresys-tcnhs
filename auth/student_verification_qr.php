<?php
session_start();
require_once "../includes/database_connection.php";

$response = ["status" => "", "message" => ""];

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
            $fullname = $lastname . ", " . $firstname . " " . $middlename . ".";
            $_SESSION['student_id'] = $idstudent;
            $_SESSION['full_name'] = $fullname;
            $_SESSION['gender'] = $gender;
            $_SESSION['grade_level'] = $grade_level;
            $_SESSION['section'] = $section;
            $_SESSION['verification_code'] = $code;

            $response['status'] = "success";
            $response['redirect'] = "../pages/official_ballot.php";

        }
        if ($VoteStatus == "Casted") {
            $response['status'] = "alreadycasted";

        }
    } else {
        $response['status'] = "error";
    }
    $verifyCode->close();
    $conn->close();
}
header("Content-Type: application/json");
echo json_encode($response);
exit();
?>