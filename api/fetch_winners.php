<?php
require_once "../includes/database_connection.php";

/* MAIN QUERY (same SQL logic) */
$query = "
WITH RankedCandidates AS (
    SELECT CandidateName, position, Election_Name, AcademicYear, PartyName, TotalLevel,
           RANK() OVER (PARTITION BY position ORDER BY TotalLevel DESC) AS rank
    FROM gradelevelresult_tablee
    WHERE position IN (
        'Grade 7 Representative', 'Grade 8 Representative', 'Grade 9 Representative',
        'Grade 10 Representative', 'Grade 11 Representative', 'Grade 12 Representative'
    )
)
SELECT CandidateName, position, Election_Name, AcademicYear, PartyName, TotalLevel
FROM RankedCandidates
WHERE rank <= 2
UNION
SELECT CandidateName, position, Election_Name, AcademicYear, PartyName, TotalLevel
FROM gradelevelresult_tablee
WHERE position NOT IN (
    'Grade 7 Representative', 'Grade 8 Representative', 'Grade 9 Representative',
    'Grade 10 Representative', 'Grade 11 Representative', 'Grade 12 Representative'
)
AND TotalLevel = (
    SELECT MAX(TotalLevel)
    FROM gradelevelresult_tablee g2
    WHERE g2.position = gradelevelresult_tablee.position
)
ORDER BY CASE
    WHEN position = 'President' THEN 1
    WHEN position = 'Vice-President' THEN 2
    WHEN position = 'Secretary' THEN 3
    WHEN position = 'Treasurer' THEN 4
    WHEN position = 'Auditor' THEN 5
    WHEN position = 'P.I.O(SHS)' THEN 6
    WHEN position = 'P.I.O(JHS)' THEN 7
    WHEN position = 'Protocol Officer(SHS)' THEN 8
    WHEN position = 'Protocol Officer(JHS)' THEN 9
    WHEN position = 'Grade 7 Representative' THEN 10
    WHEN position = 'Grade 8 Representative' THEN 11
    WHEN position = 'Grade 9 Representative' THEN 12
    WHEN position = 'Grade 10 Representative' THEN 13
    WHEN position = 'Grade 11 Representative' THEN 14
    WHEN position = 'Grade 12 Representative' THEN 15
    ELSE 16
END
";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {

    $candidateName = $row['CandidateName'];
    $position = $row['position'];
    $electionName = $row['Election_Name'];
    $academicYear = $row['AcademicYear'];
    $partyName = $row['PartyName'];
    $totalVotes = $row['TotalLevel'];

    /* Candidate ID */
    $stmt = $conn->prepare("SELECT Candidate_ID, Photo FROM registeredcandidate_table WHERE CandidateName = ?");
    $stmt->bind_param("s", $candidateName);
    $stmt->execute();
    $stmt->bind_result($candidateID, $Photo);
    $stmt->fetch();
    $stmt->close();

    if (!$candidateID)
        continue;

    /* Position ID */
    $stmt = $conn->prepare("SELECT positionID FROM position WHERE position = ?");
    $stmt->bind_param("s", $position);
    $stmt->execute();
    $stmt->bind_result($positionID);
    $stmt->fetch();
    $stmt->close();

    /* Election ID */
    $stmt = $conn->prepare("SELECT Election_ID FROM electionname_table WHERE Election_Name = ?");
    $stmt->bind_param("s", $electionName);
    $stmt->execute();
    $stmt->bind_result($electionID);
    $stmt->fetch();
    $stmt->close();

    /* Academic Year ID */
    $stmt = $conn->prepare("SELECT Academicyear_ID FROM academicyear_table WHERE AcademicYear = ?");
    $stmt->bind_param("s", $academicYear);
    $stmt->execute();
    $stmt->bind_result($academicYearID);
    $stmt->fetch();
    $stmt->close();

    /* Party ID */
    $stmt = $conn->prepare("SELECT partyname_ID FROM partyname_table WHERE PartyName = ?");
    $stmt->bind_param("s", $partyName);
    $stmt->execute();
    $stmt->bind_result($partyNameID);
    $stmt->fetch();
    $stmt->close();

    /* Check if winner exists */
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM listofwinners
        WHERE CandidateName = ? AND Election__Name = ? AND AcademicYear = ? AND position = ?
    ");
    $stmt->bind_param("ssss", $candidateName, $electionName, $academicYear, $position);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        /* INSERT */
        $stmt = $conn->prepare("
            INSERT INTO listofwinners
            (Candidate__ID, position__ID, position, Election__ID, Election__Name,
             Academicyear__ID, Academicyear, partyname__ID, partyname, CandidateName, TOTAL_VOTE)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iisisisissi", 
            $candidateID,
            $positionID,
            $position,
            $electionID,
            $electionName,
            $academicYearID,
            $academicYear,
            $partyNameID,
            $partyName,
            $candidateName,
            $totalVotes
        );
        $stmt->execute();
        $stmt->close();
    } else {
        /* UPDATE */
        $stmt = $conn->prepare("
            UPDATE listofwinners
            SET TOTAL_VOTE = ?
            WHERE CandidateName = ? AND Election__Name = ? AND AcademicYear = ? AND position = ?
        ");
        $stmt->bind_param(
            "issss",
            $totalVotes,
            $candidateName,
            $electionName,
            $academicYear,
            $position
        );
        $stmt->execute();
        $stmt->close();
    }
    //table
    $imgSrc = !empty($Photo)
        ? "data:image/jpeg;base64," . base64_encode($Photo)
        : "pictures/default_avatar.png";

    echo "
<tr>
    <td>
        <img class='result_photo' src='{$imgSrc}'
             alt='Candidate Photo'
             style='width:50px; height:50px; border-radius:50%; object-fit:cover; box-shadow: 2px 2px 4px rgba(0,0,0,.2); border: 1px solid black'>
    </td>
    <td>{$candidateName}</td>
    <td>{$position}</td>
    <td>{$partyName}</td>
    <td>{$totalVotes}</td>
</tr>";
}
    
$conn->close();
?>