<?php
require_once "../includes/database_connection.php";

$add = $_POST['add'];

switch ($add) {

    case 'party':
        $value = strtoupper(trim($_POST['party']));
        $stmt = $conn->prepare("INSERT INTO partyname_table (partyname) VALUES (?)");
        $stmt->bind_param("s", $value);
        echo $stmt->execute() ? "success" : "error";
        break;

    case 'year':
        $value = trim($_POST['year']);
        $stmt = $conn->prepare("INSERT INTO academicyear_table (academicyear) VALUES (?)");
        $stmt->bind_param("s", $value);
        echo $stmt->execute() ? "success" : "error";
        break;

    case 'section':
        $section = strtoupper(trim($_POST['section']));
        $gradeLevel = strtoupper(trim($_POST['gradeLevel']));

        $stmt = $conn->prepare("INSERT INTO section_table (grade_level_to_sec, section) VALUES (?, ?)");
        $stmt->bind_param("ss", $gradeLevel, $section);
        echo $stmt->execute() ? "success" : "error";
        break;

    case 'school':
        $value = strtoupper(trim($_POST['school']));
        $stmt = $conn->prepare("INSERT INTO school_table (schoolname) VALUES (?)");
        $stmt->bind_param("s", $value);
        echo $stmt->execute() ? "success" : "error";
        break;

    default:
        echo "error";
        break;
}

$conn->close();
?>
