<?php
require_once "../includes/database_connection.php";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $candidate_name = strtoupper(trim($_POST['candidate-name']));
    $positionID = (int) $_POST['position'];
    $party_ID = (int) $_POST['party-candidate'];
    $year_candidateID = (int) $_POST['year-candidate'];

  
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
        die("No photo uploaded or upload error.");
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
        die("Invalid image type. Only JPG, PNG, WEBP allowed.");
    }

    if ($_FILES['photo']['size'] > (20 * 1024 * 1024)) {
        die("File too large (max 20MB).");
    }

    $photoData = file_get_contents($_FILES['photo']['tmp_name']);


    $selectPosition = $conn->prepare("SELECT position FROM position WHERE positionID = ?");
    $selectPosition->bind_param("i", $positionID);
    $selectPosition->execute();
    $result = $selectPosition->get_result();

    if (!$row = $result->fetch_assoc()) {
        die("Invalid position selected.");
    }
    $position = $row['position'];
    $selectPosition->close();

    
    $selectYear = $conn->prepare("SELECT academicyear FROM academicyear_table WHERE academicyear_ID = ?");
    $selectYear->bind_param("i", $year_candidateID);
    $selectYear->execute();
    $result = $selectYear->get_result();

    if (!$row = $result->fetch_assoc()) {
        die("Invalid academic year selected.");
    }
    $year = $row['academicyear'];
    $selectYear->close();

    
    $selectParty = $conn->prepare("SELECT partyname FROM partyname_table WHERE partyname_ID = ?");
    $selectParty->bind_param("i", $party_ID);
    $selectParty->execute();
    $result = $selectParty->get_result();

    if (!$row = $result->fetch_assoc()) {
        die("Invalid party selected.");
    }
    $party = $row['partyname'];
    $selectParty->close();


    $electionID = 70;
    $election = "SSLG";


    $addCandidate = $conn->prepare("INSERT INTO registeredcandidate_table (CandidateName, positionID, Position, Election_ID, ElectionName, 
    academicYear_ID, AcademicYear, partyName_ID, PartyName, Photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
     $addCandidate->bind_param(
        "sisisisiss",
        $candidate_name,
        $positionID,
        $position,
        $electionID,
        $election,
        $year_candidateID,
        $year,
        $party_ID,
        $party,
        $photoData
    );

    if ($addCandidate->execute()) {
        $messageType = "success";
    } else {
        $messageType = "error";
    }

    $addCandidate->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../pictures/tcnhs_logo.png">
    <link rel="stylesheet" href="../css_folder/candidates_registration.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <title>Candidates Registration</title>
</head>

<body>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="candidates-regis-pop">
            <div class="candidate-photo-section">
                <img src="../pictures/user_candidate2.avif" name="photo" id="preview" class="candidate-photo">
                <input class="selectphoto" type="file" name="photo" id="photo" accept="image/*" required>
            </div>
            <div class="candidate-data">
                <label>Candidate Name</label>
                <input type="text" name="candidate-name" id="candidate" placeholder="Enter candidate name" required>
            </div>
            <div class="select-position">
                <label>Position</label>
                <select name="position" id="position" required>
                    <option value="" disabled selected>Select Position</option>
                    <?php

                    $loadPosition = $conn->prepare("SELECT positionID, position FROM position");
                    $loadPosition->execute();
                    $result = $loadPosition->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['positionID']}'>{$row['position']}</option>";
                    }
                    $loadPosition->close();
                    ?>
                </select>
                <i class="ph ph-caret-down"></i>
            </div>
            <div class="select-party">
                <div class="select1">
                    <label>Party</label>
                    <select name="party-candidate" id="party-candidate" required>
                        <option value="" disabled selected>Select Party</option>
                        <?php

                        $loadParty = $conn->prepare("SELECT partyname_ID, partyname FROM partyname_table");
                        $loadParty->execute();
                        $result = $loadParty->get_result();

                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['partyname_ID']}'>{$row['partyname']}</option>";
                        }
                        $loadParty->close();
                        ?>
                    </select>
                    <i class="ph ph-caret-down"></i>
                </div>
                <div class="select2">
                    <label>Academic Year</label>
                    <select name="year-candidate" id="year-candidate" required>
                        <option value="" disabled selected>Select Year</option>
                        <?php

                        $loadYear = $conn->prepare("SELECT academicyear_ID, academicyear FROM  academicyear_table");
                        $loadYear->execute();
                        $result = $loadYear->get_result();

                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['academicyear_ID']}'>{$row['academicyear']}</option>";
                        }

                        $loadYear->close();
                        ?>
                    </select>
                    <i class="ph ph-caret-down"></i>
                </div>
            </div>

            <div class="warning">
                <p><i class="ph ph-warning-circle"></i>Double-check candidate registration! Changes cannot be made once
                    the election is in progress.</p>
            </div>
            <div class="button-registration-candidate">
                <button type="submit" class="submitCandidate" onclick="submitCandidate()">Register</button>
                <button type="button" class="back" onclick="backRegisteredCandidate()">Back</button>
            </div>
        </div>
    </form>

</body>
<script src="../javascript/candidate_registration.js"></script>
<script>window.__PAGE_CONFIG__={messageType:"<?php echo isset($messageType) ? $messageType : ''; ?>"}</script>
<script src="../javascript/candidate_msg_inline.js"></script>

</html>

<?php
$conn->close();
?>