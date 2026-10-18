import { auth, signOut, onSnapshot, collection, db } from "./firebase.js";
import { requireStaff } from "./auth.js";
import * as D from "./data.js";
import {
  $, $$, escapeHtml, toast, openModal, closeModal, btnLoading, fileToDataUrl, fmtDate
} from "./ui.js";

let profile = null;
let candidates = [];
let students = [];
let staffList = [];
let settings = null;
let candTerm = "", stuTerm = "", accTerm = "";
let editPhoto = null;

const VIEW_META = {
  overview: ["Overview", "Election at a glance"],
  candidates: ["Candidates", "Manage registered candidates"],
  students: ["Students", "Registered voters and verification codes"],
  accounts: ["Accounts", "Approve and manage staff access"],
  settings: ["Settings", "Election configuration"],
  archive: ["Archive", "Past election results"]
};

window.doLogout = () => signOut(auth).finally(() => location.replace("login.html"));

// ---------------------------------------------------------------- bootstrap
async function main() {
  profile = await requireStaff();
  document.body.classList.toggle("is-admin", profile.role === "Admin");
  $("#userName").textContent = profile.name || profile.username;
  $("#userRole").textContent = profile.role;

  setupNav();
  settings = await D.getSettings();
  fillSettingsForm(settings);
  await fillLookupOptions();

  watchCandidates();
  watchStudents();
  if (profile.role === "Admin") watchStaff();
  watchOverview();
  renderArchive();

  $("#loading").style.display = "none";
  $("#app").style.display = "";
}

function setupNav() {
  $$(".nav-item[data-view]").forEach((btn) => {
    btn.addEventListener("click", () => switchView(btn.dataset.view));
  });
}
function switchView(view) {
  $$(".nav-item[data-view]").forEach((b) => b.classList.toggle("active", b.dataset.view === view));
  $$(".view").forEach((v) => v.classList.toggle("active", v.id === `view-${view}`));
  const [t, s] = VIEW_META[view] || ["Dashboard", ""];
  $("#viewTitle").textContent = t;
  $("#viewSub").textContent = s;
  $("#sidebar").classList.remove("open");
  if (view === "archive") renderArchive();
}

// ---------------------------------------------------------------- overview
function watchOverview() {
  onSnapshot(collection(db, "students"), (snap) => {
    students = snap.docs.map((d) => ({ id: d.id, ...d.data() }));
    renderOverview();
    renderStudents();
  });
}

function renderOverview() {
  const registered = students.length;
  const casted = students.filter((s) => s.voteStatus === "Casted").length;
  const turnout = registered ? Math.round((casted / registered) * 1000) / 10 : 0;
  $("#statsGrid").innerHTML = `
    <div class="stat"><div class="ico"><i class="ph ph-users"></i></div><div class="val">${registered}</div><div class="lbl2">Registered Voters</div></div>
    <div class="stat"><div class="ico"><i class="ph ph-check-square"></i></div><div class="val">${casted}</div><div class="lbl2">Ballots Cast</div></div>
    <div class="stat"><div class="ico"><i class="ph ph-user-minus"></i></div><div class="val">${registered - casted}</div><div class="lbl2">Not Yet Voted</div></div>
    <div class="stat"><div class="ico"><i class="ph ph-percent"></i></div><div class="val">${turnout}%</div><div class="lbl2">Turnout</div></div>`;

  const byGrade = {};
  for (const g of D.GRADES) byGrade[g] = { reg: 0, cast: 0 };
  for (const s of students) {
    if (!byGrade[s.gradeLevel]) byGrade[s.gradeLevel] = { reg: 0, cast: 0 };
    byGrade[s.gradeLevel].reg++;
    if (s.voteStatus === "Casted") byGrade[s.gradeLevel].cast++;
  }
  $("#gradeTurnout").innerHTML = D.GRADES.map((g) => {
    const d = byGrade[g] || { reg: 0, cast: 0 };
    const pct = d.reg ? Math.round((d.cast / d.reg) * 100) : 0;
    return `<div class="grade-row">
      <div class="gl">${g}</div>
      <div class="gbar"><div class="progress"><span style="width:${pct}%"></span></div></div>
      <div class="gv"><b>${d.cast}</b>/${d.reg} &middot; ${pct}%</div>
    </div>`;
  }).join("");

  $("#statusPanel").innerHTML = `
    <div class="status-line"><span>Election</span><b>${escapeHtml(settings.electionName)}</b></div>
    <div class="status-line"><span>Academic Year</span><b>${escapeHtml(settings.academicYear)}</b></div>
    <div class="status-line"><span>Voting Status</span><b>${settings.votingOpen ? '<span class="badge badge-green">OPEN</span>' : '<span class="badge badge-red">CLOSED</span>'}</b></div>
    <div class="status-line"><span>Results Visibility</span><b>${settings.resultsPublic ? '<span class="badge badge-blue">Public</span>' : '<span class="badge badge-amber">Staff only</span>'}</b></div>
    <div class="status-line"><span>Registered Candidates</span><b>${candidates.length}</b></div>`;
}

