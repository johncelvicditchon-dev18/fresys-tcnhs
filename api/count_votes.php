<?php
date_default_timezone_set("Asia/Manila");
require_once "../includes/database_connection.php";
require_once "../handlers/count_function.php"; 

countTheVote($conn); 
echo "VOTE COUNTED AT " . date("M:d:Y H:i:s"); 
?>
