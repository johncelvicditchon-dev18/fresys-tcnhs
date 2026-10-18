<?php
require_once "../includes/database_connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $id = intval($_POST['id']);
    $status = $_POST['status'];

    $updateAccountStatus = $conn->prepare("UPDATE admin_account SET status=? WHERE id=?");
    $updateAccountStatus->bind_param("si", $status, $id);

    if ($updateAccountStatus->execute()) {
        echo "Status updated";
    } else {
        echo "Error updating";
    }

    $updateAccountStatus->close();
    $conn->close();
}
?>