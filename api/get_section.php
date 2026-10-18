<?php
require_once "../includes/database_connection.php";

if (isset($_GET['grade'])) {
    $grade = $_GET['grade'];

  
    $retrieveSection = $conn->prepare("SELECT DISTINCT section AS name FROM student_info WHERE grade_level = ?");
    $retrieveSection->bind_param("s", $grade);
    $retrieveSection->execute();
    $result = $retrieveSection->get_result();

    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row['name'];
    }

    header('Content-Type: application/json');
    echo json_encode($sections);
}
$conn->close();
?>