// ---------------------------------------------------------------- candidates
function watchCandidates() {
  onSnapshot(collection(db, "candidates"), (snap) => {
    candidates = snap.docs.map((d) => ({ id: d.id, ...d.data() }))
      .sort((a, b) => D.POSITION_ORDER(a.position) - D.POSITION_ORDER(b.position) || (a.name || "").localeCompare(b.name || ""));
    renderCandidates();
    if (settings) renderOverview();
  });
}

function renderCandidates() {
  const term = candTerm.toLowerCase();
  const rows = candidates.filter((c) =>
    !term || (c.name || "").toLowerCase().includes(term) || (c.position || "").toLowerCase().includes(term) || (c.party || "").toLowerCase().includes(term));
  $("#candBody").innerHTML = rows.length ? rows.map((c) => `
    <tr>
      <td><img class="row-photo" src="${c.photo || "assets/pictures/user_candidate.jpg"}" onerror="this.src='assets/pictures/user_candidate.jpg'" alt=""></td>
      <td>${escapeHtml(c.name)}</td>
      <td><span class="badge badge-blue">${escapeHtml(c.position)}</span></td>
      <td>${escapeHtml(c.party || "-")}</td>
      <td>${escapeHtml(c.year || "-")}</td>
      <td><div class="actions">
        <button class="btn btn-ghost btn-sm" onclick="openCandidate('${c.id}')"><i class="ph ph-pencil-simple"></i></button>
        <button class="btn btn-danger btn-sm" onclick="removeCandidate('${c.id}','${escapeHtml(c.name).replace(/'/g, "\\'")}')"><i class="ph ph-trash"></i></button>
      </div></td>
    </tr>`).join("") : `<tr><td colspan="6"><div class="empty"><i class="ph ph-user-list"></i>No candidates yet.</div></td></tr>`;
}

window.openCandidate = async function (id) {
  editPhoto = null;
  const modal = $("#candidateModal");
  const posSel = $("#candPosition");
  posSel.innerHTML = D.ALL_POSITIONS.map((p) => `<option>${p}</option>`).join("");
  $("#candPreview").src = "assets/pictures/user_candidate.jpg";
  if (id) {
    const c = candidates.find((x) => x.id === id);
    if (!c) return;
    $("#candModalTitle").textContent = "Edit Candidate";
    $("#candId").value = c.id;
    $("#candName").value = c.name || "";
    posSel.value = c.position || D.ALL_POSITIONS[0];
    $("#candParty").value = c.party || "";
    $("#candYear").value = c.year || settings.academicYear;
    if (c.photo) $("#candPreview").src = c.photo;
  } else {
    $("#candModalTitle").textContent = "Add Candidate";
    $("#candId").value = "";
    $("#candName").value = "";
    posSel.value = D.ALL_POSITIONS[0];
    $("#candParty").value = "";
    $("#candYear").value = settings.academicYear;
  }
  $("#candPhoto").value = "";
  openModal("candidateModal");
};

