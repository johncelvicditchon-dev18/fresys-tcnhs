<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>
    <link rel="icon" type="image/png" href="../pictures/tcnhs_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="../css_folder/dashboard.css">

    <?php
    require_once "../includes/database_connection.php";
    session_start();
    $user_name = $_SESSION['user_name'] ?? 'Admin';
    $user_role = $_SESSION['user_role'] ?? 'admin';
    ?>

    <style>
        body {
            background: var(--bg);
            overflow: hidden;
        }

        .page-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.06);
            z-index: 1000;
        }

        .page-header h2 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        .page-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-btn {
            padding: 8px 16px;
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            box-shadow: 0 2px 6px rgba(45, 17, 233, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(45, 17, 233, 0.3);
        }

        .back-btn i {
            font-size: 13px;
        }

        .page-content {
            margin-top: 56px;
            padding: 24px;
            overflow-y: auto;
            height: calc(100vh - 56px);
        }

        .page-title-row {
            margin-bottom: 18px;
        }

        .page-title-row h2 {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 2px 0;
            letter-spacing: -0.3px;
        }

        .page-title-row h2::after {
            content: '';
            display: block;
            width: 32px;
            height: 2.5px;
            border-radius: 2px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            margin-top: 4px;
        }

        .page-title-row p {
            font-size: 12px;
            margin: 0;
            color: var(--text-light);
        }

        .searchbar {
            text-align: left;
            position: relative;
            justify-content: space-between;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            background: var(--card);
            padding: 10px 14px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
        }

        .searchbar input {
            border-radius: var(--radius-md);
            outline: none;
            padding: 9px 12px 9px 36px;
            border: 1.5px solid var(--border);
            width: 300px;
            font-size: 12px;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text-dark);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .searchbar input::placeholder {
            color: var(--text-muted);
        }

        .searchbar input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(45, 17, 233, 0.08);
            background: #fff;
        }

        .searchbar .ph-magnifying-glass {
            position: absolute;
            left: 26px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--text-muted);
            pointer-events: none;
        }

        .table-container {
            box-shadow: var(--shadow-md);
            width: 100%;
            margin: 4px auto;
            border: 1px solid var(--border-light);
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            border-radius: var(--radius-lg);
            background: var(--card);
            position: relative;
        }

        .table-container::-webkit-scrollbar {
            width: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        table {
            border-collapse: collapse;
            font-size: 13px;
            text-align: center;
            width: 100%;
        }

        th, td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border-light);
            font-size: 12px;
        }

        td {
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            max-width: 150px;
            color: var(--text-medium);
        }

        th {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 2;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 12px 14px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.15);
        }

        tr:nth-child(even) {
            background: #F4F6FA;
        }

        tr:nth-child(odd) {
            background: #FFFFFF;
        }

        tr {
            transition: background-color 0.15s ease;
        }

        .table-container tr:hover {
            background: rgba(45, 17, 233, 0.04);
        }

        .access-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .access-admin {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            box-shadow: 0 1px 4px rgba(45, 17, 233, 0.25);
        }

        .access-adviser {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            box-shadow: 0 1px 4px rgba(16, 185, 129, 0.25);
        }

        .access-student {
            background: linear-gradient(135deg, #F59E0B, #D97706);
            color: white;
            box-shadow: 0 1px 4px rgba(245, 158, 11, 0.25);
        }

        .no-data {
            text-align: center;
            color: var(--text-muted);
            font-style: italic;
            padding: 20px;
        }

        .header-profile {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px 5px 6px;
            border-radius: 24px;
            cursor: pointer;
            transition: background 0.2s ease;
            position: relative;
        }

        .header-profile:hover {
            background: var(--border-light);
        }

        .header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 15px;
            flex-shrink: 0;
        }

        .header-username {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
            white-space: nowrap;
        }

        .header-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 240px;
            background: var(--card);
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.15), 0 0 0 1px var(--border-light);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: all 0.2s ease;
            z-index: 1100;
            overflow: hidden;
        }

        .header-profile.open .header-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .header-dropdown-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
        }

        .header-dropdown-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            flex-shrink: 0;
        }

        .header-dropdown-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .header-dropdown-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .header-dropdown-role {
            font-size: 11px;
            color: var(--text-muted);
        }

        .header-dropdown-divider {
            height: 1px;
            background: var(--border-light);
            margin: 0;
        }

        .header-dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 10px 16px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-medium);
            font-family: 'Inter', system-ui, sans-serif;
            transition: background 0.15s ease, color 0.15s ease;
            text-align: left;
        }

        .header-dropdown-item:hover {
            background: var(--border-light);
        }

        .header-dropdown-logout {
            color: var(--red);
        }

        .header-dropdown-logout:hover {
            background: rgba(239, 68, 68, 0.06);
            color: #DC2626;
        }

        .header-dropdown-logout i {
            font-size: 14px;
            width: 16px;
            text-align: center;
            flex-shrink: 0;
        }

        /* ============================================
           RESPONSIVE — TABLETS (max 768px)
           ============================================ */
        @media (max-width: 768px) {
            .page-header {
                padding: 0 16px;
            }

            .page-header h2 {
                font-size: 13px;
            }

            .back-btn {
                padding: 7px 12px;
                font-size: 11px;
            }

            .page-content {
                padding: 16px;
            }

            .searchbar {
                flex-direction: column;
                gap: 8px;
            }

            .searchbar input {
                width: 100%;
            }

            .table-container {
                max-height: calc(100vh - 240px);
            }
        }

        /* ============================================
           RESPONSIVE — PHONES (max 599px)
           ============================================ */
        @media (max-width: 599px) {
            .page-header {
                padding: 0 12px;
                height: 50px;
            }

            .page-header h2 {
                font-size: 12px;
            }

            .back-btn {
                padding: 6px 10px;
                font-size: 10px;
            }

            .back-btn i {
                font-size: 11px;
            }

            .header-username {
                font-size: 11px;
            }

            .header-avatar {
                width: 28px;
                height: 28px;
                font-size: 13px;
            }

            .page-content {
                margin-top: 50px;
                padding: 12px 10px;
                height: calc(100vh - 50px);
            }

            .page-title-row h2 {
                font-size: 15px;
            }

            .searchbar input {
                padding: 8px 10px 8px 32px;
                font-size: 11px;
            }

            table {
                font-size: 11px;
            }

            th {
                padding: 8px 8px;
                font-size: 9px;
            }

            td {
                padding: 6px 8px;
                font-size: 10px;
                max-width: 100px;
            }

            .access-badge {
                padding: 3px 8px;
                font-size: 9px;
            }

            .header-dropdown {
                width: 200px;
                right: 0;
            }
        }

        /* ============================================
           RESPONSIVE — EXTRA SMALL (max 400px)
           ============================================ */
        @media (max-width: 400px) {
            .page-header h2 {
                font-size: 11px;
            }

            .page-title-row h2 {
                font-size: 13px;
            }

            .page-title-row p {
                font-size: 10px;
            }

            table {
                min-width: 500px;
                font-size: 10px;
            }

            th {
                font-size: 8px;
                padding: 6px 6px;
            }

            td {
                padding: 5px 6px;
                font-size: 9px;
            }
        }
    </style>
