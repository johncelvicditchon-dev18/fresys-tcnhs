<?php
require_once "../includes/database_connection.php";

// MySQL query
$query = "
SELECT CandidateName, position, Election_Name, Academicyear, PartyName, TotalLevel,
CASE
    WHEN position IN ('President','Vice-President','Secretary','Auditor','Treasurer') THEN 
        (SELECT COUNT(*) FROM student_info WHERE grade_level IN ('Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12'))
    WHEN position IN ('P.I.O(SHS)','Protocol Officer(SHS)') THEN
        (SELECT COUNT(*) FROM student_info WHERE grade_level IN ('Grade 10','Grade 11','Grade 12'))
    WHEN position = 'Grade 11 Representative' THEN
        (SELECT COUNT(*) FROM student_info WHERE grade_level = 'Grade 10')
    WHEN position = 'Grade 12 Representative' THEN
        (SELECT COUNT(*) FROM student_info WHERE grade_level = 'Grade 11')
    WHEN position = 'Grade 7 Representative' THEN
        (SELECT COUNT(*) FROM student_info WHERE grade_level = 'Grade 7')
    WHEN position = 'Grade 8 Representative' THEN
        (SELECT COUNT(*) FROM student_info WHERE grade_level = 'Grade 7')
    WHEN position = 'Grade 9 Representative' THEN
        (SELECT COUNT(*) FROM student_info WHERE grade_level = 'Grade 8')
    WHEN position = 'Grade 10 Representative' THEN
        (SELECT COUNT(*) FROM student_info WHERE grade_level = 'Grade 9')
    WHEN position IN ('P.I.O(JHS)','Protocol Officer(JHS)') THEN
        (SELECT COUNT(*) FROM student_info WHERE grade_level IN ('Grade 7','Grade 8','Grade 9'))
    ELSE 0
END AS RegisteredStudents
FROM gradelevelresult_tablee
ORDER BY 
  CASE 
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
  END";



$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

      
        $selectPhoto = $conn->prepare("SELECT Photo FROM registeredcandidate_table WHERE CandidateName = ?");
        $selectPhoto->bind_param("s", $row['CandidateName']);
        $selectPhoto->execute();
        $photoResult = $selectPhoto->get_result();
        $photoRow = $photoResult->fetch_assoc();


        if ($photoRow && !empty($photoRow['Photo'])) {
            $imageData = $photoRow['Photo'];
            $base64 = 'data:image/jpeg;base64,' . base64_encode($imageData);
        } else {
            $imageData = file_get_contents('pictures/default.jpg');
            $base64 = 'data:image/jpeg;base64,' . base64_encode($imageData);
        }

        echo "<tr>";
        echo "<td><img src='{$base64}' style='width:50px; height:50px; border-radius:50%; object-fit:cover; box-shadow: 2px 2px 4px rgba(0,0,0,.2); border: 1px solid black'></td>";
        echo "<td>{$row['CandidateName']}</td>";
        echo "<td>{$row['position']}</td>";
        echo "<td>{$row['TotalLevel']}</td>";
        echo "<td>{$row['RegisteredStudents']}</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No results found</td></tr>";
}

?>