$("#candPhoto").addEventListener("change", async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  try { editPhoto = await fileToDataUrl(file, 400); $("#candPreview").src = editPhoto; }
  catch (_) { toast("Could not read that image.", "error"); }
});

window.saveCandidate = async function () {
  const id = $("#candId").value;
  const payload = {
    name: $("#candName").value.trim(),
    position: $("#candPosition").value,
    party: $("#candParty").value.trim(),
    year: $("#candYear").value.trim() || settings.academicYear,
    election: settings.electionName
  };
  if (!payload.name) { toast("Candidate name is required.", "error"); return; }
  if (editPhoto) payload.photo = editPhoto;
  const btn = $("#candSave");
  btnLoading(btn, true);
  try {
    if (id) await D.updateCandidate(id, payload);
    else await D.addCandidate(payload);
    toast(id ? "Candidate updated." : "Candidate added.", "success");
    closeModal("candidateModal");
  } catch (err) { console.error(err); toast(err.message || "Save failed.", "error"); }
  finally { btnLoading(btn, false, "Save Candidate"); }
};

window.removeCandidate = function (id, name) {
  confirmAction("Delete candidate?", `Remove "${name}" permanently? Existing votes are unaffected.`, async () => {
    try { await D.deleteCandidate(id); toast("Candidate deleted.", "success"); }
    catch (err) { toast(err.message || "Delete failed.", "error"); }
  });
};

// ---------------------------------------------------------------- students
function renderStudents() {
  const term = stuTerm.toLowerCase();
  const rows = students.filter((s) => {
    const name = D.fullName(s).toLowerCase();
    return !term || name.includes(term) || (s.code || "").toLowerCase().includes(term) ||
      (s.gradeLevel || "").toLowerCase().includes(term) || (s.section || "").toLowerCase().includes(term);
  });
  $("#stuBody").innerHTML = rows.length ? rows.slice(0, 500).map((s) => `
    <tr>
      <td><code>${escapeHtml(s.code || "-")}</code></td>
      <td>${escapeHtml(D.fullName(s))}</td>
      <td>${escapeHtml(s.gradeLevel || "-")}</td>
      <td>${escapeHtml(s.section || "-")}</td>
      <td>${s.voteStatus === "Casted" ? '<span class="badge badge-green">Casted</span>' : '<span class="badge badge-amber">Available</span>'}</td>
      <td><div class="actions">
        <button class="btn btn-ghost btn-sm" onclick="openStudent('${s.id}')"><i class="ph ph-pencil-simple"></i></button>
        <button class="btn btn-danger btn-sm" onclick="removeStudent('${s.id}','${escapeHtml(D.fullName(s)).replace(/'/g, "\\'")}')"><i class="ph ph-trash"></i></button>
      </div></td>
    </tr>`).join("") : `<tr><td colspan="6"><div class="empty"><i class="ph ph-student"></i>No students found.</div></td></tr>`;
}

window.generateCode = function () {
  const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
  let code = "";
  for (let i = 0; i < 8; i++) code += chars[Math.floor(Math.random() * chars.length)];
  $("#stuCode").value = code;
};

window.openStudent = function (id) {
  if (id) {
    const s = students.find((x) => x.id === id);
    if (!s) return;
    $("#stuModalTitle").textContent = "Edit Student";
    $("#stuId").value = s.id;
    $("#stuFirst").value = s.firstName || "";
    $("#stuMiddle").value = s.middleName || "";
    $("#stuLast").value = s.lastName || "";
    $("#stuGender").value = s.gender || "";
    $("#stuGrade").value = s.gradeLevel || "";
    $("#stuSection").value = s.section || "";
    $("#stuCode").value = s.code || "";
  } else {
    $("#stuModalTitle").textContent = "Add Student";
    $("#stuForm") && $("#stuForm").reset();
    ["stuFirst", "stuMiddle", "stuLast", "stuSection", "stuCode"].forEach((k) => ($("#" + k).value = ""));
    $("#stuGender").value = ""; $("#stuGrade").value = ""; $("#stuId").value = "";
    generateCode();
  }
  openModal("studentModal");
};

