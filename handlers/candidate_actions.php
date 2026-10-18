<?php

require_once "../includes/database_connection.php";

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === "delete") {
        $id = intval($_POST['id']);

        $deleteCandidate = $conn->prepare("DELETE FROM  registeredcandidate_table WHERE Candidate_ID = ?");
        $deleteCandidate->bind_param("i", $id);

        if ($deleteCandidate->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    } elseif ($action === "update") {
        $id = intval($_POST['id']);
        $candidatename = strtoupper(trim($_POST['candidatename']));
        $position = $_POST['position'];
        $year = $_POST['year'];
        $party = $_POST['party'];

        if(empty($candidatename) || empty($position) || empty($year) || empty($party)){
            echo "empty";
            exit;
        }

        $photoData = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        }

   
        $selectPosition = $conn->prepare("SELECT positionID FROM position WHERE position = ?");
        $selectPosition->bind_param("s", $position);
        $selectPosition->execute();
        $result = $selectPosition->get_result();
        if (!$row = $result->fetch_assoc()) {
            echo "invalid_position";
            exit;
        }
        $positionID = $row['positionID'];
        $selectPosition->close();

        $selectYear = $conn->prepare("SELECT academicyear_ID FROM academicyear_table WHERE academicyear = ?");
        $selectYear->bind_param("s", $year);
        $selectYear->execute();
        $result = $selectYear->get_result();
        if (!$row = $result->fetch_assoc()) {
            echo "invalid_year";
            exit;
        }
        $yearID = $row['academicyear_ID'];
        $selectYear->close();

        $selectParty = $conn->prepare("SELECT partyname_ID FROM partyname_table WHERE partyname = ?");
        $selectParty->bind_param("s", $party);
        $selectParty->execute();
        $result = $selectParty->get_result();
        if (!$row = $result->fetch_assoc()) {
            echo "invalid_party";
            exit;
        }
        $partyID = $row['partyname_ID'];
        $selectParty->close();


        if ($photoData !== null) {
            $updateCandidate = $conn->prepare("
            UPDATE registeredcandidate_table SET CandidateName = ?, positionID = ?, Position = ?, academicYear_ID = ?, AcademicYear = ?, partyName_ID= ?, PartyName = ?, Photo = ?
            WHERE Candidate_ID = ?
        ");
            $updateCandidate->bind_param("sisisissi", $candidatename, $positionID, $position, $yearID, $year, $partyID, $party, $photoData, $id);
        } else {
          
            $updateCandidate = $conn->prepare("
            UPDATE registeredcandidate_table SET CandidateName = ?, positionID = ?, Position = ?, academicYear_ID = ?, AcademicYear = ?, partyName_ID= ?, PartyName = ?
            WHERE Candidate_ID = ?
        ");
            $updateCandidate->bind_param("sisisisi", $candidatename, $positionID, $position, $yearID, $year, $partyID, $party, $id);
        }

        if ($updateCandidate->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    }

}

$conn->close();

?>