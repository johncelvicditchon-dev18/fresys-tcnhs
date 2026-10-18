var csvData = null;

function previewCSV(input) {
    var file = input.files[0];
    if (!file) return;
    document.getElementById('csvFileName').textContent = file.name;
    var reader = new FileReader();
    reader.onload = function(e) {
        var lines = e.target.result.split('\n').filter(function(l){ return l.trim() !== ''; });
        if (lines.length < 2) {
            alert('CSV file is empty or has no data rows.');
            return;
        }
        var headers = lines[0].split(',').map(function(h){ return h.trim().toLowerCase().replace(/ /g, '_'); });
        var required = ['first_name','middle_initial','last_name','gender','grade_level','section'];
        var missing = required.filter(function(r){ return headers.indexOf(r) === -1; });
        if (missing.length > 0) {
            alert('Missing columns: ' + missing.join(', '));
            return;
        }
        csvData = [];
        for (var i = 1; i < lines.length; i++) {
            var vals = lines[i].split(',');
            if (vals.length < headers.length) continue;
            var row = {};
            headers.forEach(function(h, idx) { row[h] = vals[idx] ? vals[idx].trim() : ''; });
            if (row.first_name && row.last_name) {
                csvData.push(row);
            }
        }
        document.getElementById('csvRowCount').textContent = csvData.length;
        var tbody = document.getElementById('csvPreviewBody');
        tbody.innerHTML = '';
        var show = csvData.slice(0, 20);
        show.forEach(function(row, idx) {
            var bg = idx % 2 === 0 ? '#FFFFFF' : '#F8FAFC';
            tbody.innerHTML += '<tr style="background:' + bg + ';">' +
                '<td style="padding:6px 8px;">' + (idx + 1) + '</td>' +
                '<td style="padding:6px 8px;">' + row.first_name + '</td>' +
                '<td style="padding:6px 8px;">' + row.middle_initial + '</td>' +
                '<td style="padding:6px 8px;">' + row.last_name + '</td>' +
                '<td style="padding:6px 8px;">' + row.gender + '</td>' +
                '<td style="padding:6px 8px;">' + row.grade_level + '</td>' +
                '<td style="padding:6px 8px;">' + row.section + '</td></tr>';
        });
        if (csvData.length > 20) {
            tbody.innerHTML += '<tr><td colspan="7" style="padding:8px; text-align:center; color:#94A3B8;">... and ' + (csvData.length - 20) + ' more rows</td></tr>';
        }
        document.getElementById('csvPreview').style.display = 'block';
        document.getElementById('importBtn').disabled = false;
        document.getElementById('importBtn').style.opacity = '1';
    };
    reader.readAsText(file);
}

