<?php
require_once "../includes/database_connection.php";
header('Content-Type: application/json');

$query = "
SELECT CandidateName, position, PartyName, TotalLevel
FROM gradelevelresult_tablee
ORDER BY CASE position
    WHEN 'President' THEN 1
    WHEN 'Vice-President' THEN 2
    WHEN 'Secretary' THEN 3
    WHEN 'Treasurer' THEN 4
    WHEN 'Auditor' THEN 5
    WHEN 'P.I.O(SHS)' THEN 6
    WHEN 'P.I.O(JHS)' THEN 7
    WHEN 'Protocol Officer(SHS)' THEN 8
    WHEN 'Protocol Officer(JHS)' THEN 9
    WHEN 'Grade 7 Representative' THEN 10
    WHEN 'Grade 8 Representative' THEN 11
    WHEN 'Grade 9 Representative' THEN 12
    WHEN 'Grade 10 Representative' THEN 13
    WHEN 'Grade 11 Representative' THEN 14
    WHEN 'Grade 12 Representative' THEN 15
    ELSE 16
END, TotalLevel DESC
";

$result = $conn->query($query);

$positionOrder = [
    'President', 'Vice-President', 'Secretary', 'Treasurer', 'Auditor',
    'P.I.O(SHS)', 'P.I.O(JHS)', 'Protocol Officer(SHS)', 'Protocol Officer(JHS)',
    'Grade 7 Representative', 'Grade 8 Representative', 'Grade 9 Representative',
    'Grade 10 Representative', 'Grade 11 Representative', 'Grade 12 Representative'
];

$grouped = [];
while ($row = $result->fetch_assoc()) {
    $pos = $row['position'];
    $stmt = $conn->prepare("SELECT Photo FROM registeredcandidate_table WHERE CandidateName = ? AND Position = ?");
    $stmt->bind_param("ss", $row['CandidateName'], $pos);
    $stmt->execute();
    $stmt->bind_result($Photo);
    $stmt->fetch();
    $stmt->close();

    $photo = '';
    if (!empty($Photo)) {
        $header = substr($Photo, 0, 8);
        $isPNG = (substr($header, 0, 4) === "\x89PNG");
        if ($isPNG) {
            $photo = 'data:image/png;base64,' . base64_encode($Photo);
        } else {
            $photo = 'data:image/jpeg;base64,' . base64_encode($Photo);
        }
    }

    if (!isset($grouped[$pos])) {
        $grouped[$pos] = [];
    }
    $grouped[$pos][] = [
        'name' => $row['CandidateName'],
        'party' => $row['PartyName'],
        'votes' => (int)$row['TotalLevel'],
        'photo' => $photo
    ];
}

$ordered = [];
foreach ($positionOrder as $pos) {
    if (isset($grouped[$pos])) {
        $ordered[] = ['position' => $pos, 'candidates' => $grouped[$pos]];
    }
}

echo json_encode($ordered);
$conn->close();
?>
