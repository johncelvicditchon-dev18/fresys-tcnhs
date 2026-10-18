<?php
require_once "../includes/database_connection.php";

if (!isset($_POST['action'], $_POST['id'])) {
    echo "error";
    exit;
}

$id = intval($_POST['id']);
$action = $_POST['action'];

switch ($action) {

    case 'updateparty':
        $value = trim(strtoupper($_POST['party']));

        $update = $conn->prepare("UPDATE partyname_table SET partyname = ? WHERE partyname_ID = ?");
        break;

    case 'updateyear':
        $value = trim(strtoupper($_POST['year']));

        $update = $conn->prepare("UPDATE academicyear_table SET academicyear = ? WHERE academicyear_ID = ?");
        break;

    case 'updateschool':
        $value = trim(strtoupper($_POST['schoolname']));

        $update = $conn->prepare("UPDATE school_table SET schoolname = ? WHERE schoolid = ?");
        break;

    case 'updatesection':
        $gradeforsection = trim($_POST['gradeforsection']);
        $section = trim(strtoupper($_POST['section']));

        $update = $conn->prepare("UPDATE section_table SET grade_level_to_sec = ?, section = ? WHERE id = ?");
        $update->bind_param("ssi", $gradeforsection, $section, $id);
        echo $update->execute() ? "success" : "error";
        exit;

    default: 
        echo "error";
        exit;
}


$update->bind_param("si", $value, $id);
echo $update->execute() ? "success" : "error";

$update->close();
$conn->close();

?>