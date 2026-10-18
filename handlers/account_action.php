<?php
    require_once "../includes/database_connection.php";
if (isset($_POST['action'])) {
    $action = $_POST['action'];


    if ($action === "update") {
        $id = intval($_POST['id']);

        $name = strtoupper(trim($_POST['name']));
        $username = trim($_POST['username']);
        $password = trim($_POST['password'] ?? '');
        $access = trim($_POST['access']);

        if (empty($name) || empty($username) || empty($access)) {
            echo "error_empty";
            exit;
        }

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateAccount = $conn->prepare("UPDATE admin_account SET name = ?, username = ?, password = ?, admin_adviser = ? WHERE id = ?");
            $updateAccount->bind_param("ssssi", $name, $username, $hashedPassword, $access, $id);
        } else {
            $updateAccount = $conn->prepare("UPDATE admin_account SET name = ?, username = ?, admin_adviser = ? WHERE id = ?");
            $updateAccount->bind_param("sssi", $name, $username, $access, $id);
        }

        if ($updateAccount->execute()) {
            echo "success";
        } else {
            echo "error";
        }

    } elseif ($action === "delete") {
        // DELETE account
        $id = intval($_POST['id']);

        $deleteAccount = $conn->prepare("DELETE FROM admin_account WHERE id= ?");
        $deleteAccount->bind_param("i", $id);

        if ($deleteAccount->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    }

    $conn->close();
}
?>