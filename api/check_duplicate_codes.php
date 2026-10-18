<?php
header("Content-Type: application/json");
require_once "../includes/database_connection.php";

$dupes = $conn->query("
    SELECT verification_code, COUNT(*) AS cnt, 
           GROUP_CONCAT(CONCAT(first_name, ' ', middle_name, ' ', last_name) SEPARATOR ', ') AS students,
           GROUP_CONCAT(id SEPARATOR ',') AS ids
    FROM student_info 
    GROUP BY verification_code 
    HAVING COUNT(*) > 1
");

$alerts = [];
if ($dupes && $dupes->num_rows > 0) {
    while ($row = $dupes->fetch_assoc()) {
        $alerts[] = [
            "code" => $row['verification_code'],
            "count" => intval($row['cnt']),
            "students" => $row['students'],
            "ids" => $row['ids']
        ];
    }
}

$recentVotes = $conn->query("
    SELECT id, first_name, middle_name, last_name, grade_level, section, date_casted 
    FROM student_info 
    WHERE vote_status = 'Casted' AND date_casted IS NOT NULL 
    ORDER BY date_casted DESC 
    LIMIT 20
");

$votes = [];
if ($recentVotes && $recentVotes->num_rows > 0) {
    while ($row = $recentVotes->fetch_assoc()) {
        $votes[] = [
            "id" => $row['id'],
            "name" => $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'],
            "grade" => $row['grade_level'],
            "section" => $row['section'],
            "date_casted" => $row['date_casted']
        ];
    }
}

$conn->close();

echo json_encode([
    "status" => "success",
    "total_duplicates" => count($alerts),
    "alerts" => $alerts,
    "recent_votes" => $votes
]);
?>
