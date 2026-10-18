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

if (!$data || !isset($data['students']) || empty($data['students'])) {
    echo json_encode(["status" => "error", "message" => "No student data received."]);
    exit;
}

$students = $data['students'];
$imported = 0;
$skipped = 0;
$failed = 0;
$errors = [];

$stmt = $conn->prepare("INSERT INTO student_info (verification_code, first_name, middle_name, last_name, gender, grade_level, section, vote_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Available')");
$secCheck = $conn->prepare("SELECT id FROM section_table WHERE grade_level_to_sec = ? AND section = ?");
$secInsert = $conn->prepare("INSERT INTO section_table (grade_level_to_sec, section) VALUES (?, ?)");
$addedSections = [];

foreach ($students as $s) {
    $fn = strtoupper(trim($s['first_name'] ?? ''));
    $mn = strtoupper(trim($s['middle_initial'] ?? ''));
    $ln = strtoupper(trim($s['last_name'] ?? ''));
    $gn = ucwords(strtolower(trim($s['gender'] ?? '')));
    $gl = ucwords(strtolower(trim($s['grade_level'] ?? '')));
    $sc = strtoupper(trim($s['section'] ?? ''));

    if ($fn === '' || $ln === '' || $gn === '' || $gl === '' || $sc === '') {
        $skipped++;
        continue;
    }

    $secCheck->bind_param("ss", $gl, $sc);
    $secCheck->execute();
    if ($secCheck->get_result()->num_rows === 0) {
        $secInsert->bind_param("ss", $gl, $sc);
        $secInsert->execute();
        $key = $gl . '|' . $sc;
        if (!in_array($key, $addedSections)) {
            $addedSections[] = $key;
        }
    }

    $vc = generateVerificationCode($conn);

    $stmt->bind_param("sssssss", $vc, $fn, $mn, $ln, $gn, $gl, $sc);
    if ($stmt->execute()) {
        $imported++;
    } else {
        $failed++;
        $errors[] = $fn . ' ' . $ln . ': ' . $stmt->error;
    }
}

$stmt->close();
$secCheck->close();
$secInsert->close();
$conn->close();

$message = $imported . " student(s) imported successfully.";
if (!empty($addedSections)) {
    $message .= " " . count($addedSections) . " new section(s) added to Manage Election.";
}
if ($skipped > 0) $message .= " " . $skipped . " skipped (incomplete data).";
if ($failed > 0) $message .= " " . $failed . " failed.";

echo json_encode([
    "status" => $imported > 0 ? "success" : "error",
    "message" => $message,
    "imported" => $imported,
    "skipped" => $skipped,
    "failed" => $failed,
    "errors" => $errors,
    "sections_added" => $addedSections
]);
?>
