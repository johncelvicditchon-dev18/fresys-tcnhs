<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
header("Content-Type: application/json");
require_once "../includes/database_connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

$electionID = intval($_POST['election'] ?? 0);
$yearID = intval($_POST['year'] ?? 0);
$partyID = intval($_POST['party'] ?? 0);

if ($electionID <= 0 || $yearID <= 0 || $partyID <= 0) {
    echo json_encode(["status" => "error", "message" => "Please select election, year, and party."]);
    exit;
}

$selElection = $conn->prepare("SELECT Election_Name FROM electionname_table WHERE Election_ID = ?");
if ($selElection === false) {
    echo json_encode(["status" => "error", "message" => "Failed to prepare election query: " . $conn->error]);
    exit;
}
$selElection->bind_param("i", $electionID);
$selElection->execute();
$electionRow = $selElection->get_result()->fetch_assoc();
$selElection->close();
if (!$electionRow) { echo json_encode(["status" => "error", "message" => "Invalid election."]); exit; }
$electionName = $electionRow['Election_Name'];

$selYear = $conn->prepare("SELECT academicyear FROM academicyear_table WHERE academicyear_ID = ?");
if ($selYear === false) {
    echo json_encode(["status" => "error", "message" => "Failed to prepare year query: " . $conn->error]);
    exit;
}
$selYear->bind_param("i", $yearID);
$selYear->execute();
$yearRow = $selYear->get_result()->fetch_assoc();
$selYear->close();
if (!$yearRow) { echo json_encode(["status" => "error", "message" => "Invalid academic year."]); exit; }
$academicYear = $yearRow['academicyear'];

$selParty = $conn->prepare("SELECT partyname FROM partyname_table WHERE partyname_ID = ?");
if ($selParty === false) {
    echo json_encode(["status" => "error", "message" => "Failed to prepare party query: " . $conn->error]);
    exit;
}
$selParty->bind_param("i", $partyID);
$selParty->execute();
$partyRow = $selParty->get_result()->fetch_assoc();
$selParty->close();
if (!$partyRow) { echo json_encode(["status" => "error", "message" => "Invalid party."]); exit; }
$partyName = $partyRow['partyname'];

$selPos = $conn->prepare("SELECT positionID FROM `position` WHERE `position` = ?");
if ($selPos === false) {
    echo json_encode(["status" => "error", "message" => "Failed to prepare position query: " . $conn->error]);
    exit;
}

$insert = $conn->prepare("INSERT INTO registeredcandidate_table (CandidateName, positionID, Position, Election_ID, ElectionName, academicYear_ID, AcademicYear, partyName_ID, PartyName, Photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if ($insert === false) {
    echo json_encode(["status" => "error", "message" => "Failed to prepare insert query: " . $conn->error]);
    exit;
}

$positions = [
    'president' => 'President',
    'vice-president' => 'Vice-President',
    'secretary' => 'Secretary',
    'treasurer' => 'Treasurer',
    'auditor' => 'Auditor',
    'pio-shs' => 'P.I.O(SHS)',
    'pio-jhs' => 'P.I.O(JHS)',
    'protocol-shs' => 'Protocol Officer(SHS)',
    'protocol-jhs' => 'Protocol Officer(JHS)',
    'grade-7-rep' => 'Grade 7 Representative',
    'grade-8-rep' => 'Grade 8 Representative',
    'grade-9-rep' => 'Grade 9 Representative',
    'grade-10-rep' => 'Grade 10 Representative',
    'grade-11-rep' => 'Grade 11 Representative',
    'grade-12-rep' => 'Grade 12 Representative'
];

$inserted = 0;
$skipped = 0;
$errors = [];

foreach ($positions as $key => $positionName) {
    $isRep = strpos($key, '-rep') !== false;
    $slots = $isRep ? 2 : 1;

    $selPos->bind_param("s", $positionName);
    $selPos->execute();
    $posRow = $selPos->get_result()->fetch_assoc();
    if (!$posRow) { $errors[] = $positionName . ': position not found'; continue; }
    $positionID = $posRow['positionID'];

    for ($i = 0; $i < $slots; $i++) {
        $nameKey = 'name-' . $key . '-' . $i;
        $photoKey = 'photo-' . $key . '-' . $i;

        $candidateName = strtoupper(trim($_POST[$nameKey] ?? ''));
        if ($candidateName === '') { $skipped++; continue; }

        $photoData = null;
        if (isset($_FILES[$photoKey]) && $_FILES[$photoKey]['error'] === 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($_FILES[$photoKey]['type'], $allowedTypes) && $_FILES[$photoKey]['size'] <= 20 * 1024 * 1024) {
                $photoData = file_get_contents($_FILES[$photoKey]['tmp_name']);
            }
        }

        $insert->bind_param("sisisisiss", $candidateName, $positionID, $positionName, $electionID, $electionName, $yearID, $academicYear, $partyID, $partyName, $photoData);
        if ($insert->execute()) {
            $inserted++;
        } else {
            $errors[] = $candidateName . ': ' . $insert->error;
        }
    }
}

$insert->close();
$selPos->close();
$conn->close();

$message = $inserted . ' candidate(s) registered successfully.';
if ($skipped > 0) $message .= ' ' . $skipped . ' skipped (empty name).';
if (!empty($errors)) $message .= ' ' . count($errors) . ' failed.';

echo json_encode([
    "status" => $inserted > 0 ? "success" : "error",
    "message" => $message,
    "inserted" => $inserted,
    "skipped" => $skipped,
    "errors" => $errors
]);
?>
