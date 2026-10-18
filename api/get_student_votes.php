<?php
header("Content-Type: application/json");
require_once "../includes/database_connection.php";

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid student ID."]);
    exit;
}

$student = $conn->prepare("SELECT id, first_name, middle_name, last_name, grade_level, section, vote_status FROM student_info WHERE id = ?");
$student->bind_param("i", $id);
$student->execute();
$stu = $student->get_result()->fetch_assoc();
$student->close();

if (!$stu) {
    echo json_encode(["status" => "error", "message" => "Student not found."]);
    exit;
}

$votes = [];
if ($stu['vote_status'] === 'Casted') {
    $stmt = $conn->prepare("SELECT Candidateee_Name, Positionnn, Partynameeee_ID, Partyname, Grade_levelll FROM resulttable WHERE CAST(Studentttt_ID AS UNSIGNED) = ? ORDER BY CASE Positionnn WHEN 'President' THEN 1 WHEN 'Vice-President' THEN 2 WHEN 'Secretary' THEN 3 WHEN 'Treasurer' THEN 4 WHEN 'Auditor' THEN 5 WHEN 'P.I.O(SHS)' THEN 6 WHEN 'P.I.O(JHS)' THEN 7 WHEN 'Protocol Officer(SHS)' THEN 8 WHEN 'Protocol Officer(JHS)' THEN 9 ELSE 10 END");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $votes[] = $row;
    }
    $stmt->close();
}

$conn->close();

echo json_encode([
    "status" => "success",
    "student" => $stu,
    "votes" => $votes
]);
?>