window.saveStudent = async function () {
  const id = $("#stuId").value;
  const payload = {
    firstName: $("#stuFirst").value.trim(),
    middleName: $("#stuMiddle").value.trim(),
    lastName: $("#stuLast").value.trim(),
    gender: $("#stuGender").value,
    gradeLevel: $("#stuGrade").value,
    section: $("#stuSection").value.trim(),
    code: $("#stuCode").value.trim()
  };
  if (!payload.firstName || !payload.lastName) { toast("First and last name are required.", "error"); return; }
  if (!payload.gradeLevel) { toast("Grade level is required.", "error"); return; }
  if (!payload.code) { toast("Verification code is required.", "error"); return; }
  const btn = $("#stuSave");
  btnLoading(btn, true);
  try {
    if (id) await D.updateStudent(id, payload);
    else await D.addStudent(payload);
    toast(id ? "Student updated." : "Student added.", "success");
    closeModal("studentModal");
  } catch (err) { console.error(err); toast(err.message || "Save failed.", "error"); }
  finally { btnLoading(btn, false, "Save Student"); }
};

window.removeStudent = function (id, name) {
  confirmAction("Delete student?", `Remove "${name}" and their verification code? This does not delete their vote.`, async () => {
    try { await D.deleteStudent(id); toast("Student deleted.", "success"); }
    catch (err) { toast(err.message || "Delete failed.", "error"); }
  });
};

window.importBulk = async function () {
  const raw = $("#bulkText").value.trim();
  const alertEl = $("#bulkAlert");
  const btn = $("#bulkSave");
  if (!raw) { toast("Paste at least one student.", "error"); return; }
  const lines = raw.split(/\r?\n/).map((l) => l.trim()).filter(Boolean);
  btnLoading(btn, true);
  let ok = 0, fail = 0;
  for (const line of lines) {
    const parts = line.split(",").map((p) => p.trim());
    const [firstName, middleName, lastName, gender, gradeLevel, section, code] = parts;
    if (!firstName || !lastName || !gradeLevel) { fail++; continue; }
    try {
      await D.addStudent({
        firstName, middleName: middleName || "", lastName, gender: gender || "",
        gradeLevel, section: section || "", code: code || genCode()
      });
      ok++;
    } catch (_) { fail++; }
  }
  btnLoading(btn, false, "Import Students");
  alertEl.className = "alert alert-success show";
  alertEl.innerHTML = `<i class="ph ph-check-circle"></i><span>Imported ${ok} student(s)${fail ? `, ${fail} skipped` : ""}.</span>`;
  toast(`Imported ${ok} students.`, "success");
  setTimeout(() => { closeModal("bulkModal"); $("#bulkText").value = ""; alertEl.className = "alert"; }, 1400);
};

function genCode() {
  const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
  let c = ""; for (let i = 0; i < 8; i++) c += chars[Math.floor(Math.random() * chars.length)];
  return c;
}

// ---------------------------------------------------------------- accounts
function watchStaff() {
  onSnapshot(collection(db, "staff"), (snap) => {
    staffList = snap.docs.map((d) => ({ id: d.id, ...d.data() }))
      .sort((a, b) => (a.status || "").localeCompare(b.status || "") || (a.name || "").localeCompare(b.name || ""));
    renderAccounts();
  });
}

