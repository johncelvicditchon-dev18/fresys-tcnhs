<?php
session_start();
$user_name = $_SESSION['user_name'] ?? 'Adviser';
$user_role = $_SESSION['user_role'] ?? 'Adviser';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students | Tangub City NHS</title>
    <link rel="stylesheet" href="../css_folder/adviser_table.css">

    <link rel="icon" type="image/png" href="../pictures/tcnhs_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">

</head>

<body>
    <!--DELETE STUDENT HTML---->
    <div class="delete-pop-up" id="delete-pop-up"></div>
    <div class="delete-box" id="delete-box">
        <div class="ph-warning-div">
            <i class='fa-solid fa-trash-can'></i>
        </div>
        <h3 class="questiondelete">Delete Student?</h3>
        <p class="delete-message">
            This action cannot be undone. Are you sure you want to delete this student?
        </p>
        <div class="btn-to-delete">
            <button type="button" class="cancel-btn" id="cancel-student"
                onclick="cancelDeletingStudent()">Cancel</button>
            <button type="button" class="delete-btn" id="delete-student">Delete</button>

        </div>
    </div>
    <!--UPDATE STUDENTS INFO HTML---->
    <div class="update-student" id="update_student">

        <div class="update-box">
            <div class="header-update-student">
                <h2>UPDATE STUDENT INFORMATION</h2>
                <i class="ph ph-x" id="close"></i>
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



    <header class="header">
        <div class="header-left">
            <img src="../pictures/tcnhs_logo.png" class="logo" alt="logo">
            <img src="../pictures/sslg_logo.jpg" alt="logo" class="logo">
            <h2 class="registeredstudent">REGISTERED LEARNERS</h2>
        </div>

        <div class="header-right">
            <button onclick="proceedToRegistration()" class="register"><i
                    class="ph ph-user-plus"></i>Registration</button>
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
                    <button class="header-dropdown-item header-dropdown-logout" onclick="backtoPage()">
                        <i class="ph ph-sign-out"></i>
                        <span>Log Out</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="searchbar">
        <input type="text" id="search-student-in-adviser" name="search" placeholder="Search student....">
        <i class="ph ph-magnifying-glass"></i>
    </div>
    <div class="table-container">
        <table>
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
                <th>FIRST NAME</th>
                <th>MIDDLE INITIAL</th>
                <th>LAST NAME</th>
                <th>GENDER</th>
                <th>GRADE LEVEL</th>
                <th>SECTION</th>
                <th>VOTE STATUS</th>
                <th>DELETE</th>
            </tr>

            <tbody id="table-body-adviser-student">
                <?php
                require_once "../includes/database_connection.php";

                $retrieveStudentData = $conn->prepare("SELECT id, verification_code, first_name, middle_name, last_name, gender, grade_level, section, vote_status
                 FROM student_info ORDER BY CASE grade_level WHEN 'Grade 7' THEN 1 WHEN 'Grade 8' THEN 2 WHEN 'Grade 9' THEN 3 WHEN 'Grade 10' THEN 4 
                 WHEN 'Grade 11' THEN 5 WHEN 'Grade 12' THEN 6 ELSE 7 END, CASE gender WHEN 'Male' THEN 1 WHEN 'Female' THEN 2 ELSE 3 END, section ASC, first_name ASC");
                $retrieveStudentData->execute();
                $result = $retrieveStudentData->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr data-id='{$row['id']}'>";
                        echo "<td><i class='fa-regular fa-pen-to-square editicon'></i></td>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td class='verificationcodenone'>{$row['verification_code']}</td>";
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
                    }
                } else {
                    echo "<tr><td colspan='11'>No data found!</td></tr> ";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
<script src="../javascript/adviser_registered_table.js"></script>

</html>