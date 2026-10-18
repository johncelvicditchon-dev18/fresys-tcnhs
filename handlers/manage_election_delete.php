<?php
require_once "../includes/database_connection.php";

if (!isset($_POST['delete'])) {
    exit("error");
}

$map = [
    "deleteparty" => ["table" => "partyname_table", "column" => "partyname_ID", "param" => "idparty"],
    "deleteyear" => ["table" => "academicyear_table", "column" => "academicyear_ID", "param" => "idyear"],
    "deletesection" => ["table" => "section_table", "column" => "id", "param" => "idsection"],
    "deleteschool" => ["table" => "school_table", "column" => "schoolid", "param" => "idschool"],
];

$action = $_POST['delete'];

if (!isset($map[$action]) || !isset($_POST[$map[$action]['param']])) {
    exit("error");
}

$id = intval($_POST[$map[$action]['param']]);

$delete = $conn->prepare("DELETE FROM {$map[$action]['table']} WHERE {$map[$action]['column']} = ?");
$delete->bind_param("i", $id);

echo $delete->execute() ? "success" : "error";

$conn->close();