function importCSV() {
    if (!csvData || csvData.length === 0) return;
    var btn = document.getElementById('importBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner"></i> Importing...';
    fetch('../handlers/bulk_import_students.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ students: csvData })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var div = document.getElementById('importResult');
        div.style.display = 'block';
        if (res.status === 'success') {
            div.style.background = '#ECFDF5';
            div.style.color = '#065F46';
            div.style.border = '1px solid #A7F3D0';
            div.innerHTML = '<strong>Success!</strong> ' + res.message;
            setTimeout(function() { location.reload(); }, 1500);
        } else {
            div.style.background = '#FEF2F2';
            div.style.color = '#991B1B';
            div.style.border = '1px solid #FECACA';
            div.innerHTML = '<strong>Error!</strong> ' + res.message;
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-download-simple"></i> Import Students';
        }
    })
    .catch(function(err) {
        alert('Import failed: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-download-simple"></i> Import Students';
    });
}

function resetImport() {
    csvData = null;
    document.getElementById('csvFile').value = '';
    document.getElementById('csvFileName').textContent = '';
    document.getElementById('csvPreview').style.display = 'none';
    document.getElementById('importResult').style.display = 'none';
    document.getElementById('importBtn').disabled = true;
    document.getElementById('importBtn').style.opacity = '0.5';
    document.getElementById('importBtn').innerHTML = '<i class="ph ph-download-simple"></i> Import Students';
}

function downloadTemplate() {
    var csv = 'FIRST NAME,MIDDLE INITIAL,LAST NAME,GENDER,GRADE LEVEL,SECTION\n';
    csv += 'JOHNCEL,C,DITCHON,Male,Grade 7,AGILITY\n';
    csv += 'ANDREW,E,SAMBULAN,Male,Grade 8,CANARY\n';
    csv += 'KATELYN,M,ERMAC,Male,Grade 9,ATHENA\n';
    csv += 'CHRISTIAN GABRIEL,E,DECALIT,Male,Grade 10,SCORPIO\n';
    csv += 'JOHN CLARK,C,GUIPITACIO,Male,Grade 11,ICT 11\n';
    csv += 'ASHLEY,B,ALANDROQUE,Male,Grade 12,ICT 12\n';
    csv += 'MARIA,L,REYES,Female,Grade 7,AGILITY\n';
    csv += 'ANGELICA,S,SANTOS,Female,Grade 8,CANARY\n';
    csv += 'JOSHUA,T,TAN,Female,Grade 9,ATHENA\n';
    csv += 'PRINCESS,D,DIAZ,Female,Grade 10,SCORPIO\n';
    csv += 'MARK, R,RAMIREZ,Female,Grade 11,ICT 11\n';
    csv += 'SOPHIA,A,AQUINO,Female,Grade 12,ICT 12\n';

 
    var blob = new Blob([csv], { type: 'text/csv' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'LIST OF STUDENTS TEMPLATE';
    a.click();
}

function showStudentVotes(studentId) {
    fetch('../api/get_student_votes.php?id=' + studentId)
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status !== 'success') { alert(res.message); return; }
        var s = res.student;
        var votes = res.votes;
        var fullName = s.first_name + ' ' + s.middle_name + ' ' + s.last_name;

        document.getElementById('svp-title').textContent = fullName;
        document.getElementById('svp-subtitle').textContent = s.grade_level + ' - ' + s.section;

        var isCasted = s.vote_status === 'Casted';
        document.getElementById('svp-status-badge').innerHTML =
            '<span style="display:inline-block; padding:4px 12px; border-radius:20px; font-family:Inter,sans-serif; font-size:11px; font-weight:600; ' +
            (isCasted ? 'background:#ECFDF5; color:#065F46;' : 'background:#FEF2F2; color:#991B1B;') + '">' +
            (isCasted ? '<i class="ph ph-check-circle"></i> Vote Casted' : '<i class="ph ph-x-circle"></i> No Vote Casted') + '</span>';

        var html = '';
        if (votes.length === 0) {
            html = '<div style="text-align:center; padding:40px 0; color:#94A3B8; font-family:Inter,sans-serif;">' +
                '<i class="ph ph-ballot" style="font-size:48px; display:block; margin-bottom:12px;"></i>' +
                '<p style="font-size:14px; font-weight:600;">No votes recorded</p>' +
                '<p style="font-size:12px;">This student has not cast a vote yet.</p></div>';
        } else {
            html += '<div style="display:flex; flex-direction:column; gap:8px;">';
            votes.forEach(function(v, i) {
                html += '<div style="display:flex; align-items:center; gap:12px; padding:12px 16px; background:' + (i % 2 === 0 ? '#F8FAFC' : '#FFFFFF') + '; border-radius:10px; border:1px solid #E2E8F0;">' +
                    '<div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#4F46E5,#7C3AED); display:flex; align-items:center; justify-content:center; color:white; font-family:Inter,sans-serif; font-size:13px; font-weight:700; flex-shrink:0;">' + (i + 1) + '</div>' +
                    '<div style="flex:1; min-width:0;">' +
                        '<div style="font-family:Inter,sans-serif; font-size:13px; font-weight:700; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' + v.Candidateee_Name + '</div>' +
                        '<div style="font-family:Inter,sans-serif; font-size:11px; color:#64748B; margin-top:2px;">' + v.Positionnn + '</div>' +
                    '</div>' +
                    '<span style="padding:3px 10px; border-radius:6px; font-family:Inter,sans-serif; font-size:10px; font-weight:600; background:#EEF2FF; color:#4F46E5; white-space:nowrap; flex-shrink:0;">' + v.Partyname + '</span>' +
                '</div>';
            });
            html += '</div>';
        }
        document.getElementById('svp-body').innerHTML = html;
        document.getElementById('student-votes-popup').style.display = 'flex';
    })
    .catch(function(err) { alert('Failed to load votes: ' + err.message); });
}

