<?php
require_once "../includes/database_connection.php";

function countTheVote($conn)
{
    $pstInsert = null;
    $pstUpdate = null;
    $pstCheckExist = null;
    $rsCheck = null;

    try {
        // SQL query to fetch the data
        $query = "
SELECT 
    registeredcandidate_table.Candidate_ID, 
    resulttable.Candidateee_Name, 
    position.positionID, 
    position.Position, 
    electionname_table.Election_ID, 
    electionname_table.Election_Name, 
    academicyear_table.academicYear_ID, 
    academicyear_table.AcademicYear, 
    partyname_table.partyName_ID, 
    partyname_table.PartyName, 
    COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 7' THEN 1 END) AS Grade7, 
    COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 8' THEN 1 END) AS Grade8, 
    COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 9' THEN 1 END) AS Grade9, 
    COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 10' THEN 1 END) AS Grade10, 
    COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 11' THEN 1 END) AS Grade11, 
    COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 12' THEN 1 END) AS Grade12, 
    (COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 7' THEN 1 END) +
     COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 8' THEN 1 END) +
     COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 9' THEN 1 END) +
     COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 10' THEN 1 END) +
     COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 11' THEN 1 END) +
     COUNT(CASE WHEN resulttable.Grade_levelll = 'Grade 12' THEN 1 END)) AS Total
FROM resulttable
JOIN registeredcandidate_table ON resulttable.Candidateee_Name = registeredcandidate_table.CandidateName
JOIN position ON registeredcandidate_table.positionID = position.positionID
JOIN electionname_table ON registeredcandidate_table.Election_ID = electionname_table.Election_ID
JOIN academicyear_table ON registeredcandidate_table.academicYear_ID = academicyear_table.academicYear_ID
JOIN partyname_table ON registeredcandidate_table.partyName_ID = partyname_table.partyName_ID
WHERE resulttable.Positionnn IN ('President', 'Vice-President', 'Secretary', 'Treasurer', 'Auditor', 
'P.I.O(SHS)', 'P.I.O(JHS)', 'Protocol Officer(SHS)', 'Protocol Officer(JHS)', 
'Grade 7 Representative', 'Grade 8 Representative', 'Grade 9 Representative', 
'Grade 10 Representative', 'Grade 11 Representative', 'Grade 12 Representative')
GROUP BY 
    resulttable.Candidateee_Name, 
    position.positionID, 
    position.Position, 
    electionname_table.Election_ID, 
    electionname_table.Election_Name, 
    academicyear_table.academicYear_ID, 
    academicyear_table.AcademicYear, 
    partyname_table.partyName_ID, 
    partyname_table.PartyName
ORDER BY CASE position.Position
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
END
";

        // Execute SELECT query
        $rs = $conn->query($query);

        // Prepare INSERT query
        $insertQuery = "INSERT INTO gradelevelresult_tablee 
        (Candidate_ID, CandidateName, positionID, position, Election_ID, Election_Name, academicYear_ID, AcademicYear, partyName_ID, PartyName, Grade_7, Grade_8, Grade_9, Grade_10, Grade_11, Grade_12, TotalLevel)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        Grade_7 = VALUES(Grade_7),
        Grade_8 = VALUES(Grade_8),
        Grade_9 = VALUES(Grade_9),
        Grade_10 = VALUES(Grade_10),
        Grade_11 = VALUES(Grade_11),
        Grade_12 = VALUES(Grade_12),
        TotalLevel = VALUES(TotalLevel)";
        $pstInsert = $conn->prepare($insertQuery);

        // Prepare check if exists query
        $checkExistQuery = "SELECT COUNT(*) FROM gradelevelresult_tablee WHERE Candidate_ID = ? AND position = ?";
        $pstCheckExist = $conn->prepare($checkExistQuery);

        // Loop through results
        while ($row = $rs->fetch_assoc()) {
            $candidateID = $row['Candidate_ID'];
            $candidateName = $row['Candidateee_Name'];
            $positionID = $row['positionID'];
            $position = $row['Position'];
            $electionID = $row['Election_ID'];
            $electionName = $row['Election_Name'];
            $academicYearID = $row['academicYear_ID'];
            $academicYear = $row['AcademicYear'];
            $partyNameID = $row['partyName_ID'];
            $partyName = $row['PartyName'];
            $grade7 = $row['Grade7'];
            $grade8 = $row['Grade8'];
            $grade9 = $row['Grade9'];
            $grade10 = $row['Grade10'];
            $grade11 = $row['Grade11'];
            $grade12 = $row['Grade12'];
            $total = $row['Total'];

            // Check if exists
            $pstCheckExist->bind_param("is", $candidateID, $position);
            $pstCheckExist->execute();
            $rsCheck = $pstCheckExist->get_result();
            $rowCheck = $rsCheck->fetch_row();

            if ($rowCheck[0] > 0) {
                // Update existing
                $updateQuery = "UPDATE gradelevelresult_tablee SET 
                    Grade_7 = ?, Grade_8 = ?, Grade_9 = ?, Grade_10 = ?, 
                    Grade_11 = ?, Grade_12 = ?, TotalLevel = ? 
                    WHERE Candidate_ID = ? AND position = ?";
                $pstUpdate = $conn->prepare($updateQuery);
                $pstUpdate->bind_param("iiiiiiiis", $grade7, $grade8, $grade9, $grade10, $grade11, $grade12, $total, $candidateID, $position);
                $pstUpdate->execute();
                $pstUpdate->close();
            } else {
                // Insert new
                $pstInsert->bind_param(
                    "isisisisisiiiiiii",
                    $candidateID, 
                    $candidateName,
                    $positionID,
                    $position,
                    $electionID,
                    $electionName,
                    $academicYearID,
                    $academicYear,
                    $partyNameID,
                    $partyName,
                    $grade7,
                    $grade8,
                    $grade9,
                    $grade10,
                    $grade11,
                    $grade12,
                    $total
                );
                $pstInsert->execute();
            }
        }


        $pstInsert->close();
        $pstCheckExist->close();

    } catch (Exception $ex) {
        echo "Error: " . $ex->getMessage();
    }
}
?>