<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

date_default_timezone_set('Asia/Manila');
require_once "../includes/database_connection.php";
session_start();
header("Content-Type: application/json");


$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

$student_id = $data['student_id'];
$student_name = $data['student_fullname'];
$student_grade = $data['student_grade'];



$stmt = $conn->prepare("SELECT vote_status FROM student_info WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($student['vote_status'] === 'Casted') {
    echo json_encode(["status" => "error", "message" => "You have already voted."]);
    exit;
}

function insertVoteByName($conn, $candidate_name, $student_id, $student_name, $student_grade)
{
    if (empty($candidate_name))
        return;

    $select = $conn->prepare("SELECT Candidate_ID, CandidateName, positionID, Position, Election_ID, ElectionName, academicYear_ID, AcademicYear,
            partyName_ID, PartyName FROM registeredcandidate_table WHERE CandidateName = ? LIMIT 1 ");
    $select->bind_param("s", $candidate_name);
    $select->execute();
    $candidate = $select->get_result()->fetch_assoc();
    $select->close();

    if (!$candidate)
        return;

    $insert = $conn->prepare("INSERT INTO resulttable (Candidateee_Name, CandidateID, PositionnnID, Positionnn, Electionnnn_ID, Electionnn_Name,
    Academicyearrrr_ID, Academicyear, Partynameeee_ID, Partyname, student, Studentttt_ID, Grade_levelll) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param(
        "siisssissssss",
        $candidate['CandidateName'],
        $candidate['Candidate_ID'],
        $candidate['positionID'],
        $candidate['Position'],
        $candidate['Election_ID'],
        $candidate['ElectionName'],
        $candidate['academicYear_ID'],
        $candidate['AcademicYear'],
        $candidate['partyName_ID'],
        $candidate['PartyName'],
        $student_name,
        $student_id,
        $student_grade
    );

    $insert->execute();
    $insert->close();
}

$positions = [
    "Grade 7" => ['president', 'vice_president', 'secretary', 'treasurer', 'auditor', 'pio_jhs', 'protocol_jhs', 'grade_8_rep1', 'grade_8_rep2'],
    "Grade 8" => ['president', 'vice_president', 'secretary', 'treasurer', 'auditor', 'pio_jhs', 'protocol_jhs', 'grade_9_rep1', 'grade_9_rep2'],
    "Grade 9" => ['president', 'vice_president', 'secretary', 'treasurer', 'auditor', 'pio_jhs', 'protocol_jhs', 'grade_10_rep1', 'grade_10_rep2'],
    "Grade 10" => ['president', 'vice_president', 'secretary', 'treasurer', 'auditor', 'pio_shs', 'protocol_shs', 'grade_11_rep1', 'grade_11_rep2'],
    "Grade 11" => ['president', 'vice_president', 'secretary', 'treasurer', 'auditor', 'pio_shs', 'protocol_shs', 'grade_12_rep1', 'grade_12_rep2'],
    "Grade 12" => ['president', 'vice_president', 'secretary', 'treasurer', 'auditor', 'pio_shs', 'protocol_shs']
];

foreach ($positions[$student_grade] as $position) {

    insertVoteByName($conn, $data[$position] ?? '', $student_id, $student_name, $student_grade);

}

$votestatus = "Casted";
$date = date("Y-m-d H:i:s");

$updateStudent = $conn->prepare("UPDATE student_info SET vote_status = ?, date_casted = ? WHERE id = ?");
$updateStudent->bind_param("ssi", $votestatus, $date, $student_id);
$updateStudent->execute();
$updateStudent->close();





echo json_encode([
    "status" => "success",
    "message" => "Votes successfully recorded"
]);
?>