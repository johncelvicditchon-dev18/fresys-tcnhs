<?php
require_once "../includes/database_connection.php";

$counter = $conn->prepare("SELECT SUM(grade_level IN ('Grade 11','Grade 12')) AS SHSTOTAL,
SUM(grade_level IN ('Grade 7','Grade 8','Grade 9','Grade 10')) AS JHSTOTAL,
COUNT(*) AS TOTALREGISTERED,
SUM(grade_level IN ('Grade 11','Grade 12') AND vote_status = 'Casted') AS SHSCASTED,
SUM(grade_level IN ('Grade 11','Grade 12') AND vote_status = 'Available') AS SHSAVAILABLE,
SUM(grade_level IN ('Grade 7','Grade 8','Grade 9','Grade 10') AND vote_status = 'Casted') AS JHSCASTED,
SUM(grade_level IN ('Grade 7','Grade 8','Grade 9','Grade 10') AND vote_status = 'Available') AS JHSAVAILABLE,
SUM(grade_level IN ('Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12') AND vote_status = 'Casted') AS TOTALCASTED,
SUM(grade_level IN ('Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12') AND vote_status = 'Available') AS TOTALAVAILABLE,
SUM(gender = 'Male' AND vote_status = 'Casted') AS MALE_CASTED,
SUM(gender = 'Female' AND vote_status = 'Casted') AS FEMALE_CASTED,
SUM(gender = 'Male' AND vote_status = 'Available') AS MALE_AVAILABLE,
SUM(gender = 'Female' AND vote_status = 'Available') AS FEMALE_AVAILABLE,
SUM(gender = 'Male') AS TOTAL_MALE,
SUM(gender = 'Female') AS TOTAL_FEMALE 
FROM student_info");

$counter->execute();
$result = $counter->get_result();
$row = $result->fetch_assoc();

$emptyQ = $conn->prepare("SELECT COUNT(*) AS TOTAL_EMPTY FROM student_info si WHERE si.vote_status = 'Casted' AND NOT EXISTS (SELECT 1 FROM resulttable rt WHERE CAST(rt.Studentttt_ID AS UNSIGNED) = si.id)");
$emptyQ->execute();
$emptyResult = $emptyQ->get_result();
$emptyRow = $emptyResult->fetch_assoc();
$row['TOTAL_EMPTY'] = $emptyRow['TOTAL_EMPTY'];
$emptyQ->close();

$gradeQ = $conn->prepare("SELECT grade_level,
    COUNT(*) AS total,
    SUM(vote_status = 'Casted') AS casted
    FROM student_info
    GROUP BY grade_level
    ORDER BY CASE grade_level
        WHEN 'Grade 7' THEN 1 WHEN 'Grade 8' THEN 2 WHEN 'Grade 9' THEN 3
        WHEN 'Grade 10' THEN 4 WHEN 'Grade 11' THEN 5 WHEN 'Grade 12' THEN 6
    END");
$gradeQ->execute();
$gradeResult = $gradeQ->get_result();
$gradeTurnout = [];
while ($gr = $gradeResult->fetch_assoc()) {
    $gradeTurnout[] = [
        'grade' => $gr['grade_level'],
        'total' => (int)$gr['total'],
        'casted' => (int)$gr['casted']
    ];
}
$row['GRADE_TURNOUT'] = $gradeTurnout;
$gradeQ->close();

header('Content-Type: application/json');
echo json_encode($row);

?>