<?php
session_start();
require_once "../includes/database_connection.php";
header("Content-Type: application/json");

$conn->query("CREATE TABLE IF NOT EXISTS election_archive (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    election_name VARCHAR(100) NOT NULL,
    academic_year VARCHAR(50) NOT NULL,
    archived_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_registered INT DEFAULT 0,
    total_casted INT DEFAULT 0,
    total_empty INT DEFAULT 0,
    archived_by VARCHAR(100) DEFAULT 'Admin',
    results_data LONGTEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS archive_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    archive_id INT NOT NULL,
    CandidateName VARCHAR(150),
    Position VARCHAR(100),
    PartyName VARCHAR(100),
    TotalLevel INT DEFAULT 0,
    Grade_7 INT DEFAULT 0,
    Grade_8 INT DEFAULT 0,
    Grade_9 INT DEFAULT 0,
    Grade_10 INT DEFAULT 0,
    Grade_11 INT DEFAULT 0,
    Grade_12 INT DEFAULT 0,
    FOREIGN KEY (archive_id) REFERENCES election_archive(archive_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$action = $_GET['action'] ?? '';

if ($action === 'save') {
    $data = json_decode(file_get_contents("php://input"), true);
    $electionName = $data['election_name'] ?? 'SSLG';
    $academicYear = $data['academic_year'] ?? '2026-2027';
    $userName = $_SESSION['user_name'] ?? 'Admin';

    $regQ = $conn->prepare("SELECT COUNT(*) AS total FROM student_info");
    $regQ->execute();
    $regResult = $regQ->get_result()->fetch_assoc();
    $totalRegistered = $regResult['total'];

    $castedQ = $conn->prepare("SELECT COUNT(*) AS total FROM student_info WHERE vote_status = 'Casted'");
    $castedQ->execute();
    $castedResult = $castedQ->get_result()->fetch_assoc();
    $totalCasted = $castedResult['total'];

    $emptyQ = $conn->prepare("SELECT COUNT(*) AS TOTAL_EMPTY FROM student_info si WHERE si.vote_status = 'Casted' AND NOT EXISTS (SELECT 1 FROM resulttable rt WHERE CAST(rt.Studentttt_ID AS UNSIGNED) = si.id)");
    $emptyQ->execute();
    $emptyResult = $emptyQ->get_result()->fetch_assoc();
    $totalEmpty = $emptyResult['TOTAL_EMPTY'];

    $save = $conn->prepare("INSERT INTO election_archive (election_name, academic_year, total_registered, total_casted, total_empty, archived_by) VALUES (?, ?, ?, ?, ?, ?)");
    $save->bind_param("ssiiss", $electionName, $academicYear, $totalRegistered, $totalCasted, $totalEmpty, $userName);
    if ($save->execute()) {
        $archiveId = $save->insert_id;
        $save->close();

        $resultsQ = $conn->query("SELECT CandidateName, position, PartyName, TotalLevel, Grade_7, Grade_8, Grade_9, Grade_10, Grade_11, Grade_12 FROM gradelevelresult_tablee");
        if ($resultsQ) {
            $insert = $conn->prepare("INSERT INTO archive_results (archive_id, CandidateName, Position, PartyName, TotalLevel, Grade_7, Grade_8, Grade_9, Grade_10, Grade_11, Grade_12) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            while ($r = $resultsQ->fetch_assoc()) {
                $insert->bind_param("isssiiiiiii", $archiveId, $r['CandidateName'], $r['position'], $r['PartyName'], $r['TotalLevel'], $r['Grade_7'], $r['Grade_8'], $r['Grade_9'], $r['Grade_10'], $r['Grade_11'], $r['Grade_12']);
                $insert->execute();
            }
            $insert->close();
        }

        echo json_encode(["status" => "success", "message" => "Election archived successfully.", "archive_id" => $archiveId]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to archive: " . $save->error]);
    }
} else if ($action === 'list') {
    $list = $conn->query("SELECT * FROM election_archive ORDER BY archived_at DESC");
    $archives = [];
    while ($row = $list->fetch_assoc()) {
        $archives[] = $row;
    }
    echo json_encode($archives);
} else if ($action === 'view') {
    $archiveId = intval($_GET['id'] ?? 0);
    if ($archiveId <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid archive ID."]);
        exit;
    }
    $info = $conn->prepare("SELECT * FROM election_archive WHERE archive_id = ?");
    $info->bind_param("i", $archiveId);
    $info->execute();
    $archiveInfo = $info->get_result()->fetch_assoc();
    $info->close();

    $results = $conn->prepare("SELECT * FROM archive_results WHERE archive_id = ? ORDER BY CASE Position
        WHEN 'President' THEN 1 WHEN 'Vice-President' THEN 2 WHEN 'Secretary' THEN 3
        WHEN 'Treasurer' THEN 4 WHEN 'Auditor' THEN 5 WHEN 'P.I.O(SHS)' THEN 6
        WHEN 'P.I.O(JHS)' THEN 7 WHEN 'Protocol Officer(SHS)' THEN 8 WHEN 'Protocol Officer(JHS)' THEN 9
        WHEN 'Grade 7 Representative' THEN 10 WHEN 'Grade 8 Representative' THEN 11
        WHEN 'Grade 9 Representative' THEN 12 WHEN 'Grade 10 Representative' THEN 13
        WHEN 'Grade 11 Representative' THEN 14 WHEN 'Grade 12 Representative' THEN 15 ELSE 16 END, TotalLevel DESC");
    $results->bind_param("i", $archiveId);
    $results->execute();
    $rResult = $results->get_result();
    $archiveResults = [];
    while ($row = $rResult->fetch_assoc()) {
        $archiveResults[] = $row;
    }
    $results->close();

    echo json_encode(["status" => "success", "archive" => $archiveInfo, "results" => $archiveResults]);
} else if ($action === 'delete') {
    $archiveId = intval($_GET['id'] ?? 0);
    if ($archiveId <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid archive ID."]);
        exit;
    }
    $del = $conn->prepare("DELETE FROM election_archive WHERE archive_id = ?");
    $del->bind_param("i", $archiveId);
    if ($del->execute()) {
        echo json_encode(["status" => "success", "message" => "Archive deleted."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed."]);
    }
    $del->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action."]);
}

$conn->close();
?>
