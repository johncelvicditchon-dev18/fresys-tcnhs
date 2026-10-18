<?php
session_start();
require_once "../includes/database_connection.php";
if (!isset($_SESSION['student_id'])) {
    header('Location: ../auth/student_verification.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$full_name = $_SESSION['full_name'];
$gender = $_SESSION['gender'];
$grade_level = $_SESSION['grade_level'];
$section = $_SESSION['section'];

$getName = $conn->prepare("SELECT first_name, last_name FROM student_info WHERE id = ?");
$getName->bind_param("i", $student_id);
$getName->execute();
$nameResult = $getName->get_result();
$nameRow = $nameResult->fetch_assoc();
$display_name = $nameRow['first_name'] . ' ' . $nameRow['last_name'];
$getName->close();

// PRESIDENT
$presidentQuery = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                   FROM registeredcandidate_table 
                   WHERE Position = 'President' 
                   ORDER BY Candidate_ID ASC";
$presidentResult = $conn->query($presidentQuery);
$presidents = [];
if ($presidentResult->num_rows > 0) {
    while ($row = $presidentResult->fetch_assoc()) {
        $presidents[] = $row;
    }
}

// VICE-PRESIDENT
$viceQuery = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
              FROM registeredcandidate_table 
              WHERE Position = 'Vice-President' 
              ORDER BY Candidate_ID ASC";
$viceResult = $conn->query($viceQuery);
$vice_presidents = [];
if ($viceResult->num_rows > 0) {
    while ($row = $viceResult->fetch_assoc()) {
        $vice_presidents[] = $row;
    }
}

// SECRETARY
$secretaryQuery = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                   FROM registeredcandidate_table 
                   WHERE Position = 'Secretary' 
                   ORDER BY Candidate_ID ASC";
$secretaryResult = $conn->query($secretaryQuery);
$secretaries = [];
if ($secretaryResult->num_rows > 0) {
    while ($row = $secretaryResult->fetch_assoc()) {
        $secretaries[] = $row;
    }
}

// TREASURER
$treasurerQuery = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                   FROM registeredcandidate_table 
                   WHERE Position = 'Treasurer' 
                   ORDER BY Candidate_ID ASC";
$treasurerResult = $conn->query($treasurerQuery);
$treasurers = [];
if ($treasurerResult->num_rows > 0) {
    while ($row = $treasurerResult->fetch_assoc()) {
        $treasurers[] = $row;
    }
}

// AUDITOR
$auditorQuery = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                 FROM registeredcandidate_table 
                 WHERE Position = 'Auditor' 
                 ORDER BY Candidate_ID ASC";
$auditorResult = $conn->query($auditorQuery);
$auditors = [];
if ($auditorResult->num_rows > 0) {
    while ($row = $auditorResult->fetch_assoc()) {
        $auditors[] = $row;
    }
}

// PIO SHS
$pioQueryshs = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
             FROM registeredcandidate_table 
             WHERE Position = 'P.I.O(SHS)' 
             ORDER BY Candidate_ID ASC";
$pios_shs = [];
$pioResult_pioshs = $conn->query($pioQueryshs);
if ($pioResult_pioshs->num_rows > 0) {
    while ($row = $pioResult_pioshs->fetch_assoc()) {
        $pios_shs[] = $row;
    }
}

// PIO JHS
$pioQueryjhs = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
             FROM registeredcandidate_table 
             WHERE Position = 'P.I.O(JHS)' 
             ORDER BY Candidate_ID ASC";
$pios_jhs = [];
$pioResult_piojhs = $conn->query($pioQueryjhs);
if ($pioResult_piojhs->num_rows > 0) {
    while ($row = $pioResult_piojhs->fetch_assoc()) {
        $pios_jhs[] = $row;
    }
}

// PROTOCOL OFFICER SHS
$protocolQueryshs = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                  FROM registeredcandidate_table 
                  WHERE Position = 'Protocol Officer(SHS)' 
                  ORDER BY Candidate_ID ASC";
$protocols_shs = [];
$protocolResult_proshs = $conn->query($protocolQueryshs);
if ($protocolResult_proshs->num_rows > 0) {
    while ($row = $protocolResult_proshs->fetch_assoc()) {
        $protocols_shs[] = $row;
    }
}
// PROTOCOL OFFICER JHS
$protocolQueryjhs = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                  FROM registeredcandidate_table 
                  WHERE Position = 'Protocol Officer(JHS)' 
                  ORDER BY Candidate_ID ASC";
$protocols_jhs = [];
$protocolResult_projhs = $conn->query($protocolQueryjhs);
if ($protocolResult_projhs->num_rows > 0) {
    while ($row = $protocolResult_projhs->fetch_assoc()) {
        $protocols_jhs[] = $row;
    }
}

// GRADE 8 REPRESENTATIVE
$grade8Query = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                FROM registeredcandidate_table 
                WHERE Position = 'Grade 8 Representative' 
                ORDER BY Candidate_ID ASC";
$grade8Result = $conn->query($grade8Query);
$grade8_representatives = [];
if ($grade8Result->num_rows > 0) {
    while ($row = $grade8Result->fetch_assoc()) {
        $grade8_representatives[] = $row;
    }
}

// GRADE 9 REPRESENTATIVE
$grade9Query = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                FROM registeredcandidate_table 
                WHERE Position = 'Grade 9 Representative' 
                ORDER BY Candidate_ID ASC";
$grade9Result = $conn->query($grade9Query);
$grade9_representatives = [];
if ($grade9Result->num_rows > 0) {
    while ($row = $grade9Result->fetch_assoc()) {
        $grade9_representatives[] = $row;
    }
}


// GRADE 10 REPRESENTATIVE
$grade10Query = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                FROM registeredcandidate_table 
                WHERE Position = 'Grade 10 Representative' 
                ORDER BY Candidate_ID ASC";
$grade10Result = $conn->query($grade10Query);
$grade10_representatives = [];
if ($grade10Result->num_rows > 0) {
    while ($row = $grade10Result->fetch_assoc()) {
        $grade10_representatives[] = $row;
    }
}


// GRADE 11 REPRESENTATIVE
$grade11Query = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                FROM registeredcandidate_table 
                WHERE Position = 'Grade 11 Representative' 
                ORDER BY Candidate_ID ASC";
$grade11Result = $conn->query($grade11Query);
$grade11_representatives = [];
if ($grade11Result->num_rows > 0) {
    while ($row = $grade11Result->fetch_assoc()) {
        $grade11_representatives[] = $row;
    }
}


// GRADE 12 REPRESENTATIVE
$grade12Query = "SELECT Candidate_ID, CandidateName, PartyName, Photo 
                FROM registeredcandidate_table 
                WHERE Position = 'Grade 12 Representative' 
                ORDER BY Candidate_ID ASC";
$grade12Result = $conn->query($grade12Query);
$grade12_representatives = [];
if ($grade12Result->num_rows > 0) {
    while ($row = $grade12Result->fetch_assoc()) {
        $grade12_representatives[] = $row;
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>official-ballot-tcnhs</title>
      <link rel="icon" href="../pictures/tcnhs_logo.png">
    <link rel="stylesheet" href="../css_folder/ballot.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600&display=swap">
</head>

<body>



    <div class="layout">

        <!-- HEADER -->
        <header class="header">
            <div class="header-left">
                <img src="../pictures/tcnhs_logo.png" alt="School Logo">
                <img src="../pictures/sslg_logo.jpg" alt="SSLG Logo">
            </div>
            <div class="header-header">
                <h2>TANGUB CITY NATIONAL HIGH SCHOOL</h2>
                <h2>SUPREME STUDENT LEARNER GOVERNMENT ELECTION</h2>
                <h2>2026-2027</h2>
            </div>
            <div class="header-right">
                <div class="header-profile" id="header-profile">
                    <div class="header-avatar">
                        <i class="ph ph-user"></i>
                    </div>
                    <div class="header-student-info">
                        <span class="header-student-name"><?php echo htmlspecialchars($display_name); ?></span>
                        <span class="header-student-section"><?php echo htmlspecialchars($gender . ' | ' . $grade_level . ' - ' . $section); ?></span>
                    </div>
                    <div class="header-dropdown" id="header-dropdown">
                        <div class="header-dropdown-header">
                            <div class="header-dropdown-avatar">
                                <i class="ph ph-user"></i>
                            </div>
                            <div class="header-dropdown-info">
                                <span class="header-dropdown-name"><?php echo htmlspecialchars($display_name); ?></span>
                                <span class="header-dropdown-role"><?php echo htmlspecialchars($gender . ' | ' . $grade_level . ' - ' . $section); ?></span>
                            </div>
                        </div>
                        <div class="header-dropdown-divider"></div>
                        <button class="header-dropdown-item header-dropdown-logout" onclick="ballotLogout()">
                            <i class="ph ph-sign-out"></i>
                            <span>Log Out</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- PRESIDENT -->
        <section class="president-box">
            <div class="headbox">
                <h2>PRESIDENT</h2>
                <h2 id="president-count">0/1</h2>
            </div>

            <div class="presidents-box">
                <?php foreach ($presidents as $president): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($president['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="president" data-position="President"
                            data-name="<?= htmlspecialchars($president['CandidateName']); ?>"
                            value="<?= $president['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span class="president-name"><?= htmlspecialchars($president['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($president['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--VICE - PRESIDENT -->
        <section class="vice-president-box">
            <div class="headbox">
                <h2>VICE-PRESIDENT</h2>
                <h2 id="vice-president-count">0/1</h2>
            </div>

            <div class="vice-presidents-box">
                <?php foreach ($vice_presidents as $vice_president): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($vice_president['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="vice-president" data-position="Vice-President"
                            data-name="<?= htmlspecialchars($vice_president['CandidateName']); ?>"
                            value="<?= $vice_president['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span class="vice-president-name"><?= htmlspecialchars($vice_president['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($vice_president['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--SECRETARY -->
        <section class="secretary-box">
            <div class="headbox">
                <h2>SECRETARY</h2>
                <h2 id="secretary-count">0/1</h2>
            </div>

            <div class="secretarys-box">
                <?php foreach ($secretaries as $secretary): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($secretary['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="secretary" data-position="Secretary"
                            data-name="<?= htmlspecialchars($secretary['CandidateName']); ?>"
                            value="<?= $secretary['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span class="secretary"><?= htmlspecialchars($secretary['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($secretary['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--TREASURER -->
        <section class="treasurer-box">
            <div class="headbox">
                <h2>TREASURER</h2>
                <h2 id="treasurer-count">0/1</h2>
            </div>

            <div class="treasurers-box">
                <?php foreach ($treasurers as $treasurer): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($treasurer['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="treasurer" data-position="Treasurer"
                            data-name="<?= htmlspecialchars($treasurer['CandidateName']); ?>"
                            value="<?= $treasurer['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span class="treasurer"><?= htmlspecialchars($treasurer['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($treasurer['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--AUDITOR -->
        <section class="auditor-box">
            <div class="headbox">
                <h2>AUDITOR</h2>
                <h2 id="auditor-count">0/1</h2>
            </div>

            <div class="auditors-box">
                <?php foreach ($auditors as $auditor): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($auditor['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="auditor" data-position="Auditor"
                            data-name="<?= htmlspecialchars($auditor['CandidateName']); ?>"
                            value="<?= $auditor['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span class="auditor"><?= htmlspecialchars($auditor['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($auditor['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--P.I.O SHS -->
        <section class="pioshs-box">
            <div class="headbox">
                <h2>P.I.O - SHS</h2>
                <h2 id="pioshs-count">0/1</h2>
            </div>

            <div class="pios-shs-box">
                <?php foreach ($pios_shs as $pios_shs_new): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($pios_shs_new['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="pio-shs" data-position="P.I.O(SHS)"
                            data-name="<?= htmlspecialchars($pios_shs_new['CandidateName']); ?>"
                            value="<?= $pios_shs_new['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span class="pio-shs"><?= htmlspecialchars($pios_shs_new['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($pios_shs_new['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--P.I.O JHS -->
        <section class="piojhs-box">
            <div class="headbox">
                <h2>P.I.O - JHS</h2>
                <h2 id="piojhs-count">0/1</h2>
            </div>

            <div class="pios-jhs-box">
                <?php foreach ($pios_jhs as $pios_jhs_new): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($pios_jhs_new['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="pio-jhs" data-position="P.I.O(JHS)"
                            data-name="<?= htmlspecialchars($pios_jhs_new['CandidateName']); ?>"
                            value="<?= $pios_jhs_new['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span class="pio-jhs"><?= htmlspecialchars($pios_jhs_new['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($pios_jhs_new['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--PROTOCOL OFFICER SHS -->
        <section class="protocol-shs-box">
            <div class="headbox">
                <h2>PROTOCOL OFFICER - SHS</h2>
                <h2 id="protocol-shs-count">0/1</h2>
            </div>

            <div class="protocols-shs-box">
                <?php foreach ($protocols_shs as $protocols_shs_new): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($protocols_shs_new['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="protocol-shs" data-position="Protocol Officer(SHS)"
                            data-name="<?= htmlspecialchars($protocols_shs_new['CandidateName']); ?>"
                            value="<?= $protocols_shs_new['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span class="protocol-shs">
                            <?= htmlspecialchars($protocols_shs_new['CandidateName']); ?>
                        </span>
                        <span class="partyname">
                            <?= htmlspecialchars($protocols_shs_new['PartyName']); ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--PROTOCOL OFFICER JHS -->
        <section class="protocol-jhs-box">
            <div class="headbox">
                <h2>PROTOCOL OFFICER - JHS</h2>
                <h2 id="protocol-jhs-count">0/1</h2>
            </div>

            <div class="protocols-jhs-box">
                <?php foreach ($protocols_jhs as $protocols_jhs_new): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($protocols_jhs_new['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="protocol-jhs" data-position="Protocol Officer(JHS)"
                            data-name="<?= htmlspecialchars($protocols_jhs_new['CandidateName']); ?>"
                            value="<?= $protocols_jhs_new['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span class="protocol-jhs"><?= htmlspecialchars($protocols_jhs_new['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($protocols_jhs_new['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>


        <!--GRADE 8 REPRESENTATIVE -->
        <section class="grade-8-representative-box">
            <div class="headbox">
                <h2>GRADE 8 REPRESENTATIVE</h2>
                <h2 id="grade-8-representative-count">0/2</h2>
            </div>

            <div class="grade-8-representatives-box">
                <?php foreach ($grade8_representatives as $grade8_representative): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($grade8_representative['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="grade_8_representative[]" data-position="Grade 8 Representative"
                            data-name="<?= htmlspecialchars($grade8_representative['CandidateName']); ?>"
                            class="grade-8-input" value="<?= $grade8_representative['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span
                            class="grade-8-representative"><?= htmlspecialchars($grade8_representative['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($grade8_representative['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--GRADE 9 REPRESENTATIVE -->
        <section class="grade-9-representative-box">
            <div class="headbox">
                <h2>GRADE 9 REPRESENTATIVE</h2>
                <h2 id="grade-9-representative-count">0/2</h2>
            </div>

            <div class="grade-9-representatives-box">
                <?php foreach ($grade9_representatives as $grade9_representative): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($grade9_representative['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="grade_9_representative[]" data-position="Grade 9 Representative"
                            data-name="<?= htmlspecialchars($grade9_representative['CandidateName']); ?>"
                            class="grade-9-input" value="<?= $grade9_representative['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span
                            class="grade-9-representative"><?= htmlspecialchars($grade9_representative['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($grade9_representative['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--GRADE 10 REPRESENTATIVE -->
        <section class="grade-10-representative-box">
            <div class="headbox">
                <h2>GRADE 10 REPRESENTATIVE</h2>
                <h2 id="grade-10-representative-count">0/2</h2>
            </div>

            <div class="grade-10-representatives-box">
                <?php foreach ($grade10_representatives as $grade10_representative): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($grade10_representative['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="grade_10_representative[]" data-position="Grade 10 Representative"
                            data-name="<?= htmlspecialchars($grade10_representative['CandidateName']); ?>"
                            class="grade-10-input" value="<?= $grade10_representative['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span
                            class="grade-10-representative"><?= htmlspecialchars($grade10_representative['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($grade10_representative['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--GRADE 11 REPRESENTATIVE -->
        <section class="grade-11-representative-box">
            <div class="headbox">
                <h2>GRADE 11 REPRESENTATIVE</h2>
                <h2 id="grade-11-representative-count">0/2</h2>
            </div>

            <div class="grade-11-representatives-box">
                <?php foreach ($grade11_representatives as $grade11_representative): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($grade11_representative['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="grade_11_representative[]" data-position="Grade 11 Representative"
                            data-name="<?= htmlspecialchars($grade11_representative['CandidateName']); ?>"
                            class="grade-11-input" value="<?= $grade11_representative['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span
                            class="grade-11-representative"><?= htmlspecialchars($grade11_representative['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($grade11_representative['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <!--GRADE 12 REPRESENTATIVE -->
        <section class="grade-12-representative-box">
            <div class="headbox">
                <h2>GRADE 12 REPRESENTATIVE</h2>
                <h2 id="grade-12-representative-count">0/2</h2>
            </div>

            <div class="grade-12-representatives-box">
                <?php foreach ($grade12_representatives as $grade12_representative): ?>
                    <?php $imageSrc = "data:image/jpeg;base64," . base64_encode($grade12_representative['Photo']); ?>
                    <label class="candidate-card">
                        <input type="checkbox" name="grade_12_representative[]" data-position="Grade 12 Representative"
                            data-name="<?= htmlspecialchars($grade12_representative['CandidateName']); ?>"
                            class="grade-12-input" value="<?= $grade12_representative['Candidate_ID']; ?>">
                        <img src="<?= $imageSrc ?>" alt="Candidate Photo">
                        <span
                            class="grade-12-representative"><?= htmlspecialchars($grade12_representative['CandidateName']); ?></span>
                        <span class="partyname"><?= htmlspecialchars($grade12_representative['PartyName']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="buttonnss">
            <div class="button-groups">
                <button type="button" class="cancelvote" onclick="cancel()">Cancel</button>
                <button type="button" class="review" onclick="review()">Review your votes</button>
            </div>
        </section>



    </div>


    <!-- REVIEW MODAL -->

    <div id="reviewModal" class="review-modal">
        <div class="review-content">

            <div class="review-header">
                <div class="student-info">

                    <div class="flexinfo"> <strong>ID: </strong><input type="text" name="" id="ballot_id"
                            value="<?= htmlspecialchars($student_id); ?>" readonly></div>
                    <div class="flexinfo"><strong>STUDENT: </strong><input type="text" name="" id="ballot_name"
                            value="<?= htmlspecialchars($full_name); ?>" readonly></div>
                    <div class="flexinfo"><strong>GENDER: </strong><input type="text" name="" id=""
                            value="<?= htmlspecialchars($gender); ?>" readonly></div>
                    <div class="flexinfo"><strong>GRADE LEVEL: </strong><input type="text" name="" id="ballot_grade"
                            value="<?= htmlspecialchars($grade_level); ?>" readonly></div>
                    <div class="flexinfo"> <strong>SECTION: </strong><input type="text" name="" id=""
                            value="<?= htmlspecialchars($section); ?>" readonly></div>

                </div>

                <div class="review-logo">
                    <img src="../pictures/sslg_logo.jpg" alt="SSLG Logo">
                </div>
            </div>

            <h2>REVIEW YOUR VOTES</h2>

            <div id="reviewBody" class="review-body">


                <div class="first-group-vote">
                    <div class="review-category card">
                        <h3>PRESIDENT</h3>
                        <input type="text" name="review_president" id="review_president" readonly>
                    </div>

                    <div class="review-category card">
                        <h3>VICE-PRESIDENT</h3>
                        <input type="text" name="review_vice_president" id="review_vice_president" readonly>
                    </div>

                    <div class="review-category card">
                        <h3>SECRETARY</h3>
                        <input type="text" name="review_secretary" id="review_secretary" readonly>
                    </div>

                    <div class="review-category card">
                        <h3>TREASURER</h3>
                        <input type="text" name="review_treasurer" id="review_treasurer" readonly>
                    </div>
                </div>


                <div class="section-group-vote">
                    <div class="review-category card">
                        <h3>AUDITOR</h3>
                        <input type="text" name="review_auditor" id="review_auditor" readonly>
                    </div>

                    <div class="review-category card" id="cardpio-shs">
                        <h3>P.I.O - SHS</h3>
                        <input type="text" name="review_pio-shs" id="review_pio_shs" readonly>
                    </div>

                    <div class="review-category card" id="cardpio-jhs">
                        <h3>P.I.O - JHS</h3>
                        <input type="text" name="review_pio-jhs" id="review_pio_jhs" readonly>
                    </div>

                    <div class="review-category card" id="cardprotocol-shs">
                        <h3>PROTOCOL OFFICER - SHS</h3>
                        <input type="text" name="review_protocol-shs" id="review_protocol_shs" readonly>
                    </div>

                    <div class="review-category card" id="cardprotocol-jhs">
                        <h3>PROTOCOL OFFICER - JHS</h3>
                        <input type="text" name="review_protocol-jhs" id="review_protocol_jhs" readonly>
                    </div>

                    <div class="review-category card" id="card-grade8">
                        <h3>GRADE 8 REPRESENTATIVE</h3>
                        <input type="text" name="review_grade8" id="review_grade8_1" readonly>
                        <input type="text" name="review_grade8" id="review_grade8_2" readonly>
                    </div>

                    <div class="review-category card" id="card-grade9">
                        <h3>GRADE 9 REPRESENTATIVE</h3>
                        <input type="text" name="review_grade9" id="review_grade9_1" readonly>
                        <input type="text" name="review_grade9" id="review_grade9_2" readonly>
                    </div>

                    <div class="review-category card" id="card-grade10">
                        <h3>GRADE 10 REPRESENTATIVE</h3>
                        <input type="text" name="review_grade10" id="review_grade10_1" readonly>
                        <input type="text" name="review_grade10" id="review_grade10_2" readonly>
                    </div>

                    <div class="review-category card" id="card-grade11">
                        <h3>GRADE 11 REPRESENTATIVE</h3>
                        <input type="text" name="review_grade11" id="review_grade11_1" readonly>
                        <input type="text" name="review_grade11" id="review_grade11_2" readonly>
                    </div>

                    <div class="review-category card" id="card-grade12">
                        <h3>GRADE 12 REPRESENTATIVE</h3>
                        <input type="text" name="review_grade12" id="review_grade12_1" readonly>
                        <input type="text" name="review_grade12" id="review_grade12_2" readonly>
                    </div>
                </div>
            </div>


            <div class="review-actions">
                <button type="button" class="btn-cancel" onclick="closeReview()">GO BACK</button>
                <button type="button" class="btn-confirm" onclick="confirm_submit()">CONFIRM & SUBMIT</button>
            </div>
        </div>
    </div>




</body>
<script src="../javascript/ballot_not_submit.js"></script>
<script src="../javascript/ballot_submit.js"></script>
<script src="../javascript/ballot_inline.js"></script>

</html>
<?php
$conn->close();

?>