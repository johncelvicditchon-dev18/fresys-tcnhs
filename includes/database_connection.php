<?php
$conn = new mysqli("localhost", "root", "", "system");

if ($conn->connect_error) {
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}
?>  