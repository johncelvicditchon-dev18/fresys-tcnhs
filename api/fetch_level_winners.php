<?php
require_once "../includes/database_connection.php";

// Join gradelevelresult_tablee with registeredcandidate_table to get the photo
$sql = "
    SELECT g.*, r.Photo 
    FROM gradelevelresult_tablee g
    LEFT JOIN registeredcandidate_table r 
        ON g.CandidateName = r.CandidateName
    ORDER BY CASE g.position
        WHEN 'President' THEN 1
        WHEN 'Vice-President' THEN 2
        WHEN 'Secretary' THEN 3
        WHEN 'Treasurer' THEN 4
        WHEN 'Auditor' THEN 5
        WHEN 'P.I.O(SHS)' THEN 6
        WHEN 'P.I.O(JHS)' THEN 7
        WHEN 'Protocol Officer(SHS)' THEN 8
        WHEN 'Protocol Officer(JHS)' THEN 9
        WHEN 'Grade 7 Representative' THEN 10
        WHEN 'Grade 8 Representative' THEN 11
        WHEN 'Grade 9 Representative' THEN 12
        WHEN 'Grade 10 Representative' THEN 13
        WHEN 'Grade 11 Representative' THEN 14
        WHEN 'Grade 12 Representative' THEN 15
    END
";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";

        if (!empty($row['Photo'])) {
            $imgData = base64_encode($row['Photo']);
            echo "<td><img src='data:image/jpeg;base64,{$imgData}' alt='Candidate'
                   style='width:50px; height:50px; border-radius:50%; object-fit:cover; box-shadow: 2px 2px 4px rgba(0,0,0,.2); border: 1px solid black'></td>";
        } else {
            echo "<td>No Image</td>";
        }


        echo "<td>{$row['CandidateName']}</td>";
        echo "<td>{$row['position']}</td>";
        echo "<td>{$row['PartyName']}</td>";
        echo "<td>{$row['Grade_7']}</td>";
        echo "<td>{$row['Grade_8']}</td>";
        echo "<td>{$row['Grade_9']}</td>";
        echo "<td>{$row['Grade_10']}</td>";
        echo "<td>{$row['Grade_11']}</td>";
        echo "<td>{$row['Grade_12']}</td>";
        echo "<td>{$row['TotalLevel']}</td>";

        echo "</tr>";
    }
}
    else {
    echo "Query Error: " . $conn->error;
}

$conn->close();
?>