function renderAccounts() {
  const term = accTerm.toLowerCase();
  const rows = staffList.filter((s) => !term || (s.name || "").toLowerCase().includes(term) || (s.username || "").toLowerCase().includes(term));
  $("#accBody").innerHTML = rows.length ? rows.map((s) => `
    <tr>
      <td>${escapeHtml(s.name)}</td>
      <td><code>${escapeHtml(s.username)}</code></td>
      <td><span class="badge badge-blue">${escapeHtml(s.role)}</span></td>
      <td>${statusBadge(s.status)}</td>
      <td>${fmtDate(s.createdAt)}</td>
      <td><div class="actions">
        ${s.status !== "Approved" ? `<button class="btn btn-success btn-sm" onclick="setStaffStatus('${s.id}','Approved')"><i class="ph ph-check"></i></button>` : ""}
        ${s.status === "Approved" ? `<button class="btn btn-ghost btn-sm" onclick="setStaffStatus('${s.id}','Deactivated')"><i class="ph ph-prohibit"></i></button>` : ""}
        ${s.id !== profile.id ? `<button class="btn btn-danger btn-sm" onclick="removeStaff('${s.id}','${escapeHtml(s.name || s.username)}')"><i class="ph ph-trash"></i></button>` : ""}
      </div></td>
    </tr>`).join("") : `<tr><td colspan="6"><div class="empty"><i class="ph ph-users-three"></i>No staff accounts.</div></td></tr>`;
}

function statusBadge(status) {
  if (status === "Approved") return '<span class="badge badge-green">Approved</span>';
  if (status === "Pending") return '<span class="badge badge-amber">Pending</span>';
  return '<span class="badge badge-red">Deactivated</span>';
}

window.setStaffStatus = async function (uid, status) {
  try { await D.updateStaffStatus(uid, status); toast(`Account ${status.toLowerCase()}.`, "success"); }
  catch (err) { toast(err.message || "Update failed.", "error"); }
};
window.removeStaff = function (uid, name) {
  confirmAction("Delete staff account?", `Remove ${name}'s profile? Their login will stop working.`, async () => {
    try { await D.deleteStaffProfile(uid); toast("Account removed.", "success"); }
    catch (err) { toast(err.message || "Delete failed.", "error"); }
  });
};

// ---------------------------------------------------------------- settings
async function fillLookupOptions() {
  try {
    const parties = await D.listParties();
    $("#partyList").innerHTML = parties.map((p) => `<option value="${escapeHtml(p.name)}"></option>`).join("");
  } catch (_) {}
}

function fillSettingsForm(s) {
  $("#setSchool").value = s.schoolName;
  $("#setElection").value = s.electionName;
  $("#setYear").value = s.academicYear;
  $("#setRepsRule").value = s.repsRule;
  $("#setVoting").value = String(s.votingOpen);
  $("#setResultsPublic").value = String(s.resultsPublic);
}

window.saveSettingsForm = async function () {
  const patch = {
    schoolName: $("#setSchool").value.trim(),
    electionName: $("#setElection").value.trim(),
    academicYear: $("#setYear").value.trim(),
    repsRule: $("#setRepsRule").value,
    votingOpen: $("#setVoting").value === "true",
    resultsPublic: $("#setResultsPublic").value === "true"
  };
  try { await D.saveSettings(patch); settings = { ...settings, ...patch }; toast("Settings saved.", "success"); renderOverview(); }
  catch (err) { toast(err.message || "Save failed.", "error"); }
};

