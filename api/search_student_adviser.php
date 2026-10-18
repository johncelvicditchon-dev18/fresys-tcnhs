<?php
require_once "../includes/database_connection.php";
$search = trim($_GET['search'] ?? '');

if (strtolower($search) === 'male' || strtolower($search) === 'female') {
    $searchStudentData = $conn->prepare("SELECT id, verification_code, first_name, middle_name, last_name, gender, grade_level, section, vote_status
        FROM student_info WHERE gender = ? ORDER BY CASE grade_level WHEN 'Grade 7' THEN 1 WHEN 'Grade 8' THEN 2 WHEN 'Grade 9' THEN 3 WHEN 'Grade 10' THEN 4 
        WHEN 'Grade 11' THEN 5 WHEN 'Grade 12' THEN 6 ELSE 7 END, CASE gender WHEN 'Male' THEN 1 WHEN 'Female' THEN 2 ELSE 3 END, section ASC, first_name ASC");

    $searchStudentData->bind_param("s", $search);

} else {
    $search = "%$search%";
    $searchStudentData = $conn->prepare("
        SELECT id, verification_code, first_name, middle_name, last_name, gender, grade_level, section, vote_status
        FROM student_info WHERE id LIKE ? OR verification_code LIKE ? OR first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ',middle_name, ' ', last_name) LIKE ?
        OR CONCAT(first_name, ' ', last_name) LIKE ? OR gender LIKE ? OR grade_level LIKE ? OR section LIKE ? OR vote_status LIKE ?
        ORDER BY CASE grade_level WHEN 'Grade 7' THEN 1 WHEN 'Grade 8' THEN 2 WHEN 'Grade 9' THEN 3 WHEN 'Grade 10' THEN 4 
        WHEN 'Grade 11' THEN 5 WHEN 'Grade 12' THEN 6 ELSE 7 END, CASE gender WHEN 'Male' THEN 1 WHEN 'Female' THEN 2 ELSE 3 END, section ASC, first_name ASC");

    $searchStudentData->bind_param("sssssssssss", $search, $search, $search, $search, $search, $search, $search, $search, $search, $search, $search);

}
$searchStudentData->execute();
$result = $searchStudentData->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr data-id='{$row['id']}'>";
        echo "<td><i class='fa-regular fa-pen-to-square editicon'></i></td>";
        echo "<td>{$row['id']}</td>";
        echo "<td  class='verificationcodenone'>{$row['verification_code']}</td>";
        echo "<td>{$row['first_name']}</td>";
        echo "<td>{$row['middle_name']}</td>";
        echo "<td>{$row['last_name']}</td>";
        echo "<td>{$row['gender']}</td>";
        echo "<td>{$row['grade_level']}</td>";
        echo "<td>{$row['section']}</td>";
        if ($row['vote_status'] === "Available") {
            echo "<td><button class='available'><i class='ph ph-check-circle'></i>Available</button></td>";
        } else if ($row['vote_status'] === "Casted") {
            echo "<td><button class='casted'><i class='ph ph-calendar-slash'></i>Casted</button></td>";
        } else {
            echo "<td><button class='loading'>Loading</button></td>";
        }
        echo "<td><i class='fa-solid fa-trash-can btn-delete-table' data-id='{$row['id']}'></td>";
    }
} else {
    echo "<tr><td colspan='11'>No data found!</td></tr> ";
}

$conn->close();

?>