function closeStudentVotes() {
    document.getElementById('student-votes-popup').style.display = 'none';
}

var notifPanelOpen = false;

function toggleNotificationPanel() {
    var panel = document.getElementById('notification-panel');
    notifPanelOpen = !notifPanelOpen;
    panel.style.display = notifPanelOpen ? 'block' : 'none';
    if (notifPanelOpen) checkDuplicateCodes();
}

function checkDuplicateCodes() {
    fetch('../api/check_duplicate_codes.php')
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status !== 'success') return;
        var badge = document.getElementById('notification-badge');
        var list = document.getElementById('notification-list');
        var totalDupes = res.total_duplicates;
        var votes = res.recent_votes || [];
        var totalCount = totalDupes + votes.length;

        if (totalCount > 0) {
            badge.style.display = 'flex';
            badge.textContent = totalCount;
        } else {
            badge.style.display = 'none';
        }

        var html = '';

        if (totalDupes > 0) {
            html += '<div style="padding:10px 20px; background:#FEF2F2; border-bottom:1px solid #FECACA;">' +
                '<span style="font-family:Inter,sans-serif; font-size:11px; font-weight:700; color:#991B1B; text-transform:uppercase; letter-spacing:0.5px;">' +
                '<i class="ph ph-warning-circle" style="margin-right:4px;"></i>Security Alerts (' + totalDupes + ')' +
                '</span></div>';
            res.alerts.forEach(function(a) {
                html += '<div id="notif-dupe-' + a.code.replace(/\s/g,'') + '" style="padding:14px 20px; border-bottom:1px solid #F1F5F9; display:flex; gap:12px; align-items:flex-start;">' +
                    '<div style="width:36px; height:36px; border-radius:10px; background:#FEF2F2; display:flex; align-items:center; justify-content:center; flex-shrink:0;">' +
                        '<i class="ph ph-warning" style="color:#EF4444; font-size:18px;"></i>' +
                    '</div>' +
                    '<div style="flex:1; min-width:0;">' +
                        '<div style="font-family:Inter,sans-serif; font-size:13px; font-weight:700; color:#0F172A;">Duplicate Verification Code</div>' +
                        '<div style="font-family:Inter,sans-serif; font-size:11px; color:#64748B; margin-top:3px; word-break:break-word;">Code: <strong style="color:#EF4444;">' + a.code + '</strong></div>' +
                        '<div style="font-family:Inter,sans-serif; font-size:11px; color:#64748B; margin-top:2px;">Shared by ' + a.count + ' student(s): ' + a.students + '</div>' +
                        '<button onclick="fixDuplicateCode(\'' + a.code.replace(/'/g, "\\'") + '\', ' + a.count + ')" style="margin-top:8px; background:#EF4444; color:white; border:none; padding:5px 12px; border-radius:6px; font-family:Inter,sans-serif; font-size:11px; font-weight:600; cursor:pointer; transition:background 0.2s;" onmouseover="this.style.background=\'#DC2626\'" onmouseout="this.style.background=\'#EF4444\'">' +
                            '<i class="ph ph-magic-wand" style="margin-right:4px;"></i>Auto-Fix: Regenerate New Codes' +
                        '</button>' +
                    '</div>' +
                '</div>';
            });
        }

        if (votes.length > 0) {
            html += '<div style="padding:10px 20px; background:#ECFDF5; border-bottom:1px solid #A7F3D0;">' +
                '<span style="font-family:Inter,sans-serif; font-size:11px; font-weight:700; color:#065F46; text-transform:uppercase; letter-spacing:0.5px;">' +
                '<i class="ph ph-check-circle" style="margin-right:4px;"></i>Recent Votes (' + votes.length + ')' +
                '</span></div>';
            votes.forEach(function(v) {
                var dt = new Date(v.date_casted);
                var dateStr = dt.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
                var timeStr = dt.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', hour12:true });
                html += '<div style="padding:12px 20px; border-bottom:1px solid #F1F5F9; display:flex; gap:12px; align-items:flex-start;">' +
                    '<div style="width:36px; height:36px; border-radius:10px; background:#ECFDF5; display:flex; align-items:center; justify-content:center; flex-shrink:0;">' +
                        '<i class="ph ph-check-fat" style="color:#10B981; font-size:18px;"></i>' +
                    '</div>' +
                    '<div style="flex:1; min-width:0;">' +
                        '<div style="font-family:Inter,sans-serif; font-size:13px; font-weight:700; color:#0F172A;">' + v.name + '</div>' +
                        '<div style="font-family:Inter,sans-serif; font-size:11px; color:#64748B; margin-top:2px;">' + v.grade + ' - ' + v.section + '</div>' +
                        '<div style="font-family:Inter,sans-serif; font-size:10px; color:#94A3B8; margin-top:3px;">' +
                            '<i class="ph ph-clock" style="margin-right:3px;"></i>' + dateStr + ' at ' + timeStr +
                        '</div>' +
                    '</div>' +
                    '<span style="padding:3px 8px; border-radius:6px; font-family:Inter,sans-serif; font-size:10px; font-weight:600; background:#ECFDF5; color:#065F46; white-space:nowrap; flex-shrink:0;">Voted</span>' +
                '</div>';
            });
        }

        if (totalCount === 0) {
            html = '<div style="text-align:center; padding:40px 20px; color:#94A3B8;">' +
                '<i class="ph ph-shield-check" style="font-size:40px; display:block; margin-bottom:10px; color:#10B981;"></i>' +
                '<div style="font-family:Inter,sans-serif; font-size:13px; font-weight:600;">All Clear</div>' +
                '<div style="font-family:Inter,sans-serif; font-size:11px; margin-top:4px;">No alerts or recent votes.</div></div>';
        }

        list.innerHTML = html;
    })
    .catch(function(err) { console.error('Notification check error:', err); });
}

