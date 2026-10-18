<?php
require_once "database_connection.php";

$conn->query("CREATE TABLE IF NOT EXISTS election_archive (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    election_name VARCHAR(100) NOT NULL,
    academic_year VARCHAR(50) NOT NULL,
    archived_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_registered INT DEFAULT 0,
    total_casted INT DEFAULT 0,
    total_empty INT DEFAULT 0,
    archived_by VARCHAR(100) DEFAULT 'Admin',
    results_data LONGTEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS archive_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    archive_id INT NOT NULL,
    CandidateName VARCHAR(150),
    Position VARCHAR(100),
    PartyName VARCHAR(100),
    TotalLevel INT DEFAULT 0,
    Grade_7 INT DEFAULT 0,
    Grade_8 INT DEFAULT 0,
    Grade_9 INT DEFAULT 0,
    Grade_10 INT DEFAULT 0,
    Grade_11 INT DEFAULT 0,
    Grade_12 INT DEFAULT 0,
    FOREIGN KEY (archive_id) REFERENCES election_archive(archive_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo json_encode(["status" => "success", "message" => "Archive tables ready."]);
?>
