<?php
session_start();
require_once "../includes/database_connection.php";

$user_name = $_SESSION['user_name'] ?? 'Admin';
$user_role = $_SESSION['user_role'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Tangub City NHS</title>
    <link rel="icon" href="../pictures/tcnhs_logo.png">
    <link rel="stylesheet" href="../css_folder/overview.css">
    <link rel="stylesheet" href="../css_folder/manage_election.css">
    <link rel="stylesheet" href="../css_folder/delete_pop_up.css">
    <link rel="stylesheet" href="../css_folder/update_candidate_pop.css">
    <link rel="stylesheet" href="../css_folder/update_account_pop.css">
    <link rel="stylesheet" href="../css_folder/dashboard.css">
    <link rel="stylesheet" href="../css_folder/manage_elec_pop_update.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
</head>

<body>
    <!--POP UPs--->
    <div id="div-for-images">
        <span class="close-popup"><i class="ph ph-x" id="closecandidatexpop"></i></span>
        <img src="" class="pop-images-candidate" id="pop-images-candidate" alt="">
    </div>

    <div class="delete-pop-up" id="delete-pop-up"></div>

    <!--STUDENT VOTES POPUP-->
    <div id="student-votes-popup" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1002; align-items:center; justify-content:center;" onclick="if(event.target===this)closeStudentVotes();">
        <div style="background:#FFFFFF; border-radius:16px; width:90%; max-width:600px; max-height:80vh; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.25); display:flex; flex-direction:column;">
            <div style="background:linear-gradient(135deg,#4F46E5,#7C3AED); padding:20px 24px; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 id="svp-title" style="margin:0; font-family:Inter,sans-serif; font-size:16px; font-weight:700; color:#FFFFFF;">Student Votes</h3>
                    <p id="svp-subtitle" style="margin:4px 0 0; font-family:Inter,sans-serif; font-size:12px; color:rgba(255,255,255,0.75);"></p>
                </div>
                <button onclick="closeStudentVotes()" style="background:rgba(255,255,255,0.15); border:none; color:white; width:32px; height:32px; border-radius:8px; cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center;">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            <div id="svp-status-badge" style="padding:12px 24px 0;"></div>
            <div id="svp-body" style="padding:12px 24px 24px; overflow-y:auto; flex:1;"></div>
        </div>
    </div>

    <!--UPDATE STUDENTS INFO HTML---->
    <div class="update-student" id="update_student">
        <div class="update-box">
            <div class="header-update-student">
                <h2>UPDATE STUDENT INFORMATION</h2>
                <i class="ph ph-x" id="closecloseclose"></i>
            </div>
            <input type="hidden" id="id-student">
            <div class="input-group-flex">
                <div class="input-group-column">
                    <label for="firstname">First Name</label>
                    <input type="text" name="fname" id="fname" maxlength="100" required>
                </div>
                <div class="input-group-column">
                    <label for="middlename">Middle Initial</label>
                    <input type="text" name="mname" id="mname" maxlength="1">
                </div>
            </div>
            <div class="input-group2">
                <label for="lastname">Last Name</label>
                <input type="text" name="lname" id="lname" maxlength="100" required>
            </div>
            <div class="select-group2">
                <div class="select-group">
                    <label for="gender">Gender</label>
                    <select name="gender" id="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <i class="ph ph-caret-down dashboard-update-student"></i>
                </div>
                <div class="select-group">
                    <label for="grade">Grade</label>
                    <select name="grade" id="grade" required>
                        <option value="Grade 7">Grade 7</option>
                        <option value="Grade 8">Grade 8</option>
                        <option value="Grade 9">Grade 9</option>
                        <option value="Grade 10">Grade 10</option>
                        <option value="Grade 11">Grade 11</option>
                        <option value="Grade 12">Grade 12</option>
                    </select>
                    <i class="ph ph-caret-down dashboard-update-student"></i>
                </div>
            </div>
            <div class="select-group" id="selectselect">
                <label for="gender">Section</label>
                <select id="section" class="section" name="section" required>
                    <option value="" disabled selected>Selection Section</option>
                </select>
                <i class="ph ph-caret-down dashboard-update-student"></i>
            </div>

            <div class="button-groups">
                <button type="button" class="save" onclick="UpdateStudent()">Save changes</button>
                <button type="button" class="cancelupdate" onclick="cancelUpdatingStudent()">Cancel</button>
            </div>
        </div>
    </div>

    <!--UPDATE ACCOUNT NI-->
    <div class="update-account" id="update-account">
        <div class="headerlogout">
            <h2>UPDATE ACCOUNT</h2>
            <i class="ph ph-x" id="close-update-account"></i>

        </div>

        <input type="hidden" name="id-account" id="id-account">
        <div class="input-group">
            <label for="">Name</label>
            <input type="text" id="teacher-name" class="name" required name="name">
        </div>

        <div class="input-group">
            <label for="">Username</label>
            <input type="text" id="username" class="username" required name="username">
        </div>


        <div class="input-group">
            <label for="">Password (leave blank to keep current)</label>
            <input type="text" id="password" class="password" name="password" placeholder="Enter new password or leave blank">
        </div>
        <div class="select-group" id="select-group-caret">
            <label for="">Access</label>
            <select name="account-access" id="account-access">
                <option value="Admin">Admin</option>
                <option value="Adviser">Adviser</option>
            </select>
            <i class="ph ph-caret-down update-account-caret-pos"></i>
        </div>
        <div class="warning-update-account">
            <p><i class="ph ph-warning-circle update-account-circle"></i>To update an account’s status, click the
                corresponding button in the table.</p>
        </div>

        <div class="button-group-update">
            <button class="updateaccount" type="button" onclick="updateAccount()">Save Changes</button>
            <button class="updatecancelaccount" type="button" onclick="cancelUpdatingAccount()">Cancel</button>
        </div>

    </div>

    <!--UPDATE CANDIDATES POP UP-->
    <div class="update-candidate-box" id="update-candidate-box">
        <input type="text" hidden id="candidate-id">
        <div class="candidate-picture-display">
            <img src="" name="photodis" id="photodis" class="photodis">
            <input class="selectphoto" type="file" name="photo" id="photo" accept="image/*">
        </div>
        <div class="input-candidate-group">
            <label>Candidate Name</label>
            <input type="text" name="candidate-update" id="candidate-update" required>
        </div>

        <div class="select-candidate-group">
            <label>Position</label>
            <select name="position-update" id="position-update" required>
                <option value="" disabled selected>Select Position</option>
                <?php

                $loadPosition = $conn->prepare("SELECT positionID, position FROM position");
                $loadPosition->execute();
                $result = $loadPosition->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['position']}'>{$row['position']}</option>";
                }
                $loadPosition->close();
                ?>
            </select>
            <i class="ph ph-caret-down -caret-update-candidate"></i>
        </div>
        <div class="flexing">
            <div class="select-candidate-group">
                <label>Academic Year</label>
                <select name="year-update" id="year-update" required>
                    <option value="" disabled selected>Select Year</option>
                    <?php
                    $loadYear = $conn->prepare("SELECT academicyear_ID, academicyear FROM  academicyear_table");
                    $loadYear->execute();
                    $result = $loadYear->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['academicyear']}'>{$row['academicyear']}</option>";
                    }

                    $loadYear->close();
                    ?>
                </select>
                <i class="ph ph-caret-down -caret-update-candidate"></i>
            </div>

            <div class="select-candidate-group">
                <label>Party</label>
                <select name="party-update" id="party-update" required>
                    <option value="" disabled selected>Select Party</option>

                    <?php
                    $loadParty = $conn->prepare("SELECT partyname_ID, partyname FROM partyname_table");
                    $loadParty->execute();
                    $result = $loadParty->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['partyname']}'>{$row['partyname']}</option>";
                    }
                    $loadParty->close();
                    ?>
                </select>
                <i class="ph ph-caret-down -caret-update-candidate"></i>
            </div>
        </div>
        <div class="warning">
            <p><i class="ph ph-warning-circle update-circle"></i>Double-check candidate registration! Changes cannot be
                made once
                the election is in progress.</p>
        </div>
        <div class="button-update-candidates">
            <button class="savechangesbutton" type="button" onclick="updateCandidate()">Save changes</button>
            <button type="button" onclick="cancelUpdateCandidate()" class="cancel-candidate">Cancel</button>
        </div>
    </div>


    <div class="manage_pop">

        <!--UPDATE PARTY  ---->
        <div class="update-party" id="update-party">
            <div class="update-party-header">
                <h2>UPDATE PARTY</h2>
                <i class="ph ph-x" id="update-party-close-x"></i>
            </div>
            <div class="update-party-input">
                <input type="text" id="id-for-party-update">
                <label>Party</label>
                <input type="text" name="update-party" id="input-for-party-update" required>
            </div>
            <div class="update-party-button">
                <button type="button" class="updatepartysave" onclick="updateParty()">Save changes</button>
                <button type="button" class="cancelupdateparty" onclick="cancelUpdateParty()">Cancel</button>
            </div>
        </div>

        <!--UPDATE ACADEMIC YEAR  ---->
        <div class="update-year" id="update-year">
            <div class="update-year-header">
                <h2>UPDATE YEAR</h2>
                <i class="ph ph-x" id="update-year-close-x"></i>
            </div>
            <div class="update-year-input">
                <input type="text" id="id-for-year-update">
                <label>Academic Year</label>
                <input type="text" name="update-year" id="input-for-update-year" required>
            </div>
            <div class="update-year-button">
                <button type="button" class="updateyearsave" onclick="updateYear()">Save changes</button>
                <button type="button" class="cancelupdateyear" onclick="cancelUpdateYear()">Cancel</button>
            </div>
        </div>

        <!--UPDATE SECTION   ---->
        <div class="update-section" id="update-section">
            <div class="update-section-header">
                <h2>UPDATE SECTION</h2>
                <i class="ph ph-x" id="update-section-close-x"></i>
            </div>
            <div class="update-section-input">
                <input type="text" id="id-for-section-update">
                <div class="selectdiv">
                    <label>Grade Level</label>
                    <select name="gradefor--section" id="grade__section">
                        <option value="GRADE 7">GRADE 7</option>
                        <option value="GRADE 8">GRADE 8</option>
                        <option value="GRADE 9">GRADE 9</option>
                        <option value="GRADE 10">GRADE 10</option>
                        <option value="GRADE 11">GRADE 11</option>
                        <option value="GRADE 12">GRADE 12</option>
                    </select>
                    <i class="ph ph-caret-down caretforupdatesectiontograde"></i>
                </div>
                <label>Section</label>
                <input type="text" name="update-section" id="input-for-update-section" required>
            </div>
            <div class="update-section-button">
                <button type="button" class="updatesectionsave" onclick="updateSectionandGrade()">Save changes</button>
                <button type="button" class="cancelupdatesection" onclick="cancelUpdateSection()">Cancel</button>
            </div>
        </div>

        <!--UPDATE SCHOOL --->
        <div class="update-school" id="update-school">
            <div class="update-school-header">
                <h2>UPDATE SCHOOL</h2>
                <i class="ph ph-x" id="update-school-close-x"></i>
            </div>
            <div class="update-school-input">
                <input type="text" id="id-for-school-update">
                <label>School</label>
                <input type="text" name="update-school" id="input-for-update-school" required>
            </div>
            <div class="update-school-button">
                <button type="button" class="updateschoolsave" onclick="updateSchool()">Save changes</button>
                <button type="button" class="cancelupdateschool" onclick="cancelUpdateSchool()">Cancel</button>
            </div>
        </div>

    </div>

    <!-- QR Modal -->
    <div id="qr-pop" class="qr-modal">
        <div class="qr-modal-content">
            <span class="qr-close"><i class="ph ph-x" id="closeqrcode"></i></span>
            <div id="qr-picture"></div>
        </div>
    </div>


    <iframe id="print-frame" style="display:none;"></iframe>
    <iframe id="archive-print-frame" style="display:none;"></iframe>
    <div class="layout">
        <div class="header">
            <div class="header-left">
                <span class="hamburger"><i class="ph ph-list"></i></span>
                <h2>Dashboard</h2>
            </div>
            <div class="header-right">
                <div class="notification-bell" id="notification-bell" onclick="toggleNotificationPanel()" style="position:relative; cursor:pointer; width:38px; height:38px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:#F1F5F9; transition:background 0.2s;">
                    <i class="ph ph-bell" style="font-size:20px; color:#475569;"></i>
                    <span id="notification-badge" style="display:none; position:absolute; top:4px; right:4px; background:#EF4444; color:white; font-family:Inter,sans-serif; font-size:10px; font-weight:700; min-width:18px; height:18px; border-radius:9px; padding:0 5px; align-items:center; justify-content:center; border:2px solid white;">0</span>
                </div>
                <div class="notification-panel" id="notification-panel" style="display:none; position:absolute; top:52px; right:80px; width:380px; max-height:420px; background:white; border-radius:14px; box-shadow:0 12px 40px rgba(0,0,0,0.15); z-index:2000; overflow:hidden;">
                    <div style="padding:16px 20px; background:linear-gradient(135deg,#EF4444,#DC2626); display:flex; align-items:center; justify-content:space-between;">
                        <span style="font-family:Inter,sans-serif; font-size:14px; font-weight:700; color:white;">Notifications</span>
                        <button onclick="clearNotifications()" style="background:rgba(255,255,255,0.15); border:none; color:white; font-family:Inter,sans-serif; font-size:11px; font-weight:600; padding:4px 10px; border-radius:6px; cursor:pointer;">Clear All</button>
                    </div>
                    <div id="notification-list" style="max-height:340px; overflow-y:auto;"></div>
                </div>
                <div class="header-profile" id="header-profile">
                    <div class="header-avatar">
                        <i class="ph ph-user"></i>
                    </div>
                    <span class="header-username"><?php echo htmlspecialchars($user_name); ?></span>
                    <div class="header-dropdown" id="header-dropdown">
                        <div class="header-dropdown-header">
                            <div class="header-dropdown-avatar">
                                <i class="ph ph-user"></i>
                            </div>
                            <div class="header-dropdown-info">
                                <span class="header-dropdown-name"><?php echo htmlspecialchars($user_name); ?></span>
                                <span class="header-dropdown-role"><?php echo htmlspecialchars($user_role); ?></span>
                            </div>
                        </div>
                        <div class="header-dropdown-divider"></div>
                        <button class="header-dropdown-item header-dropdown-logout" onclick="popUpLogout()">
                            <i class="ph ph-sign-out"></i>
                            <span>Log Out</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar" id="sidebar">
            <div class="header-sidebar">
                <div class="divlogo">
                    <img src="../pictures/tcnhs_logo.png" alt="tcnhs logo">
                    <div class="flexingword">
                        <h1>Dashboard</h1>
                        <label class="fast">Fast-Result Electoral System</label>
                    </div>
                </div>
            </div>


            <div>
                <nav class="navigation">
                    <ul>
                        <li id="overview" class="overview"><i class="fa fa-chart-line"></i> <span>Overview</span></li>
                        <li id="student" class="student">
                            <i class="fa-solid fa-user-graduate"></i><span>Students</span>
                            <i class="fa fa-caret-down dropdown-icon"></i>
                            <ul class="dropdown">
                                <li id="registered"><i class="ph ph-table"></i>Registered Students</li>
                                <li id="bulkimport"><i class="ph ph-file-csv"></i>Import Students</li>
                                <li id="printstudent"><i class="ph ph-printer"></i>Print Students</li>
                                <li id="votestudentcasted"><i class="ph ph-chart-bar"></i>Vote Casted</li>
                            </ul>
                        </li>

                        <li id="account"><i class="fa-solid fa-users"></i><span>Accounts</span></li>
                        <li id="candidate-sidebar"><i class="fa-solid fa-user-tie"></i><span>Candidates</span></li>
                        <li id="gotoresult"><i class="fa-solid fa-square-poll-horizontal"></i><span>Results</span></li>
                        <li id="manage_election"><i class="fa-solid fa-gear"></i><span>Manage Election</span></li>
                        <li id="election-history"><i class="fa-solid fa-clock-rotate-left"></i><span>Election History</span></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="main">



            <div class="student-card" id="student-card">
                <h2 class="header-h2">REGISTERED STUDENTS</h2>
                <p class="viewview">Manageable list of registered students</p>
                <div class="searchbar">
                    <input type="text" id="search-student" name="search" placeholder="Search student....">
                    <i class="ph ph-magnifying-glass"></i>
                    <div class="buttons-search">
                        <button onclick="proceedToRegistration()" class="register"><i
                                class="ph ph-user-plus"></i>Registration</button>
                    </div>
                </div>
                <div class="table-container">
                    <table id="table-student">
                        <colgroup>
                            <col style="width: 10px;">
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col style="width: 5px;">
                            <col class="columneight">
                            <col>
                            <col>
                            <col style="width: 10px;">
                        </colgroup>
                        <tr>
                            <th>EDIT</th>
                            <th>ID</th>
                            <th>VERIFICATION CODE</th>
                            <th>QR CODE</th>
                            <th>FIRST NAME</th>
                            <th>MIDDLE INITIAL</th>
                            <th>LAST NAME</th>
                            <th>GENDER</th>
                            <th>GRADE LEVEL</th>
                            <th>SECTION</th>
                            <th>VOTE STATUS</th>
                            <th>DELETE</th>
                        </tr>

                        <tbody id="table-body-student">
                            <?php


                            $retrieveStudentData = $conn->prepare("SELECT id, verification_code, first_name, middle_name, last_name, gender, grade_level, section, vote_status
                    FROM student_info ORDER BY CASE grade_level WHEN 'Grade 7' THEN 1 WHEN 'Grade 8' THEN 2 WHEN 'Grade 9' THEN 3 WHEN 'Grade 10' THEN 4 
                    WHEN 'Grade 11' THEN 5 WHEN 'Grade 12' THEN 6 ELSE 7 END, CASE gender WHEN 'Male' THEN 1 WHEN 'Female' THEN 2 ELSE 3 END, section ASC, first_name ASC");
                            $retrieveStudentData->execute();
                            $result = $retrieveStudentData->get_result();

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {

                                    $qr_data = [
                                        "verification_code" => $row['verification_code'],
                                        "grade_level" => $row['grade_level'],
                                        "student_name" => $row['last_name'] . ", " . $row['first_name'] . " " . $row['middle_name'] . ".",
                                        "section" => $row['section']
                                    ];

                                    $qr_json = htmlspecialchars(json_encode($qr_data), ENT_QUOTES, 'UTF-8');

                                    echo "<tr data-id='{$row['id']}'>";
                                    echo "<td><i class='fa-regular fa-pen-to-square editicon'></i></td>";
                                    echo "<td>{$row['id']}</td>";
                                    echo "<td>{$row['verification_code']}</td>";
                                    echo "<td><img class='qr-code' data-qr='{$qr_json}' src='../pictures/qrcode.jpg'  alt='QR CODE' width='30' height='30' style='cursor:pointer;' /></td>";
                                    echo "<td>{$row['first_name']}</td>";
                                    echo "<td>{$row['middle_name']}</td>";
                                    echo "<td>{$row['last_name']}</td>";
                                    echo "<td>{$row['gender']}</td>";
                                    echo "<td>{$row['grade_level']}</td>";
                                    echo "<td>{$row['section']}</td>";
                                    if ($row['vote_status'] === "Available") {
                                        echo "<td><button class='available'><i class='ph ph-check-circle'></i>Available</button></td>";
                                    } else if ($row['vote_status'] === "Casted") {
                                        echo "<td><button class='casted'><i class='ph ph-calendar-slash'></i>Casted</button></td>";
                                    } else {
                                        echo "<td><button class='loading'>Loading</button></td>";
                                    }
                                    echo "<td><i class='fa-solid fa-trash-can btn-delete-table' data-id='{$row['id']}'></i></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='12'>No data found!</td></tr>";
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="student-card-print" id="student-card-print">
                <h2 class="header-h2">LIST OF STUDENTS</h2>
                <p class="viewview">Manageable list of registered students</p>
                <div class="actions">
                    <div class="select-group-print">
                        <select class="grade" name="grade" id="grade-print" required>
                            <option value="" disabled selected>Select Grade Level</option>
                            <option value="Grade 7">Grade 7</option>
                            <option value="Grade 8">Grade 8</option>
                            <option value="Grade 9">Grade 9</option>
                            <option value="Grade 10">Grade 10</option>
                            <option value="Grade 11">Grade 11</option>
                            <option value="Grade 12">Grade 12</option>
                        </select>
                        <i class="ph ph-caret-down caret-print"></i>
                    </div>

                    <div class="select-group-print">
                        <select name="section" id="section-print">
                            <option value="" disabled selected>Select Section</option>
                        </select>
                        <i class="ph ph-caret-down caret-print"></i>
                    </div>
                    <button class="print-student" onclick="printStudentBySection()"><i
                            class="ph ph-printer print-studen"></i>Print</button>
                </div>

                <div class="table-container-print">
                    <table id="table-student-print">
                        <colgroup>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>

                        </colgroup>
                        <tr>
                            <th>VERIFICATION CODE</th>
                            <th>FIRST NAME</th>
                            <th>MIDDLE INITIAL</th>
                            <th>LAST NAME</th>
                            <th>GENDER</th>
                            <th>GRADE LEVEL</th>
                            <th>SECTION</th>

                        </tr>

                        <tbody id="table-body-student-print">
                            <?php


                            $retrieveStudentData = $conn->prepare("SELECT id, verification_code, first_name, middle_name, last_name, gender, grade_level, section, vote_status
                    FROM student_info ORDER BY CASE grade_level WHEN 'Grade 7' THEN 1 WHEN 'Grade 8' THEN 2 WHEN 'Grade 9' THEN 3 WHEN 'Grade 10' THEN 4 
                    WHEN 'Grade 11' THEN 5 WHEN 'Grade 12' THEN 6 ELSE 7 END, CASE gender WHEN 'Male' THEN 1 WHEN 'Female' THEN 2 ELSE 3 END, section ASC, first_name ASC");
                            $retrieveStudentData->execute();
                            $result = $retrieveStudentData->get_result();

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr data-id='{$row['id']}'>";
                                    echo "<td>{$row['verification_code']}</td>";
                                    echo "<td>{$row['first_name']}</td>";
                                    echo "<td>{$row['middle_name']}</td>";
                                    echo "<td>{$row['last_name']}</td>";
                                    echo "<td>{$row['gender']}</td>";
                                    echo "<td>{$row['grade_level']}</td>";
                                    echo "<td>{$row['section']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='11'>No data found!</td></tr>";
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="student-card-vote-status" id="student-card-vote-status">
                <h2 class="header-h2">VOTE CASTED</h2>
                <p class="viewview">Manageable list of registered students</p>
                <div class="vote-sta-student">
                    <div class="div-status">
                        <select class="availability" name="student-status" id="student-status" required>
                            <option value="" disabled selected>Select Vote Status</option>
                            <option value="Available">Available</option>
                            <option value="Casted">Casted</option>
                        </select>
                        <i class="ph ph-caret-down caret-status-"></i>
                    </div>
                    <div class="buttons-print">
                        <button class="print-vote-status" onclick="printStudentVoteStatus()"><i
                                class="ph ph-printer"></i>Print</button>
                    </div>

                </div>
                <div class="table-container-vote-status">
                    <table id="table-student-vote-status">
                        <colgroup>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col style="width: 5px;">
                            <col class="columneight">
                            <col>
                            <col>
                            <col>
                        </colgroup>
                        <tr>

                            <th>VERIFICATION CODE</th>
                            <th>FIRST NAME</th>
                            <th>MIDDLE INITIAL</th>
                            <th>LAST NAME</th>
                            <th>GENDER</th>
                            <th>GRADE LEVEL</th>
                            <th>SECTION</th>
                            <th>VOTE STATUS</th>
                            <th>DATE CASTED</th>

                        </tr>

                        <tbody id="table-body-casted">
                            <?php


                            $retrieveStudentData = $conn->prepare("SELECT id, verification_code, first_name, middle_name, last_name, gender, grade_level, section, vote_status, date_casted
                    FROM student_info ORDER BY CASE grade_level WHEN 'Grade 7' THEN 1 WHEN 'Grade 8' THEN 2 WHEN 'Grade 9' THEN 3 WHEN 'Grade 10' THEN 4 
                    WHEN 'Grade 11' THEN 5 WHEN 'Grade 12' THEN 6 ELSE 7 END, CASE gender WHEN 'Male' THEN 1 WHEN 'Female' THEN 2 ELSE 3 END, section ASC, first_name ASC");
                            $retrieveStudentData->execute();
                            $result = $retrieveStudentData->get_result();

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr data-id='{$row['id']}'>";
                                    echo "<td>{$row['verification_code']}</td>";
                                    echo "<td>{$row['first_name']}</td>";
                                    echo "<td>{$row['middle_name']}</td>";
                                    echo "<td>{$row['last_name']}</td>";
                                    echo "<td>{$row['gender']}</td>";
                                    echo "<td>{$row['grade_level']}</td>";
                                    echo "<td>{$row['section']}</td>";
                                    if ($row['vote_status'] === "Available") {
                                        echo "<td><button class='available'><i class='ph ph-check-circle'></i>Available</button></td>";
                                    } else if ($row['vote_status'] === "Casted") {
                                        echo "<td><button class='casted'><i class='ph ph-calendar-slash'></i>Casted</button></td>";
                                    } else {
                                        echo "<td><button class='loading'>Loading</button></td>";
                                    }
                                    echo "<td>{$row['date_casted']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='11'>No data found!</td></tr>";
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
            </div>


            <div class="account-card" id="account-card">
                <h2 class="header-h2">ACCOUNTS</h2>
                <p class="viewview">Click the button in the table to approve user accounts.</p>
                <div class="searchbar">
                    <input type="text" id="search-account" name="search-account" placeholder="Search account....">
                    <i class="ph ph-magnifying-glass"></i>
                    <button onclick="proceedToHistory()" class="history"><i class="ph ph-clock-user"></i>
                        History</button>
                </div>
                <div class="table-container">
                    <table id="table-account">
                        <colgroup>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                        </colgroup>
                        <tr>
                            <th>EDIT</th>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>USERNAME</th>
                            <th>STATUS</th>
                            <th>ACCESS</th>
                            <th>CREATED</th>
                            <th>DELETE</th>
                        </tr>

                        <tbody id="table-body-account">
                            <?php

                            $retrieveAccount = $conn->prepare("SELECT id, name, username, status, admin_adviser, acc_created FROM admin_account
                                ORDER BY CASE admin_adviser WHEN 'Admin' THEN 1 WHEN 'Adviser' THEN 2 ELSE 3 END");
                            $retrieveAccount->execute();
                            $result = $retrieveAccount->get_result();

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
                                echo "<tr><td colspan='9'>No data found!</td></tr> ";
                            }

                            ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="overview-card" id="overview-card">
                <h2 class="header-h2-1">OVERVIEW</h2>
                <p class="viewview">View real-time voting progress.</p>

                <div style="background:#FFFFFF; border-radius:14px; box-shadow:0 1px 4px rgba(15,23,42,0.06); border:1px solid #EDF1F7; padding:18px 16px 14px; margin-bottom:16px;">
                    <h3 class="chart-card-title">Turnout by Grade Level</h3>
                    <div style="height:320px; width:100%; position:relative;">
                        <canvas id="chartGradeTurnout"></canvas>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card">
                        <h3 class="chart-card-title">Students</h3>
                        <div class="chart-card-body">
                            <canvas id="chartStudents"></canvas>
                        </div>
                        <div class="chart-legend-custom" id="legendStudents"></div>
                    </div>

                    <div class="chart-card">
                        <h3 class="chart-card-title">Overall Vote Status</h3>
                        <div class="chart-card-body">
                            <canvas id="chartVoteStatus"></canvas>
                        </div>
                        <div class="chart-legend-custom" id="legendVoteStatus"></div>
                    </div>

                    <div class="chart-card">
                        <h3 class="chart-card-title">Registered Students Categorize By gender</h3>
                        <div class="chart-card-body">
                            <canvas id="chartGender"></canvas>
                        </div>
                        <div class="chart-legend-custom" id="legendGender"></div>
                    </div>

                    <div class="chart-card">
                        <h3 class="chart-card-title">Votes by Gender</h3>
                        <div class="chart-card-body">
                            <canvas id="chartGenderVote"></canvas>
                        </div>
                        <div class="chart-legend-custom" id="legendGenderVote"></div>
                    </div>

                    <div class="chart-card">
                        <h3 class="chart-card-title">Ballot Submission Breakdown</h3>
                        <div class="chart-card-body">
                            <canvas id="chartEmptyBallot"></canvas>
                        </div>
                        <div class="chart-legend-custom" id="legendEmptyBallot"></div>
                    </div>
                </div>

            </div>

            <div class="manage-election" id="manage-e-one">
                <!-- add party--->

                <div class="add-section card">
                    <h2>ADD PARTY</h2>
                    <div class="input-party">
                        <input type="text" id="add-party" name="add-party" placeholder="Enter new party" required>
                        <button type="button" onclick="addParty()">Save</button>
                    </div>
                    <div class="table-container-party" id="table-party-here">
                        <table class="table-section" id="party_table">
                            <colgroup>
                                <col>
                                <col>
                                <col style="width: 330px;">
                                <col>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>EDIT</th>
                                    <th>ID</th>
                                    <th>PARTY</th>
                                    <th>DELETE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php


                                $retrieveSection = $conn->prepare("SELECT partyname_ID, partyname FROM partyname_table");
                                $retrieveSection->execute();
                                $result = $retrieveSection->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr data-id='{$row['partyname_ID']}'>";
                                        echo "<td><i class='fa-regular fa-pen-to-square editparty'></i></td>";
                                        echo "<td>{$row['partyname_ID']}</td>";
                                        echo "<td>{$row['partyname']}</td>";
                                        echo "<td><i class='fa-solid fa-trash-can btn-delete-party' data-id='{$row['partyname_ID']}'></i></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='no-data'>No data found!</td></tr>";
                                }

                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- add year--->
                <div class="add-section card">
                    <h2>ADD ELECTION YEAR</h2>
                    <div class="input-party">
                        <input type="text" id="add-year" name="add-year" placeholder="Enter new academic year" required>
                        <button type="button" onclick="addYear()">Save</button>
                    </div>
                    <div class="table-container-party" id="table-year-here">
                        <table class="table-section" id="year_table">
                            <colgroup>
                                <col>
                                <col>
                                <col style="width: 330px;">
                                <col>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>EDIT</th>
                                    <th>ID</th>
                                    <th>ELECTION YEAR</th>
                                    <th>DELETE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php


                                $retrieveSection = $conn->prepare("SELECT academicyear_ID, academicyear FROM academicyear_table");
                                $retrieveSection->execute();
                                $result = $retrieveSection->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr data-id='{$row['academicyear_ID']}'>";
                                        echo "<td><i class='fa-regular fa-pen-to-square editacadyear'></i></td>";
                                        echo "<td>{$row['academicyear_ID']}</td>";
                                        echo "<td>{$row['academicyear']}</td>";
                                        echo "<td><i class='fa-solid fa-trash-can btn-delete-year' data-id='{$row['academicyear_ID']}'></i></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='no-data'>No data found!</td></tr>";
                                }

                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="manage-election" id="manage-e-two">
                <!-- add section--->
                <div class="add-section card">
                    <h2>ADD SECTION</h2>
                    <div class="input-party">
                        <div class="select-grade-for-section">
                            <select name="gradelevelinsection" id="gradelevelinsection" class="gradelevelinsection"
                                required>
                                <option value="" selected disabled>Select Grade Level</option>
                                <option value="Grade 7">Grade 7</option>
                                <option value="Grade 8">Grade 8</option>
                                <option value="Grade 9">Grade 9</option>
                                <option value="Grade 10">Grade 10</option>
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                            </select>
                            <i class="ph ph-caret-down caret-down-section"></i>
                        </div>
                        <input type="text" id="add-section" name="add-section" placeholder="Enter new section" required>
                        <button type="button" onclick="addSection()">Save</button>
                    </div>
                    <div class="table-container-party" id="table-section-here">
                        <table class="table-section" id="section_table">
                            <thead>
                                <tr>
                                    <th>EDIT</th>
                                    <th>ID</th>
                                    <th>GRADE LEVEL</th>
                                    <th>SECTION</th>
                                    <th>DELETE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $retrieveSection = $conn->prepare("SELECT id, grade_level_to_sec,section FROM section_table
                                ORDER BY CASE grade_level_to_sec WHEN 'GRADE 7' THEN 1 WHEN 'GRADE 8' THEN 2 WHEN 'GRADE 9' THEN 3 WHEN 'GRADE 10' THEN 4 WHEN 'GRADE 11' THEN 5
                                WHEN 'GRADE 12' THEN 6 ELSE 7 END, section ASC");
                                $retrieveSection->execute();
                                $result = $retrieveSection->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr data-id='{$row['id']}'>";
                                        echo "<td><i class='fa-regular fa-pen-to-square editsection'></i></td>";
                                        echo "<td>{$row['id']}</td>";
                                        echo "<td>{$row['grade_level_to_sec']}</td>";
                                        echo "<td>{$row['section']}</td>";
                                        echo "<td><i class='fa-solid fa-trash-can btn-delete-section' data-id='{$row['id']}'></i></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='no-data'>No data found!</td></tr>";
                                }

                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- add school--->
                <div class="add-section card">
                    <h2>ADD SCHOOL NAME</h2>
                    <div class="input-party">
                        <input type="text" id="add-school" name="add-school" placeholder="Enter new school name" required>
                        <button type="button" onclick="addSchool()">Save</button>
                    </div>
                    <div class="table-container-party" id="table-school-here">
                        <table class="table-section" id="school_table">
                            <colgroup>
                                <col>
                                <col>
                                <col style="width: 310px;">
                                <col>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>EDIT</th>
                                    <th>ID</th>
                                    <th>SCHOOL NAME</th>
                                    <th>DELETE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php


                                $retrieveSection = $conn->prepare("SELECT schoolid, schoolname FROM school_table");
                                $retrieveSection->execute();
                                $result = $retrieveSection->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr data-id='{$row['schoolid']}'>";
                                        echo "<td><i class='fa-regular fa-pen-to-square editschoolname'></i></td>";
                                        echo "<td>{$row['schoolid']}</td>";
                                        echo "<td>{$row['schoolname']}</td>";
                                        echo "<td><i class='fa-solid fa-trash-can btn-delete-school' data-id='{$row['schoolid']}'></i></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='no-data'>No data found!</td></tr>";
                                }

                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- BULK IMPORT SECTION -->
            <div class="student-card" id="bulk-import-card" style="display:none;">
                <h2 class="header-h2">IMPORT STUDENTS</h2>
                <p class="viewview">Import students via CSV file</p>
                <div style="padding:24px; width:100%; box-sizing:border-box;">
                    <div style="background:#F8FAFC; border:2px dashed #CBD5E1; border-radius:14px; padding:40px; text-align:center; margin-bottom:20px; width:100%; box-sizing:border-box;">
                        <i class="ph ph-file-csv" style="font-size:48px; color:#6366F1; margin-bottom:12px; display:block;"></i>
                        <h3 style="font-family:Inter,sans-serif; font-size:16px; font-weight:700; color:#0F172A; margin:0 0 8px;">Upload CSV File</h3>
                        <p style="font-family:Inter,sans-serif; font-size:12px; color:#64748B; margin:0 0 4px;"><b>CSV COLUMNS:</b> FIRST NAME, MIDDLE INITIAL, LAST NAME, GENDER, GRADE LEVEL, SECTION &nbsp;|&nbsp; VERIFICATION CODE AND QR CODE WILL BE AUTOMATICALLY GENERATED.</p>
                        <p style="font-family:Inter,sans-serif; font-size:12px; color:#EF4444; font-weight:700; margin:0 0 16px;">NOTE: FIRST NAME, MIDDLE INITIAL, LAST NAME, AND SECTION SHOULD BE IN UPPERCASE TO AVOID IMPORTING ERRORS.</p>
                        <form id="bulkImportForm" enctype="multipart/form-data">
                            <input type="file" id="csvFile" name="csv_file" accept=".csv" style="display:none;" onchange="previewCSV(this)">
                            <button type="button" onclick="document.getElementById('csvFile').click();" style="font-family:Inter,sans-serif; font-size:13px; font-weight:600; color:white; background:#4F46E5; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; margin-bottom:12px;">
                                <i class="ph ph-upload-simple"></i> Choose CSV File
                            </button>
                            <p id="csvFileName" style="font-family:Inter,sans-serif; font-size:12px; color:#64748B; margin:0;"></p>
                        </form>
                        <button type="button" onclick="downloadTemplate()" style="font-family:Inter,sans-serif; font-size:12px; font-weight:500; color:#6366F1; background:none; border:none; cursor:pointer; text-decoration:underline; margin-top:8px;">
                            Download CSV Template
                        </button>
                    </div>

                    <div id="csvPreview" style="display:none; margin-bottom:20px;">
                        <h4 style="font-family:Inter,sans-serif; font-size:13px; font-weight:700; color:#0F172A; margin:0 0 8px;">Preview (<span id="csvRowCount">0</span> students)</h4>
                        <div style="max-height:300px; overflow:auto; border:1px solid #E2E8F0; border-radius:10px; width:100%;">
                            <table style="width:100%; border-collapse:collapse; font-family:Inter,sans-serif; font-size:11px; table-layout:fixed;">
                                <thead>
                                    <tr style="background:#4F46E5; color:white;">
                                        <th style="padding:8px; text-align:left; width:5%;">#</th>
                                        <th style="padding:8px; text-align:left; width:20%;">FIRST NAME</th>
                                        <th style="padding:8px; text-align:left; width:12%;">MIDDLE INITIAL</th>
                                        <th style="padding:8px; text-align:left; width:20%;">LAST NAME</th>
                                        <th style="padding:8px; text-align:left; width:13%;">GENDER</th>
                                        <th style="padding:8px; text-align:left; width:15%;">GRADE LEVEL</th>
                                        <th style="padding:8px; text-align:left; width:15%;">SECTION</th>
                                    </tr>
                                </thead>
                                <tbody id="csvPreviewBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="importResult" style="display:none; margin-bottom:20px; padding:16px; border-radius:10px; font-family:Inter,sans-serif; font-size:13px;"></div>

                    <div style="display:flex; gap:10px;">
                        <button type="button" id="importBtn" onclick="importCSV()" disabled style="font-family:Inter,sans-serif; font-size:13px; font-weight:600; color:white; background:#10B981; border:none; padding:10px 28px; border-radius:8px; cursor:pointer; opacity:0.5;">
                            <i class="ph ph-download-simple"></i> Import Students
                        </button>
                        <button type="button" onclick="resetImport()" style="font-family:Inter,sans-serif; font-size:13px; font-weight:500; color:#64748B; background:#F1F5F9; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <div class="candidates" id="candidate-card">
                <h2 class="header-h2">OFFICIAL CANDIDATES</h2>
                <p class="viewview">..</p>
                <div class="searchbar">
                    <input type="text" id="search-candidate" name="search" placeholder="Search candidates....">
                    <i class="ph ph-magnifying-glass"></i>
                    <div class="buttons-search">
                        <button onclick="document.getElementById('candidate-card').style.display='none';document.getElementById('ballot-preview-card').style.display='block';" class="register" style="background:#4F46E5;">
                            <i class="ph ph-eye" style="margin-right:4px;"></i>Preview Ballot
                        </button>
                        <button onclick="window.location.href='lineup_registration.php'" class="register" style="background:#7C3AED;">
                            <i class="ph ph-people-group" style="margin-right:4px;"></i>Candidates Registration
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table id="table-candidate">
                        <colgroup>

                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col>
                            <col style="width: 10px;">
                        </colgroup>
                        <tr>
                            <th>EDIT</th>
                            <th>ID</th>
                            <th>PHOTO</th>
                            <th>CANDIDATE NAME</th>
                            <th>POSITION</th>
                            <th>PARTY</th>
                            <th>ACADEMIC YEAR</th>
                            <th>ELECTION</th>
                            <th>DELETE</th>
                        </tr>
                        <tbody id="table-body-candidate">
                            <?php


                            $retrieveCandidates = $conn->prepare("SELECT `Candidate_ID`, `CandidateName`, `Position`, 
                            `ElectionName`,  `AcademicYear`, `PartyName`, Photo FROM `registeredcandidate_table` ORDER BY CASE Position WHEN 'President' THEN 1
                            WHEN 'Vice-President' THEN 2 WHEN 'Secretary' THEN 3 WHEN 'Treasurer' THEN 4 WHEN 'Auditor' THEN 5 WHEN 'P.I.O(SHS)' THEN 6 WHEN 'P.I.O(JHS)' THEN 7
                            WHEN 'Protocol Officer(SHS)' THEN 8 WHEN 'Protocol Officer(JHS)' THEN 9 WHEN 'Grade 7 Representative' THEN 10 WHEN 'Grade 8 Representative' THEN 11
                            WHEN 'Grade 9 Representative' THEN 12 WHEN 'Grade 10 Representative' THEN 13 WHEN 'Grade 11 Representative' THEN 14 WHEN 'Grade 12 Representative' THEN 15 ELSE 16 END");
                            $retrieveCandidates->execute();
                            $result = $retrieveCandidates->get_result();

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {

                                    $photoData = $row['Photo'];

                                    if (!empty($photoData)) {

                                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                                        $mime = $finfo->buffer($photoData);

                                        $imgBase64 = base64_encode($photoData);

                                        $imgTag = "<img class='candidate-image' src='data:$mime;base64,$imgBase64' alt='Candidate Photo' style='width:100px; height:100px; object-fit:cover;
                                        border-radius: 50%; box-shadow: 2px 2px 4px rgba(0,0,0,.2); border: 2px solid black; cursor: pointer;'>";
                                    } else {

                                        $imgTag = "<img src='../pictures/user_candidate.jpg' alt='No Photo' style='width:50px; height:50px; object-fit:cover;'>";
                                    }

                                    $photoBase64 = !empty($photoData) ? "data:$mime;base64," . base64_encode($photoData) : "../pictures/user_candidate.jpg";

                                    echo "<tr data-id='{$row['Candidate_ID']}' data-photo='{$photoBase64}'>";
                                    echo "<td><i class='fa-regular fa-pen-to-square editcandidate'></i></td>";
                                    echo "<td>{$row['Candidate_ID']}</td>";
                                    echo "<td>$imgTag</td>";
                                    echo "<td>{$row['CandidateName']}</td>";
                                    echo "<td>{$row['Position']}</td>";
                                    echo "<td>{$row['PartyName']}</td>";
                                    echo "<td>{$row['AcademicYear']}</td>";
                                    echo "<td>{$row['ElectionName']}</td>";
                                    echo "<td><i class='fa-solid fa-trash-can btn-delete-candidate' data-id='{$row['Candidate_ID']}'></i></td>";
                                    echo "</tr>";
                                }

                            } else {
                                echo "<tr><td colspan = '9'>No data found</td></tr>";
                            }



                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BALLOT PREVIEW SECTION -->
            <div class="ballot-preview-card" id="ballot-preview-card" style="display:none;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                    <button onclick="document.getElementById('ballot-preview-card').style.display='none';document.getElementById('candidate-card').style.display='block';" style="font-family:Inter,sans-serif; font-size:12px; font-weight:600; color:#64748B; background:#F1F5F9; border:none; padding:8px 16px; border-radius:8px; cursor:pointer;">
                        <i class="ph ph-arrow-left"></i> Back to Candidates
                    </button>
                </div>
                <h2 class="header-h2">BALLOT PREVIEW</h2>
                <p class="viewview">View-only overview of all registered candidates as they appear on the ballot</p>

                <?php
                $positions = [
                    'President', 'Vice-President', 'Secretary', 'Treasurer', 'Auditor',
                    'P.I.O(SHS)', 'P.I.O(JHS)', 'Protocol Officer(SHS)', 'Protocol Officer(JHS)',
                    'Grade 7 Representative', 'Grade 8 Representative', 'Grade 9 Representative',
                    'Grade 10 Representative', 'Grade 11 Representative', 'Grade 12 Representative'
                ];

                $posStmt = $conn->prepare("SELECT Candidate_ID, CandidateName, PartyName, Photo, Position FROM registeredcandidate_table WHERE Position = ? ORDER BY Candidate_ID ASC");
                if ($posStmt === false) {
                    echo "<p style='color:red;'>Failed to prepare ballot query: " . $conn->error . "</p>";
                }

                foreach ($positions as $pos) {
                    $posStmt->bind_param("s", $pos);
                    $posStmt->execute();
                    $res = $posStmt->get_result();
                    $candidates = [];
                    while ($row = $res->fetch_assoc()) {
                        $candidates[] = $row;
                    }

                ?>

                <div class="ballot-section">
                    <div class="ballot-section-header">
                        <h3><?php echo strtoupper(htmlspecialchars($pos)); ?></h3>
                        <span class="ballot-count"><?php echo count($candidates) . ' candidate' . (count($candidates) !== 1 ? 's' : ''); ?></span>
                    </div>
                    <div class="ballot-candidates-grid">
                        <?php if (empty($candidates)): ?>
                            <div class="ballot-empty">No candidates registered for this position</div>
                        <?php else: ?>
                            <?php foreach ($candidates as $c): ?>
                                <?php
                                $imgSrc = '../pictures/user_candidate.jpg';
                                if (!empty($c['Photo'])) {
                                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                                    $mime = $finfo->buffer($c['Photo']);
                                    $imgSrc = "data:$mime;base64," . base64_encode($c['Photo']);
                                }
                                ?>
                                <div class="ballot-candidate-card">
                                    <img src="<?php echo $imgSrc; ?>" alt="Photo">
                                    <span class="ballot-candidate-name"><?php echo htmlspecialchars($c['CandidateName']); ?></span>
                                    <span class="ballot-candidate-party"><?php echo htmlspecialchars($c['PartyName']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                }
                if ($posStmt) $posStmt->close();
                ?>
            </div>

            <div class="results-card" id="results-card">
                <h2 class="header-h2">ELECTION RESULTS</h2>
                <p class="viewview">View election results in different formats.</p>
                <div class="actions">
                    <div class="selectresult">
                        <select id="result-select-dashboard">
                            <option value="result1" selected>List of Winners</option>
                            <option value="result2">Votes by Grade Level</option>
                            <option value="result3">Vote Gap Analysis</option>
                            <option value="result4">Visual Comparison</option>
                        </select>
                        <i class="ph ph-caret-down caret-result"></i>
                    </div>
                </div>

                <div class="searchbar">
                    <input type="text" id="search-result" name="search" placeholder="Search candidates....">
                    <i class="ph ph-magnifying-glass"></i>
                    <button class="print-vote-status" onclick="printResult()"><i class="ph ph-printer"></i> Print</button>
                    <iframe id="print-result-frame" style="display:none;"></iframe>
                </div>

                <div class="table-container" id="result-table-winners" style="display:block;">
                    <table>
                        <thead>
                            <tr>
                                <th>PICTURE</th>
                                <th>CANDIDATE NAME</th>
                                <th>POSITION</th>
                                <th>PARTY</th>
                                <th>TOTAL VOTES</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-winners"></tbody>
                    </table>
                </div>

                <div class="table-container" id="result-table-grade" style="display:none;">
                    <table>
                        <thead>
                            <tr>
                                <th>PICTURE</th>
                                <th>CANDIDATE NAME</th>
                                <th>POSITION</th>
                                <th>PARTY</th>
                                <th>GRADE 7</th>
                                <th>GRADE 8</th>
                                <th>GRADE 9</th>
                                <th>GRADE 10</th>
                                <th>GRADE 11</th>
                                <th>GRADE 12</th>
                                <th>TOTAL</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-by-grade-level"></tbody>
                    </table>
                </div>

                <div class="table-container" id="result-table-gap" style="display:none;">
                    <table>
                        <thead>
                            <tr>
                                <th>PICTURE</th>
                                <th>CANDIDATE NAME</th>
                                <th>POSITION</th>
                                <th>TOTAL</th>
                                <th>REGISTERED STUDENT TO VOTE</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-gap-result"></tbody>
                    </table>
                </div>

                <div id="result-chart-container" style="display:none;">
                    <div style="background:var(--card); border-radius:var(--radius-lg); padding:24px; border:1px solid var(--border-light); box-shadow:var(--shadow-md); margin-top:10px;" id="charts-wrapper">
                    </div>
                </div>
            </div>

            <!-- ELECTION HISTORY SECTION -->
            <div class="student-card" id="election-history-card" style="display:none;">
                <h2 class="header-h2">ELECTION HISTORY</h2>
                <p class="viewview">View and manage archived election results</p>
                <div class="searchbar">
                    <button onclick="archiveCurrentElection()" class="register" style="font-family:Inter,sans-serif; font-size:13px; font-weight:600; color:white; background:#4F46E5; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">
                        <i class="ph ph-archive"></i> Archive Current Election
                    </button>
                </div>

                <div class="table-container" style="display:block;">
                    <table>
                        <thead>
                            <tr>
                                <th>ELECTION NAME</th>
                                <th>ACADEMIC YEAR</th>
                                <th>REGISTERED</th>
                                <th>VOTED</th>
                                <th>EMPTY</th>
                                <th>DATE ARCHIVED</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="archive-list-body"></tbody>
                    </table>
                </div>

                <div id="archive-detail" style="display:none; margin-top:20px;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                        <button onclick="closeArchiveDetail()" style="font-family:Inter,sans-serif; font-size:12px; font-weight:600; color:#64748B; background:#F1F5F9; border:none; padding:8px 16px; border-radius:8px; cursor:pointer;">
                            <i class="ph ph-arrow-left"></i> Back to List
                        </button>
                        <h3 id="archive-detail-title" style="font-family:Inter,sans-serif; font-size:16px; font-weight:700; color:#0F172A; margin:0; flex:1;"></h3>
                        <button id="archive-print-btn" onclick="" style="font-family:Inter,sans-serif; font-size:12px; font-weight:600; color:white; background:#10B981; border:none; padding:8px 16px; border-radius:8px; cursor:pointer;">
                            <i class="ph ph-printer"></i> Print
                        </button>
                    </div>
                    <div id="archive-detail-info" style="display:flex; gap:16px; margin-bottom:16px;"></div>
                    <div class="table-container" style="display:block;">
                        <table>
                            <thead>
                                <tr>
                                    <th>CANDIDATE NAME</th>
                                    <th>POSITION</th>
                                    <th>PARTY</th>
                                    <th>TOTAL VOTES</th>
                                    <th>G7</th>
                                    <th>G8</th>
                                    <th>G9</th>
                                    <th>G10</th>
                                    <th>G11</th>
                                    <th>G12</th>
                                </tr>
                            </thead>
                            <tbody id="archive-detail-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script src="../javascript/dashboard.js"></script>
    <script src="../javascript/update_account_status.js"></script>
    <script src="../javascript/sidebar.js"></script>
    <script src="../javascript/overview.js"></script>
    <script src="../javascript/manage_election.js"></script>
    <script src="../javascript/section_dropdown.js"></script>
    <script src="../javascript/search_candidates.js"></script>
    <script src="../javascript/candidate_actions.js"></script>
    <script src="../javascript/update_dis_images.js"></script>
    <script src="../javascript/print_students.js"></script>
    <script src="../javascript/get_regis_section.js"></script>
    <script src="../javascript/manage_elec_pop_update.js"></script>
    <script src="../javascript/qr_code.js"></script>
    <script src="../javascript/count_votes.js"></script>

    <script src="../javascript/dashboard_inline.js"></script>

</body>

</html>
<?php
$conn->close();
?>