</head>

<body>
    <header class="page-header">
        <h2>Activity Log</h2>
        <div class="page-header-right">
            <button onclick="backtoDash()" class="back-btn">
                <i class="ph ph-arrow-left"></i> Back
            </button>
            <div class="header-profile" onclick="toggleDropdown(event)">
                <div class="header-avatar"><i class="ph ph-user"></i></div>
                <span class="header-username"><?php echo htmlspecialchars($user_name); ?></span>
                <div class="header-dropdown">
                    <div class="header-dropdown-header">
                        <div class="header-dropdown-avatar"><i class="ph ph-user"></i></div>
                        <div class="header-dropdown-info">
                            <span class="header-dropdown-name"><?php echo htmlspecialchars($user_name); ?></span>
                            <span class="header-dropdown-role"><?php echo ucfirst(htmlspecialchars($user_role)); ?></span>
                        </div>
                    </div>
                    <div class="header-dropdown-divider"></div>
                    <button class="header-dropdown-item header-dropdown-logout" onclick="window.location.href='../auth/logout.php'">
                        <i class="ph ph-sign-out"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="page-content">
        <div class="page-title-row">
            <h2>Account Activity</h2>
            <p>Track all account login and access history</p>
        </div>

        <div class="searchbar">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="search" placeholder="Search by name, username, or access..." onkeyup="filterTable()">
        </div>

        <div class="table-container">
            <table>
                <colgroup>
                    <col style="width: 60px;">
                    <col>
                    <col>
                    <col>
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NAME</th>
                        <th>USERNAME</th>
                        <th>ACCESS</th>
                        <th>DATE & TIME</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php
                    $retrieveAccountLog = $conn->prepare("SELECT account_ID, Teacher_Name, username, Access, Date_Time FROM userloginaccount ORDER BY Date_Time DESC");
                    $retrieveAccountLog->execute();
                    $result = $retrieveAccountLog->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $accessClass = 'access-' . strtolower($row['Access']);
                            echo "<tr>";
                            echo "<td>{$row['account_ID']}</td>";
                            echo "<td>{$row['Teacher_Name']}</td>";
                            echo "<td>{$row['username']}</td>";
                            echo "<td><span class='access-badge {$accessClass}'>{$row['Access']}</span></td>";
                            echo "<td>{$row['Date_Time']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='no-data'>No activity records found</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../javascript/account_datestamp_inline.js"></script>
</body>

</html>
