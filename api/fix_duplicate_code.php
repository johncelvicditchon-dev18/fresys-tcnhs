<?php
header("Content-Type: application/json");
require_once "../includes/database_connection.php";

function generateVerificationCode($conn) {
    do {
        $code = "TCNHS" . str_pad(mt_rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        $check = $conn->prepare("SELECT id FROM student_info WHERE verification_code = ?");
        $check->bind_param("s", $code);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();
    } while ($exists);
    return $code;
}

$data = json_decode(file_get_contents("php://input"), true);
$dupeCode = trim($data['code'] ?? '');

if ($dupeCode === '') {
    echo json_encode(["status" => "error", "message" => "No code provided."]);
    exit;
}

$find = $conn->prepare("SELECT id, first_name, last_name FROM student_info WHERE verification_code = ?");
$find->bind_param("s", $dupeCode);
$find->execute();
$students = $find->get_result();
$find->close();

$count = $students->num_rows;
if ($count <= 1) {
    echo json_encode(["status" => "success", "message" => "No duplicates found for this code.", "fixed" => 0]);
    exit;
}

$update = $conn->prepare("UPDATE student_info SET verification_code = ? WHERE id = ?");
$fixed = 0;

while ($stu = $students->fetch_assoc()) {
    $newCode = generateVerificationCode($conn);
    $update->bind_param("si", $newCode, $stu['id']);
    $update->execute();
    $fixed++;
}
$update->close();
$conn->close();

echo json_encode([
    "status" => "success",
    "message" => $fixed . " student(s) received new unique verification codes.",
    "fixed" => $fixed
]);
?>
