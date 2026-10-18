<?php
require_once "../includes/database_connection.php";
$search_account = trim($_GET['search-account'] ?? '');

$search_account = "%$search_account%";

$searchAccount = $conn->prepare("SELECT id, name, username, status, admin_adviser, acc_created FROM admin_account WHERE
id LIKE ? OR name LIKE ? OR username LIKE ? OR status LIKE ? OR admin_adviser LIKE ? OR acc_created LIKE ?");
$searchAccount->bind_param("ssssss", $search_account, $search_account, $search_account, $search_account, $search_account, $search_account);
$searchAccount->execute();
$result = $searchAccount->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr data-id='{$row['id']}'>";
        echo "<td><i class='fa-regular fa-pen-to-square editaccount'></i></td>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['username']}</td>";
        $statusColor = ($row['status'] === "Approved") ? "#10b981" : "#ff5252";
        echo "<td><button class='status status-btn'  data-id='{$row['id']}' style='background:$statusColor'>{$row['status']}</button></td>";
        echo "<td>{$row['admin_adviser']}</td>";
        echo "<td>{$row['acc_created']}</td>";
        echo "<td><i class='fa-solid fa-trash-can btn-delete-acc' data-id='{$row['id']}'></i></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan = '8'>No data found</td></tr>";
}
$conn->close();
?>