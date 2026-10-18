<?php
require_once "../includes/database_connection.php";

if (!isset($_POST['gradeselected'])) {
    echo json_encode([]);
    exit;
}
$gradeselected = $_POST['gradeselected'];

$selectSection = $conn->prepare("SELECT DISTINCT section FROM section_table WHERE grade_level_to_sec =? ORDER BY  section ASC");
$selectSection->bind_param("s", $gradeselected);
$selectSection->execute();
$result = $selectSection->get_result();
 
$sections = [];
while ($row = $result->fetch_assoc()) {
    $sections[] = $row['section'];
}

header('Content-Type: application/json');
echo json_encode($sections);

$conn->close();
?>