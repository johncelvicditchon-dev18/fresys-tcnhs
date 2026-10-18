<?php
require_once "../includes/database_connection.php";

if (isset($_POST['action'])) {

    $action = $_POST['action'];

    if ($action === "update") {
        $id = intval($_POST['id']);

        $firstname = strtoupper(trim($_POST['firstname']));
        $middlename = strtoupper(trim($_POST['middlename']));
        $lastname = strtoupper(trim($_POST['lastname']));
        $gender = $_POST['gender'];
        $gradelevel = $_POST['gradelevel'];
        $section = $_POST['section'];

        if (empty($firstname) || empty($lastname) || empty($gender) || empty($gradelevel) || empty($section)) {
            echo "error_empty";
            exit;
        }
        $updateStudent = $conn->prepare("UPDATE student_info SET first_name = ?, middle_name = ?, last_name = ?, gender = ?, grade_level = ?, section = ?  WHERE id =?
        ORDER BY CASE grade_level WHEN 'Grade 7' THEN 1 WHEN 'Grade 8' THEN 2 WHEN 'Grade 9' THEN 3 WHEN 'Grade 10' THEN 4 
        WHEN 'Grade 11' THEN 5 WHEN 'Grade 12' THEN 6 ELSE 7 END, CASE gender WHEN 'Male' THEN 1 WHEN 'Female' THEN 2 ELSE 3 END, section ASC, first_name ASC");
        $updateStudent->bind_param("ssssssi", $firstname, $middlename, $lastname, $gender, $gradelevel, $section, $id);

        if ($updateStudent->execute()) {
            echo "success";
        } else {
            echo "error";
        }

    } elseif ($action === "delete") {
        $id = intval($_POST['id']);

        $deleteStudent = $conn->prepare("DELETE FROM student_info WHERE id = ?");
        $deleteStudent->bind_param("i", $id);


        if ($deleteStudent->execute()) {
            echo "success";
        } else {
            echo "error";
        }

    }
}

?>