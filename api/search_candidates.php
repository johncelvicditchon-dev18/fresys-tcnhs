<?php
require_once "../includes/database_connection.php";

$candidate = trim($_GET['search-candidate'] ?? '');
$candidate = "%$candidate%";

$searchCandidates = $conn->prepare("SELECT `Candidate_ID`, `CandidateName`,  `Position`,`ElectionName`, 
 `AcademicYear`,  `PartyName`, Photo FROM registeredcandidate_table WHERE Candidate_ID LIKE  ? OR CandidateName LIKE ? OR Position LIKE ? OR ElectionName LIKE ? OR
 AcademicYear LIKE ? OR PartyName LIKE ? ORDER BY CASE Position WHEN 'President' THEN 1
                            WHEN 'Vice-President' THEN 2 WHEN 'Secretary' THEN 3 WHEN 'Treasurer' THEN 4 WHEN 'Auditor' THEN 5 WHEN 'P.I.O(SHS)' THEN 6 WHEN 'P.I.O(JHS)' THEN 7
                            WHEN 'Protocol Officer(SHS)' THEN 8 WHEN 'Protocol Officer(JHS)' THEN 9 WHEN 'Grade 7 Representative' THEN 10 WHEN 'Grade 8 Representative' THEN 11
                            WHEN 'Grade 9 Representative' THEN 12 WHEN 'Grade 10 Representative' THEN 13 WHEN 'Grade 11 Representative' THEN 14 WHEN 'Grade 12 Representative' THEN 15 ELSE 16 END");
$searchCandidates->bind_param("ssssss", $candidate, $candidate, $candidate, $candidate, $candidate, $candidate);
$searchCandidates->execute();
$result = $searchCandidates->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $photoData = $row['Photo'];

        if (!empty($photoData)) {

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($photoData);

            $imgBase64 = base64_encode($photoData);

            $imgTag = "<img class='candidate-image' src='data:$mime;base64,$imgBase64' alt='Candidate Photo' style='width:100px; height:100px; object-fit:cover;
                                        border-radius: 50%; box-shadow: 2px 2px 4px rgba(0,0,0,.2); border: 2px solid black; cursor: pointer;'>";
        } else {

            $imgTag = "<img src='../pictures/user_candidate.jpg' alt='No Photo' style='width:50px; height:50px; object-fit:cover;'>";
        }

        $photoBase64 = !empty($photoData) ? "data:$mime;base64," . base64_encode($photoData) : "../pictures/user_candidate.jpg";

        echo "<tr data-id='{$row['Candidate_ID']}' data-photo='{$photoBase64}'>";
        echo "<td><i class='fa-regular fa-pen-to-square editcandidate'></i></td>";
        echo "<td>{$row['Candidate_ID']}</td>";
        echo "<td>$imgTag</td>";
        echo "<td>{$row['CandidateName']}</td>";
        echo "<td>{$row['Position']}</td>";
        echo "<td>{$row['PartyName']}</td>";
        echo "<td>{$row['AcademicYear']}</td>";
        echo "<td>{$row['ElectionName']}</td>";
        echo "<td><i class='fa-solid fa-trash-can btn-delete-candidate' data-id='{$row['Candidate_ID']}'></i></td>";
        echo "</tr>";
    }

} else {
    echo "<tr><td colspan = '9'>No data found</td></tr>";
}




?>