let lookupKind = null;
window.manageLookup = async function (kind) {
  lookupKind = kind;
  $("#lookupTitle").textContent = `Manage ${kind[0].toUpperCase() + kind.slice(1)}`;
  await renderLookup();
  openModal("lookupModal");
};
async function renderLookup() {
  const listFn = { parties: D.listParties, years: D.listYears, sections: D.listSections }[lookupKind];
  const items = await listFn();
  $("#lookupList").innerHTML = items.length ? items.map((i) => `
    <div class="lookup-item">
      <span>${escapeHtml(i.name)}${i.gradeLevel ? ` <span class="muted">(${escapeHtml(i.gradeLevel)})</span>` : ""}</span>
      <button onclick="delLookupEntry('${i.id}')"><i class="ph ph-trash"></i></button>
    </div>`).join("") : '<p class="muted" style="font-size:13px">No entries yet.</p>';
}
window.addLookupEntry = async function () {
  const name = $("#lookupInput").value.trim();
  if (!name) return;
  const payload = { name };
  if (lookupKind === "sections") payload.gradeLevel = "";
  try { await D.addLookup(lookupKind, payload); $("#lookupInput").value = ""; await renderLookup(); toast("Added.", "success"); }
  catch (err) { toast(err.message || "Failed.", "error"); }
};
window.delLookupEntry = async function (id) {
  try { await D.deleteLookup(lookupKind, id); await renderLookup(); toast("Removed.", "success"); }
  catch (err) { toast(err.message || "Failed.", "error"); }
};

window.archiveNow = function () {
  confirmAction("Archive current election?", "This saves a snapshot of the current results and clears nothing — voters and votes remain. Use it to keep a historical record.", async () => {
    try {
      const stats = await D.getOverviewStats();
      const votes = await D.listVotes();
      const results = D.computeResults(candidates, votes);
      await D.archiveElection({
        electionName: settings.electionName, academicYear: settings.academicYear,
        stats, results, archivedBy: profile.name || profile.username
      });
      toast("Election archived.", "success");
      renderArchive();
    } catch (err) { toast(err.message || "Archive failed.", "error"); }
  });
};

async function renderArchive() {
  try {
    const items = await D.listArchive();
    $("#archiveList").innerHTML = items.length ? items.map((a) => `
      <div class="card" style="margin-bottom:14px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap">
          <div>
            <div class="section-title" style="margin:0">${escapeHtml(a.electionName)} ${escapeHtml(a.academicYear)}</div>
            <span class="muted" style="font-size:12.5px">Archived by ${escapeHtml(a.archivedBy)} &middot; ${fmtDate(a.archivedAt)} &middot; ${a.stats ? a.stats.casted : 0} ballots of ${a.stats ? a.stats.registered : 0} voters</span>
          </div>
          <button class="btn btn-danger btn-sm" onclick="removeArchive('${a.id}')"><i class="ph ph-trash"></i> Delete</button>
        </div>
      </div>`).join("") : `<div class="empty card"><i class="ph ph-archive"></i>No archived elections yet.</div>`;
  } catch (err) { $("#archiveList").innerHTML = `<div class="empty card">${escapeHtml(err.message || "Could not load archive.")}</div>`; }
}
window.removeArchive = function (id) {
  confirmAction("Delete archive?", "This permanently removes the saved snapshot.", async () => {
    try { await D.deleteArchive(id); toast("Archive deleted.", "success"); renderArchive(); }
    catch (err) { toast(err.message || "Delete failed.", "error"); }
  });
};

// ---------------------------------------------------------------- confirm
let confirmHandler = null;
function confirmAction(title, msg, onYes) {
  $("#confirmTitle").textContent = title;
  $("#confirmMsg").textContent = msg;
  confirmHandler = onYes;
  openModal("confirmModal");
}
$("#confirmYes").addEventListener("click", async () => {
  const fn = confirmHandler; confirmHandler = null; closeModal("confirmModal");
  if (fn) await fn();
});

// ---------------------------------------------------------------- search
$("#candSearch").addEventListener("input", (e) => { candTerm = e.target.value.trim(); renderCandidates(); });
$("#stuSearch").addEventListener("input", (e) => { stuTerm = e.target.value.trim(); renderStudents(); });
$("#accSearch").addEventListener("input", (e) => { accTerm = e.target.value.trim(); renderAccounts(); });

// Close sidebar on mobile after nav click handled in switchView.
main().catch((err) => {
  console.error(err);
  $("#loading").innerHTML = `<div class="empty"><i class="ph ph-warning-circle"></i>${escapeHtml(err.message || "Failed to load dashboard.")}</div>`;
});