function clearNotifications() {
    document.getElementById('notification-list').innerHTML = '<div style="text-align:center; padding:40px 20px; color:#94A3B8;">' +
        '<i class="ph ph-check-circle" style="font-size:40px; display:block; margin-bottom:10px; color:#10B981;"></i>' +
        '<div style="font-family:Inter,sans-serif; font-size:13px; font-weight:600;">All Clear</div>' +
        '<div style="font-family:Inter,sans-serif; font-size:11px; margin-top:4px;">No alerts or recent votes.</div></div>';
    document.getElementById('notification-badge').style.display = 'none';
}

function fixDuplicateCode(code, count) {
    if (!confirm('Regenerate new unique verification codes for all ' + count + ' student(s) sharing code "' + code + '"?')) return;
    var el = document.getElementById('notif-dupe-' + code.replace(/\s/g,''));
    if (el) el.innerHTML = '<div style="padding:14px 20px; display:flex; align-items:center; gap:10px;">' +
        '<i class="ph ph-spinner" style="color:#4F46E5; font-size:18px; animation:spin 1s linear infinite;"></i>' +
        '<span style="font-family:Inter,sans-serif; font-size:12px; color:#64748B;">Regenerating codes...</span></div>';

    fetch('../api/fix_duplicate_code.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: code })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status === 'success') {
            checkDuplicateCodes();
        } else {
            alert('Error: ' + res.message);
            checkDuplicateCodes();
        }
    })
    .catch(function(err) { alert('Failed: ' + err.message); checkDuplicateCodes(); });
}

document.addEventListener('click', function(e) {
    var bell = document.getElementById('notification-bell');
    var panel = document.getElementById('notification-panel');
    if (notifPanelOpen && !bell.contains(e.target) && !panel.contains(e.target)) {
        panel.style.display = 'none';
        notifPanelOpen = false;
    }
});

checkDuplicateCodes();
setInterval(checkDuplicateCodes, 30000);

