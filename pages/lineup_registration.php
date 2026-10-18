<?php
require_once "../includes/database_connection.php";
session_start();
if (!isset($_SESSION['user_name'])) {
    header('Location: ../auth/login.php');
    exit;
}
$user_name = $_SESSION['user_name'];

$positions = $conn->query("SELECT positionID, position FROM position ORDER BY CASE position
    WHEN 'President' THEN 1 WHEN 'Vice-President' THEN 2 WHEN 'Secretary' THEN 3
    WHEN 'Treasurer' THEN 4 WHEN 'Auditor' THEN 5 WHEN 'P.I.O(SHS)' THEN 6
    WHEN 'P.I.O(JHS)' THEN 7 WHEN 'Protocol Officer(SHS)' THEN 8 WHEN 'Protocol Officer(JHS)' THEN 9
    WHEN 'Grade 7 Representative' THEN 10 WHEN 'Grade 8 Representative' THEN 11
    WHEN 'Grade 9 Representative' THEN 12 WHEN 'Grade 10 Representative' THEN 13
    WHEN 'Grade 11 Representative' THEN 14 WHEN 'Grade 12 Representative' THEN 15 ELSE 16 END");

$parties = $conn->query("SELECT partyname_ID, partyname FROM partyname_table ORDER BY partyname ASC");
$years = $conn->query("SELECT academicyear_ID, academicyear FROM academicyear_table ORDER BY academicyear DESC");
$elections = $conn->query("SELECT Election_ID, Election_Name FROM electionname_table ORDER BY Election_Name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lineup Registration | FRESYS</title>
    <link rel="icon" href="../pictures/tcnhs_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/phosphor-icons@2.1.1/src/css/icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:#F0F2F5; min-height:100vh; width:100%; overflow-x:hidden; }

        .lineup-header { background:linear-gradient(135deg,#1E1B4B,#4F46E5); padding:32px 40px; color:white; width:100%; }
        .lineup-header h1 { font-size:22px; font-weight:800; margin-bottom:4px; }
        .lineup-header p { font-size:13px; color:rgba(255,255,255,0.7); }

        .lineup-container { max-width:1100px; margin:0 auto; padding:24px 40px 60px; }

        .lineup-meta { background:white; border-radius:14px; padding:24px; margin-bottom:24px; box-shadow:0 1px 4px rgba(0,0,0,0.06); display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
        .lineup-meta .field label { display:block; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }

        .lineup-section { margin-bottom:20px; }
        .lineup-section-title { font-size:13px; font-weight:700; color:#4F46E5; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px; padding-bottom:8px; border-bottom:2px solid #EEF2FF; display:flex; align-items:center; gap:8px; }
        .lineup-section-title i { font-size:16px; }

        .position-card { background:white; border-radius:12px; padding:16px 20px; margin-bottom:10px; box-shadow:0 1px 3px rgba(0,0,0,0.05); display:flex; align-items:center; gap:16px; transition:box-shadow 0.2s; }
        .position-card:hover { box-shadow:0 4px 12px rgba(0,0,0,0.08); }
        .position-label { min-width:160px; max-width:200px; flex-shrink:0; }
        .position-label .pos-name { font-size:13px; font-weight:700; color:#0F172A; }
        .position-label .pos-badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:600; background:#EEF2FF; color:#4F46E5; margin-top:3px; }

        .candidate-slots { flex:1; display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
        .candidate-slot { flex:1; min-width:180px; display:flex; gap:10px; align-items:center; }
        .candidate-slot .photo-upload { width:50px; height:50px; border-radius:10px; border:2px dashed #CBD5E1; display:flex; align-items:center; justify-content:center; cursor:pointer; overflow:hidden; flex-shrink:0; transition:border-color 0.2s, background 0.2s; position:relative; }
        .candidate-slot .photo-upload:hover { border-color:#4F46E5; background:#F5F3FF; }
        .candidate-slot .photo-upload img { width:100%; height:100%; object-fit:cover; border-radius:8px; }
        .candidate-slot .photo-upload i { font-size:18px; color:#94A3B8; }
        .candidate-slot .photo-upload input { position:absolute; inset:0; opacity:0; cursor:pointer; }
        .candidate-slot input[type="text"] { flex:1; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-family:'Inter',sans-serif; font-size:13px; font-weight:500; color:#0F172A; transition:border-color 0.2s; min-width:0; text-transform:uppercase; letter-spacing:0.3px; }
        .candidate-slot input[type="text"]:focus { outline:none; border-color:#4F46E5; }
        .candidate-slot input[type="text"]::placeholder { color:#CBD5E1; }

        .slot-separator { width:1px; height:40px; background:#E2E8F0; flex-shrink:0; }

        .submit-section { text-align:center; margin-top:32px; }
        .submit-btn { background:linear-gradient(135deg,#4F46E5,#7C3AED); color:white; border:none; padding:14px 48px; border-radius:12px; font-family:'Inter',sans-serif; font-size:15px; font-weight:700; cursor:pointer; transition:transform 0.2s, box-shadow 0.2s; box-shadow:0 4px 16px rgba(79,70,229,0.3); }
        .submit-btn:hover { transform:translateY(-2px); box-shadow:0 6px 24px rgba(79,70,229,0.4); }
        .submit-btn:active { transform:translateY(0); }
        .submit-btn:disabled { opacity:0.6; cursor:not-allowed; transform:none; }

        .result-msg { margin-top:16px; padding:12px 20px; border-radius:10px; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; display:none; }
        .result-msg.success { display:block; background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0; }
        .result-msg.error { display:block; background:#FEF2F2; color:#991B1B; border:1px solid #FECACA; }

        .back-btn { position:fixed; top:20px; right:20px; background:white; border:none; width:40px; height:40px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:100; transition:box-shadow 0.2s; }
        .back-btn:hover { box-shadow:0 4px 16px rgba(0,0,0,0.15); }
        .back-btn i { font-size:18px; color:#475569; }

        .lineup-meta .field { position:relative; }
        .lineup-meta .field select { width:100%; padding:10px 36px 10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; color:#0F172A; background:white; cursor:pointer; transition:border-color 0.2s; appearance:none; -webkit-appearance:none; -moz-appearance:none; }
        .lineup-meta .field::after { content:''; position:absolute; right:14px; bottom:14px; width:0; height:0; border-left:5px solid transparent; border-right:5px solid transparent; border-top:6px solid #64748B; pointer-events:none; }
        .lineup-meta .field select:focus { outline:none; border-color:#4F46E5; }

        @media (max-width: 768px) {
            .lineup-header { padding:24px 20px; }
            .lineup-header h1 { font-size:18px; }
            .lineup-container { padding:16px 16px 40px; }
            .lineup-meta { grid-template-columns:1fr; padding:16px; gap:12px; }
            .position-card { flex-direction:column; align-items:stretch; padding:14px 16px; gap:10px; }
            .position-label { min-width:0; max-width:none; width:100%; }
            .candidate-slots { flex-direction:column; gap:10px; }
            .candidate-slot { min-width:0; flex-direction:row; }
            .candidate-slot input[type="text"] { min-width:0; flex:1; }
            .slot-separator { width:100%; height:1px; }
            .back-btn { top:12px; right:12px; width:36px; height:36px; }
        }

        @media (max-width: 480px) {
            .lineup-header { padding:20px 14px; }
            .lineup-header h1 { font-size:16px; }
            .lineup-header p { font-size:11px; }
            .lineup-container { padding:12px 10px 32px; }
            .lineup-meta { padding:14px; }
            .position-card { padding:12px; }
            .candidate-slot .photo-upload { width:42px; height:42px; }
            .submit-btn { width:100%; padding:14px; }
        }
    </style>
</head>
<body>

<button class="back-btn" onclick="window.location.href = '../pages/dashboard.php'" title="Back to Dashboard">
    <i class="fa-solid fa-arrow-left"></i>
</button>

<div class="lineup-header">
    <h1><i class="ph ph-users-three" style="margin-right:8px;"></i>Lineup Registration</h1>
    <p>Register an entire party lineup — from President to Grade 12 Representatives — in one form.</p>
</div>

<div class="lineup-container">
    <form id="lineupForm" enctype="multipart/form-data">
        <div class="lineup-meta">
            <div class="field">
                <label>Election</label>
                <select name="election" id="lineup-election" required>
                    <option value="" disabled selected>Select Election</option>
                    <?php while ($e = $elections->fetch_assoc()): ?>
                        <option value="<?php echo $e['Election_ID']; ?>"><?php echo htmlspecialchars($e['Election_Name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="field">
                <label>Academic Year</label>
                <select name="year" id="lineup-year" required>
                    <option value="" disabled selected>Select Year</option>
                    <?php while ($y = $years->fetch_assoc()): ?>
                        <option value="<?php echo $y['academicyear_ID']; ?>"><?php echo htmlspecialchars($y['academicyear']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="field">
                <label>Party</label>
                <select name="party" id="lineup-party" required>
                    <option value="" disabled selected>Select Party</option>
                    <?php while ($p = $parties->fetch_assoc()): ?>
                        <option value="<?php echo $p['partyname_ID']; ?>"><?php echo htmlspecialchars($p['partyname']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="lineup-section">
            <div class="lineup-section-title"><i class="ph ph-crown"></i> Executive Officers</div>

            <div class="position-card">
                <div class="position-label">
                    <div class="pos-name">President</div>
                    <div class="pos-badge">1 Candidate</div>
                </div>
                <div class="candidate-slots">
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-president-0" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-president-0" accept="image/*" onchange="previewPhoto(this, 'photo-president-0')">
                        </div>
                        <input type="text" name="name-president-0" placeholder="Full Name">
                    </div>
                </div>
            </div>

            <div class="position-card">
                <div class="position-label">
                    <div class="pos-name">Vice-President</div>
                    <div class="pos-badge">1 Candidate</div>
                </div>
                <div class="candidate-slots">
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-vice-president-0" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-vice-president-0" accept="image/*" onchange="previewPhoto(this, 'photo-vice-president-0')">
                        </div>
                        <input type="text" name="name-vice-president-0" placeholder="Full Name">
                    </div>
                </div>
            </div>

            <div class="position-card">
                <div class="position-label">
                    <div class="pos-name">Secretary</div>
                    <div class="pos-badge">1 Candidate</div>
                </div>
                <div class="candidate-slots">
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-secretary-0" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-secretary-0" accept="image/*" onchange="previewPhoto(this, 'photo-secretary-0')">
                        </div>
                        <input type="text" name="name-secretary-0" placeholder="Full Name">
                    </div>
                </div>
            </div>

            <div class="position-card">
                <div class="position-label">
                    <div class="pos-name">Treasurer</div>
                    <div class="pos-badge">1 Candidate</div>
                </div>
                <div class="candidate-slots">
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-treasurer-0" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-treasurer-0" accept="image/*" onchange="previewPhoto(this, 'photo-treasurer-0')">
                        </div>
                        <input type="text" name="name-treasurer-0" placeholder="Full Name">
                    </div>
                </div>
            </div>

            <div class="position-card">
                <div class="position-label">
                    <div class="pos-name">Auditor</div>
                    <div class="pos-badge">1 Candidate</div>
                </div>
                <div class="candidate-slots">
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-auditor-0" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-auditor-0" accept="image/*" onchange="previewPhoto(this, 'photo-auditor-0')">
                        </div>
                        <input type="text" name="name-auditor-0" placeholder="Full Name">
                    </div>
                </div>
            </div>

            <div class="position-card">
                <div class="position-label">
                    <div class="pos-name">P.I.O (SHS)</div>
                    <div class="pos-badge">1 Candidate</div>
                </div>
                <div class="candidate-slots">
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-pio-shs-0" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-pio-shs-0" accept="image/*" onchange="previewPhoto(this, 'photo-pio-shs-0')">
                        </div>
                        <input type="text" name="name-pio-shs-0" placeholder="Full Name">
                    </div>
                </div>
            </div>

            <div class="position-card">
                <div class="position-label">
                    <div class="pos-name">P.I.O (JHS)</div>
                    <div class="pos-badge">1 Candidate</div>
                </div>
                <div class="candidate-slots">
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-pio-jhs-0" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-pio-jhs-0" accept="image/*" onchange="previewPhoto(this, 'photo-pio-jhs-0')">
                        </div>
                        <input type="text" name="name-pio-jhs-0" placeholder="Full Name">
                    </div>
                </div>
            </div>

            <div class="position-card">
                <div class="position-label">
                    <div class="pos-name">Protocol Officer (SHS)</div>
                    <div class="pos-badge">1 Candidate</div>
                </div>
                <div class="candidate-slots">
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-protocol-shs-0" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-protocol-shs-0" accept="image/*" onchange="previewPhoto(this, 'photo-protocol-shs-0')">
                        </div>
                        <input type="text" name="name-protocol-shs-0" placeholder="Full Name">
                    </div>
                </div>
            </div>

            <div class="position-card">
                <div class="position-label">
                    <div class="pos-name">Protocol Officer (JHS)</div>
                    <div class="pos-badge">1 Candidate</div>
                </div>
                <div class="candidate-slots">
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-protocol-jhs-0" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-protocol-jhs-0" accept="image/*" onchange="previewPhoto(this, 'photo-protocol-jhs-0')">
                        </div>
                        <input type="text" name="name-protocol-jhs-0" placeholder="Full Name">
                    </div>
                </div>
            </div>
        </div>

        <div class="lineup-section">
            <div class="lineup-section-title"><i class="ph ph-student"></i> Grade Representatives (2 per Grade)</div>

            <?php
            $repGrades = [
                'grade-7' => 'Grade 7 Representative',
                'grade-8' => 'Grade 8 Representative',
                'grade-9' => 'Grade 9 Representative',
                'grade-10' => 'Grade 10 Representative',
                'grade-11' => 'Grade 11 Representative',
                'grade-12' => 'Grade 12 Representative'
            ];
            foreach ($repGrades as $key => $label):
            ?>
            <div class="position-card">
                <div class="position-label">
                    <div class="pos-name"><?php echo $label; ?></div>
                    <div class="pos-badge">2 Candidates</div>
                </div>
                <div class="candidate-slots">
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-<?php echo $key; ?>-rep-0" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-<?php echo $key; ?>-rep-0" accept="image/*" onchange="previewPhoto(this, 'photo-<?php echo $key; ?>-rep-0')">
                        </div>
                        <input type="text" name="name-<?php echo $key; ?>-rep-0" placeholder="Candidate 1 Name">
                    </div>
                    <div class="slot-separator"></div>
                    <div class="candidate-slot">
                        <div class="photo-upload" id="photo-<?php echo $key; ?>-rep-1" title="Upload Photo">
                            <i class="ph ph-camera"></i>
                            <input type="file" name="photo-<?php echo $key; ?>-rep-1" accept="image/*" onchange="previewPhoto(this, 'photo-<?php echo $key; ?>-rep-1')">
                        </div>
                        <input type="text" name="name-<?php echo $key; ?>-rep-1" placeholder="Candidate 2 Name">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="submit-section">
            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="ph ph-rocket-launch" style="margin-right:6px;"></i>Register Entire Lineup
            </button>
            <div class="result-msg" id="resultMsg"></div>
        </div>
    </form>
</div>

<script src="../javascript/lineup_registration_inline.js"></script>

</body>
</html>