function archiveCurrentElection() {
    if (!confirm('Archive the current election results? This will save a snapshot of all results.')) return;
    fetch('../handlers/election_archive_api.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ election_name: 'SSLG', academic_year: '2026-2027' })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status === 'success') {
            alert(res.message);
            loadElectionHistory();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(function(err) { alert('Failed: ' + err.message); });
}

function loadElectionHistory() {
    fetch('../handlers/election_archive_api.php?action=list')
    .then(function(r) { return r.json(); })
    .then(function(archives) {
        var tbody = document.getElementById('archive-list-body');
        tbody.innerHTML = '';
        if (archives.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:24px; color:#94A3B8; font-family:Inter,sans-serif;">No archived elections yet.</td></tr>';
            return;
        }
        archives.forEach(function(a) {
            var bg = archives.indexOf(a) % 2 === 0 ? '#FFFFFF' : '#F8FAFC';
            var date = new Date(a.archived_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            tbody.innerHTML += '<tr style="background:' + bg + ';">' +
                '<td style="font-weight:600;">' + a.election_name + '</td>' +
                '<td>' + a.academic_year + '</td>' +
                '<td style="text-align:center;">' + a.total_registered + '</td>' +
                '<td style="text-align:center;">' + a.total_casted + '</td>' +
                '<td style="text-align:center;">' + a.total_empty + '</td>' +
                '<td>' + date + '</td>' +
                '<td>' +
                '<button onclick="viewArchive(' + a.archive_id + ')" style="font-family:Inter,sans-serif; font-size:11px; font-weight:600; color:#4F46E5; background:#EEF2FF; border:none; padding:5px 12px; border-radius:6px; cursor:pointer; margin-right:4px;">View</button>' +
                '<button onclick="deleteArchive(' + a.archive_id + ')" style="font-family:Inter,sans-serif; font-size:11px; font-weight:600; color:#EF4444; background:#FEF2F2; border:none; padding:5px 12px; border-radius:6px; cursor:pointer;">Delete</button>' +
                '</td></tr>';
        });
    })
    .catch(function(err) { console.error('Load archive error:', err); });
}

function viewArchive(id) {
    fetch('../handlers/election_archive_api.php?action=view&id=' + id)
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status !== 'success') { alert(res.message); return; }
        var a = res.archive;
        var results = res.results;
        document.getElementById('archive-detail-title').textContent = a.election_name + ' - ' + a.academic_year;
        document.getElementById('archive-print-btn').onclick = function() { printArchiveDetail(id); };
        var date = new Date(a.archived_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        document.getElementById('archive-detail-info').innerHTML =
            '<div style="background:#EEF2FF; padding:12px 20px; border-radius:10px; text-align:center;"><div style="font-family:Inter,sans-serif; font-size:20px; font-weight:800; color:#4F46E5;">' + a.total_registered + '</div><div style="font-family:Inter,sans-serif; font-size:10px; color:#64748B; text-transform:uppercase;">Registered</div></div>' +
            '<div style="background:#ECFDF5; padding:12px 20px; border-radius:10px; text-align:center;"><div style="font-family:Inter,sans-serif; font-size:20px; font-weight:800; color:#10B981;">' + a.total_casted + '</div><div style="font-family:Inter,sans-serif; font-size:10px; color:#64748B; text-transform:uppercase;">Voted</div></div>' +
            '<div style="background:#FEF2F2; padding:12px 20px; border-radius:10px; text-align:center;"><div style="font-family:Inter,sans-serif; font-size:20px; font-weight:800; color:#EF4444;">' + a.total_empty + '</div><div style="font-family:Inter,sans-serif; font-size:10px; color:#64748B; text-transform:uppercase;">Empty</div></div>' +
            '<div style="background:#F8FAFC; padding:12px 20px; border-radius:10px; text-align:center;"><div style="font-family:Inter,sans-serif; font-size:13px; font-weight:600; color:#0F172A;">' + date + '</div><div style="font-family:Inter,sans-serif; font-size:10px; color:#64748B; text-transform:uppercase;">Date Archived</div></div>';

        var tbody = document.getElementById('archive-detail-body');
        tbody.innerHTML = '';
        results.forEach(function(r, idx) {
            var bg = idx % 2 === 0 ? '#FFFFFF' : '#F8FAFC';
            tbody.innerHTML += '<tr style="background:' + bg + ';">' +
                '<td style="font-weight:600;">' + r.CandidateName + '</td>' +
                '<td>' + r.Position + '</td>' +
                '<td>' + r.PartyName + '</td>' +
                '<td style="font-weight:700; color:#4F46E5;">' + r.TotalLevel + '</td>' +
                '<td style="text-align:center;">' + r.Grade_7 + '</td>' +
                '<td style="text-align:center;">' + r.Grade_8 + '</td>' +
                '<td style="text-align:center;">' + r.Grade_9 + '</td>' +
                '<td style="text-align:center;">' + r.Grade_10 + '</td>' +
                '<td style="text-align:center;">' + r.Grade_11 + '</td>' +
                '<td style="text-align:center;">' + r.Grade_12 + '</td></tr>';
        });
        document.getElementById('archive-detail').style.display = 'block';
    })
    .catch(function(err) { console.error('View archive error:', err); });
}

function closeArchiveDetail() {
    document.getElementById('archive-detail').style.display = 'none';
}

function printArchiveDetail(id) {
    var frame = document.getElementById('archive-print-frame');
    frame.src = '../print/print_archive.php?id=' + id;
    frame.onload = function() { frame.contentWindow.print(); };
}

function printArchiveList() {
    var frame = document.getElementById('archive-print-frame');
    frame.src = '../print/print_archive_list.php';
    frame.onload = function() { frame.contentWindow.print(); };
}

function deleteArchive(id) {
    if (!confirm('Delete this archive? This cannot be undone.')) return;
    fetch('../handlers/election_archive_api.php?action=delete&id=' + id)
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status === 'success') {
            loadElectionHistory();
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(function(err) { alert('Delete failed: ' + err.message); });
}

function printResult() {
    const type = document.getElementById('result-select-dashboard').value;
    const paper = 'LETTER';
    let url = '';
    if (type === 'result1') url = '../print/print_result_1.php?paper=' + paper;
    else if (type === 'result2') url = '../print/print_result_2.php?paper=' + paper;
    else if (type === 'result3') url = '../print/print_result_3.php?paper=' + paper;

    const iframe = document.getElementById('print-result-frame');
    iframe.src = url;
    iframe.onload = function () {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    };
}
(function() {
    const resultSelect = document.getElementById('result-select-dashboard');
    const tableWinners = document.getElementById('result-table-winners');
    const tableGrade = document.getElementById('result-table-grade');
    const tableGap = document.getElementById('result-table-gap');
    const chartContainer = document.getElementById('result-chart-container');

    function fetchWinners() {
        fetch('../api/fetch_winners.php')
            .then(r => r.text())
            .then(data => {
                document.getElementById('table-body-winners').innerHTML = data;
            })
            .catch(err => console.error('Error loading winners:', err));
    }

    function fetchGradeLevel() {
        fetch('../api/fetch_level_winners.php')
            .then(r => r.text())
            .then(data => {
                document.getElementById('table-body-by-grade-level').innerHTML = data;
            })
            .catch(err => console.error('Error loading grade level results:', err));
    }

    function fetchGap() {
        fetch('../api/fetch_gap.php')
            .then(r => r.text())
            .then(data => {
                document.getElementById('table-body-gap-result').innerHTML = data;
            })
            .catch(err => console.error('Error loading gap results:', err));
    }

    function showTable(type) {
        tableWinners.style.display = type === 'result1' ? 'block' : 'none';
        tableGrade.style.display = type === 'result2' ? 'block' : 'none';
        tableGap.style.display = type === 'result3' ? 'block' : 'none';
        chartContainer.style.display = type === 'result4' ? 'block' : 'none';
        if (type === 'result4') loadResultChart();
    }

    resultSelect.addEventListener('change', function() {
        showTable(this.value);
    });

    fetchWinners();
    fetchGradeLevel();
    fetchGap();

    setInterval(fetchWinners, 20000);
    setInterval(fetchGradeLevel, 20000);
    setInterval(fetchGap, 20000);

    let resultCharts = [];

    function loadResultChart() {
        fetch('../api/fetch_result_chart.php')
            .then(r => r.json())
            .then(data => {
                if (!data || data.length === 0) return;

                resultCharts.forEach(c => c.destroy());
                resultCharts = [];

                var wrapper = document.getElementById('charts-wrapper');
                wrapper.innerHTML = '';

                var palette = ['#2D11E9','#5B4CFF','#10B981','#F59E0B','#EF4444','#0EA5E9','#F43F5E','#8B5CF6','#14B8A6','#F97316','#6366F1','#EC4899'];

                var grid = document.createElement('div');
                grid.style.cssText = 'display:grid; grid-template-columns:repeat(auto-fill, minmax(480px, 1fr)); gap:20px;';
                wrapper.appendChild(grid);

                data.forEach(function(posData, pi) {
                    var card = document.createElement('div');
                    card.style.cssText = 'background:#FFFFFF; border-radius:14px; padding:0; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;';
                    grid.appendChild(card);

                    var header = document.createElement('div');
                    header.style.cssText = 'background:linear-gradient(135deg,#0F172A,#1E293B); padding:14px 20px; display:flex; align-items:center; justify-content:space-between;';
                    var title = document.createElement('h3');
                    title.textContent = posData.position;
                    title.style.cssText = 'font-family:Inter,sans-serif; font-size:14px; font-weight:700; color:#FFFFFF; margin:0;';
                    header.appendChild(title);
                    var badge = document.createElement('span');
                    var isRepBadge = posData.position.indexOf('Representative') !== -1;
                    var topV = Math.max.apply(null, posData.candidates.map(function(x){return x.votes;}));
                    var topN = posData.candidates.filter(function(x){return x.votes === topV;}).length;
                    if (isRepBadge) {
                        badge.textContent = posData.candidates.length + ' candidate(s) \u2022 2 winners';
                    } else if (topN > 1) {
                        badge.textContent = posData.candidates.length + ' candidate(s) \u2022 TIE';
                    } else {
                        badge.textContent = posData.candidates.length + ' candidate(s) \u2022 1 winner';
                    }
                    badge.style.cssText = 'font-family:Inter,sans-serif; font-size:10px; font-weight:600; color:rgba(255,255,255,0.6); background:rgba(255,255,255,0.1); padding:3px 10px; border-radius:12px;';
                    header.appendChild(badge);
                    card.appendChild(header);

                    var body = document.createElement('div');
                    body.style.cssText = 'padding:16px 20px;';
                    card.appendChild(body);

                    posData.candidates.forEach(function(c, ci) {
                        var isRep = posData.position.indexOf('Representative') !== -1;
                        var maxWinners = isRep ? 2 : 1;

                        var allVotes = posData.candidates.map(function(x){return x.votes;});
                        var maxVotes = Math.max.apply(null, allVotes);
                        var topCount = posData.candidates.filter(function(x){return x.votes === maxVotes;}).length;

                        var isTie = false;
                        var isWinner = false;
                        var rankLabel = '';

                        if (isRep) {
                            var sorted = posData.candidates.slice().sort(function(a,b){return b.votes - a.votes;});
                            var rank = sorted.findIndex(function(x){return x.name === c.name;});
                            isWinner = rank < 2;
                            rankLabel = rank === 0 ? '1ST' : '2ND';
                        } else {
                            if (topCount > 1 && c.votes === maxVotes) {
                                isTie = true;
                            } else if (c.votes === maxVotes) {
                                isWinner = true;
                                rankLabel = '1ST';
                            }
                        }

                        var row = document.createElement('div');
                        row.style.cssText = 'display:flex; align-items:center; gap:14px; margin-bottom:' + (ci < posData.candidates.length - 1 ? '12px' : '0') + '; padding:12px; border-radius:10px; background:' + (isWinner ? 'linear-gradient(135deg,#EFF6FF,#F0F9FF)' : isTie ? 'linear-gradient(135deg,#FFF7ED,#FFFBEB)' : '#F8FAFC') + '; border:1px solid ' + (isWinner ? '#BFDBFE' : isTie ? '#FED7AA' : '#F1F5F9') + '; position:relative;';

                        var showBadges = !isRep;
                        if (isWinner && showBadges) {
                            var crown = document.createElement('div');
                            crown.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="#F59E0B"><path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 14h14v2H5v-2z"/></svg>';
                            crown.style.cssText = 'position:absolute; top:6px; right:10px;';
                            row.appendChild(crown);
                        }

                        var imgWrap = document.createElement('div');
                        imgWrap.style.cssText = 'width:52px; height:52px; border-radius:50%; overflow:hidden; flex-shrink:0; border:3px solid ' + (isWinner ? '#2D11E9' : isTie ? '#F59E0B' : '#CBD5E1') + '; background:#E2E8F0; position:relative;';
                        var img = document.createElement('img');
                        img.src = c.photo || 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 52 52"><rect width="52" height="52" fill="#E2E8F0" rx="26"/><text x="26" y="30" text-anchor="middle" font-family="Arial" font-size="18" fill="#94A3B8">?</text></svg>');
                        img.style.cssText = 'width:100%; height:100%; object-fit:cover;';
                        imgWrap.appendChild(img);
                        row.appendChild(imgWrap);

                        var info = document.createElement('div');
                        info.style.cssText = 'flex:1; min-width:0;';

                        var nameRow = document.createElement('div');
                        nameRow.style.cssText = 'font-family:Inter,sans-serif; font-size:13px; font-weight:700; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;';
                        nameRow.textContent = c.name;
                        info.appendChild(nameRow);

                        var partyRow = document.createElement('div');
                        partyRow.style.cssText = 'font-family:Inter,sans-serif; font-size:11px; color:#64748B; margin-top:1px;';
                        partyRow.textContent = c.party;
                        info.appendChild(partyRow);

                        var barWrap = document.createElement('div');
                        barWrap.style.cssText = 'width:100%; height:6px; background:#E2E8F0; border-radius:3px; margin-top:8px; overflow:hidden;';
                        var barFill = document.createElement('div');
                        var pct = maxVotes > 0 ? (c.votes / maxVotes * 100) : 0;
                        barFill.style.cssText = 'height:100%; width:' + pct + '%; background:linear-gradient(90deg,' + (isTie ? '#F59E0B,#D97706' : palette[ci % palette.length] + ',' + palette[(ci+1) % palette.length]) + '); border-radius:3px; transition:width 0.8s cubic-bezier(0.4,0,0.2,1);';
                        barWrap.appendChild(barFill);
                        info.appendChild(barWrap);

                        row.appendChild(info);

                        var voteBox = document.createElement('div');
                        voteBox.style.cssText = 'text-align:center; flex-shrink:0; min-width:56px;';
                        var voteNum = document.createElement('div');
                        voteNum.style.cssText = 'font-family:Inter,sans-serif; font-size:22px; font-weight:800; color:' + (isWinner ? '#2D11E9' : isTie ? '#D97706' : palette[ci % palette.length]) + '; line-height:1;';
                        voteNum.textContent = c.votes;
                        voteBox.appendChild(voteNum);
                        var voteLabel = document.createElement('div');
                        voteLabel.style.cssText = 'font-family:Inter,sans-serif; font-size:8px; color:#94A3B8; text-transform:uppercase; letter-spacing:0.5px; margin-top:3px;';
                        voteLabel.textContent = 'votes';
                        voteBox.appendChild(voteLabel);
                        row.appendChild(voteBox);

                        if (isWinner && showBadges) {
                            var winTag = document.createElement('div');
                            winTag.textContent = 'WINNER';
                            winTag.style.cssText = 'position:absolute; top:-1px; right:40px; font-family:Inter,sans-serif; font-size:8px; font-weight:800; color:white; background:linear-gradient(135deg,#2D11E9,#5B4CFF); padding:2px 8px; border-radius:0 0 6px 6px; letter-spacing:0.8px;';
                            row.appendChild(winTag);

                            var rankTag = document.createElement('div');
                            rankTag.textContent = rankLabel;
                            rankTag.style.cssText = 'position:absolute; top:-1px; right:105px; font-family:Inter,sans-serif; font-size:8px; font-weight:800; color:white; background:linear-gradient(135deg,#F59E0B,#D97706); padding:2px 6px; border-radius:0 0 6px 6px; letter-spacing:0.5px;';
                            row.appendChild(rankTag);
                        }

                        if (isTie) {
                            var tieTag = document.createElement('div');
                            tieTag.textContent = 'TIE';
                            tieTag.style.cssText = 'position:absolute; top:-1px; right:40px; font-family:Inter,sans-serif; font-size:8px; font-weight:800; color:white; background:linear-gradient(135deg,#F59E0B,#D97706); padding:2px 8px; border-radius:0 0 6px 6px; letter-spacing:0.8px;';
                            row.appendChild(tieTag);
                        }

                        body.appendChild(row);
                    });
                });
            })
            .catch(err => console.error('Error loading chart:', err));
    }

    const searchResult = document.getElementById('search-result');
    searchResult.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const visibleTable = document.querySelector('.results-card .table-container[style*="block"] tbody');
        if (!visibleTable) return;
        const rows = visibleTable.querySelectorAll('tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
})();
