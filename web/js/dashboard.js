import { auth, signOut, onSnapshot, collection, db } from "./firebase.js";
import { requireStaff } from "./auth.js";
import * as D from "./data.js";
import {
  $, $$, escapeHtml, toast, openModal, closeModal, btnLoading, fileToDataUrl, fmtDate, showAlert, hideAlert
} from "./ui.js";

let profile = null;
let candidates = [];
let students = [];
let staffList = [];
let positionsList = [];
let partiesList = [];
let yearsList = [];
let sectionsList = [];
let loginLog = [];
let settings = null;
let candTerm = "", stuTerm = "", accTerm = "", logTerm = "";
let editPhoto = null;
let stuPage = 1;
const STU_PAGE = 100;

window.doLogout = () => signOut(auth).finally(() => location.replace("login.html"));

const SECTIONS = [
  "overview-card", "student-card", "student-card-vote-status", "account-card",
  "loginlog-card", "candidate-card", "bulk-import-card", "manage-e-one",
  "manage-e-two", "manage-e-three", "election-history-card"
];

function hideAll() {
  SECTIONS.forEach((id) => { const el = document.getElementById(id); if (el) el.style.display = "none"; });
}
function showSection(id, display = "block") {
  hideAll();
  const el = document.getElementById(id);
  if (el) el.style.display = display;
}
window.showAccounts = () => showSection("account-card");
window.showLoginLog = () => { showSection("loginlog-card"); renderLoginLog(); };

// ---------------------------------------------------------------- bootstrap
async function main() {
  profile = await requireStaff();
  const isAdmin = profile.role === "Admin";
  document.body.classList.toggle("is-admin", isAdmin);
  if ($("#userName")) $("#userName").textContent = profile.name || profile.username;
  if ($("#ddName")) $("#ddName").textContent = profile.name || profile.username;
  if ($("#userRole")) $("#userRole").textContent = profile.role;

  if (isAdmin) { try { await D.ensureSeed(); } catch (_) {} }
  await loadPositions();

  setupNav();
  settings = await D.getSettings();
  fillSettingsForm(settings);
  await refreshLookups();

  watchCandidates();
  if (isAdmin) watchStaff();
  watchOverview();
  renderArchive();

  $("#loading").style.display = "none";
  $("#app").style.display = "";
}

async function loadPositions() {
  try { positionsList = await D.getPositions(); } catch (_) { positionsList = []; }
  if (!positionsList.length) positionsList = D.DEFAULT_POSITIONS.map((p, i) => ({ id: `default-${i}`, ...p }));
}

// ---------------------------------------------------------------- navigation
function setupNav() {
  const map = {
    overview: () => { showSection("overview-card"); setTitle("Overview"); },
    registered: () => { showSection("student-card"); setTitle("Registered Students"); },
    bulkimport: () => { showSection("bulk-import-card"); setTitle("Import Students"); },
    printstudent: () => window.open("print.html?type=students", "_blank"),
    votestudentcasted: () => { showSection("student-card-vote-status"); renderCasted(); setTitle("Vote Casted"); },
    account: () => { showSection("account-card"); setTitle("Accounts"); },
    "candidate-sidebar": () => { showSection("candidate-card"); setTitle("Candidates"); },
    gotoresult: () => { window.location.href = "results.html"; },
    manage_election: () => { hideAll(); ["manage-e-one", "manage-e-two", "manage-e-three"].forEach((id) => { const el = document.getElementById(id); if (el) el.style.display = "grid"; }); renderManage(); setTitle("Manage Election"); },
    "election-history": () => { showSection("election-history-card"); renderArchive(); setTitle("Election History"); }
  };
  Object.entries(map).forEach(([id, fn]) => { const el = document.getElementById(id); if (el) el.addEventListener("click", fn); });

  // Students dropdown toggle
  const studentLi = document.getElementById("student");
  if (studentLi) studentLi.addEventListener("click", () => studentLi.classList.toggle("active"));
  $$(".dropdown li").forEach((item) => item.addEventListener("click", (e) => e.stopPropagation()));

  // Hamburger
  const hamburger = document.querySelector(".hamburger");
  const sidebar = document.querySelector(".sidebar");
  const layout = document.querySelector(".layout");
  if (hamburger) hamburger.addEventListener("click", () => {
    if (window.innerWidth > 768) { sidebar.classList.toggle("collapsed"); layout.classList.toggle("sidebar-collapsed"); }
    else sidebar.classList.toggle("active");
  });

  // Profile dropdown
  const profileEl = document.getElementById("header-profile");
  if (profileEl) {
    profileEl.addEventListener("click", (e) => { e.stopPropagation(); profileEl.classList.toggle("open"); });
    document.addEventListener("click", (e) => { if (!profileEl.contains(e.target)) profileEl.classList.remove("open"); });
  }

  showSection("overview-card");
}

function setTitle(t) { const el = $("#pageTitle"); if (el) el.textContent = t; }

// ---------------------------------------------------------------- overview
function watchOverview() {
  onSnapshot(collection(db, "students"), (snap) => {
    students = snap.docs.map((d) => ({ id: d.id, ...d.data() }));
    renderOverview();
    renderStudents();
    renderCasted();
  });
}

function renderOverview() {
  const registered = students.length;
  const casted = students.filter((s) => s.voteStatus === "Casted").length;
  const turnout = registered ? Math.round((casted / registered) * 1000) / 10 : 0;
  if ($("#statsGrid")) $("#statsGrid").innerHTML = `
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
  if ($("#gradeTurnout")) $("#gradeTurnout").innerHTML = D.GRADES.map((g) => {
    const d = byGrade[g] || { reg: 0, cast: 0 };
    const pct = d.reg ? Math.round((d.cast / d.reg) * 100) : 0;
    return `<div class="grade-row">
      <div class="gl">${g}</div>
      <div class="gbar"><div class="progress"><span style="width:${pct}%"></span></div></div>
      <div class="gv"><b>${d.cast}</b>/${d.reg} &middot; ${pct}%</div>
    </div>`;
  }).join("");

  if ($("#statusPanel")) $("#statusPanel").innerHTML = `
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
  const body = $("#table-body-candidate");
  if (!body) return;
  const term = candTerm.toLowerCase();
  const rows = candidates.filter((c) =>
    !term || (c.name || "").toLowerCase().includes(term) || (c.position || "").toLowerCase().includes(term) || (c.party || "").toLowerCase().includes(term));
  body.innerHTML = rows.length ? rows.map((c) => `
    <tr>
      <td><i class="fa-regular fa-pen-to-square editcandidate" style="cursor:pointer" data-id="${c.id}" onclick="openCandidate(this.dataset.id)"></i></td>
      <td><img class="candidate-row-photo" src="${c.photo || "assets/pictures/user_candidate.jpg"}" onerror="this.src='assets/pictures/user_candidate.jpg'" alt=""></td>
      <td>${escapeHtml(c.name)}</td>
      <td>${escapeHtml(c.position)}</td>
      <td>${escapeHtml(c.party || "-")}</td>
      <td>${escapeHtml(c.year || "-")}</td>
      <td><i class="fa-solid fa-trash-can btn-delete-candidate" style="cursor:pointer" data-id="${c.id}" data-name="${escapeHtml(c.name)}" onclick="removeCandidate(this.dataset.id, this.dataset.name)"></i></td>
    </tr>`).join("") : `<tr><td colspan="7" class="no-data">No data found!</td></tr>`;
}

window.openCandidate = async function (id) {
  editPhoto = null;
  const names = positionsList.length ? positionsList.map((p) => p.name) : D.ALL_POSITIONS;
  const posSel = $("#candPosition");
  posSel.innerHTML = names.map((p) => `<option>${escapeHtml(p)}</option>`).join("");
  $("#candPreview").src = "assets/pictures/user_candidate.jpg";
  if (id) {
    const c = candidates.find((x) => x.id === id);
    if (!c) return;
    $("#candModalTitle").textContent = "Edit Candidate";
    $("#candId").value = c.id;
    $("#candName").value = c.name || "";
    posSel.value = c.position || names[0];
    $("#candParty").value = c.party || "";
    $("#candYear").value = c.year || settings.academicYear;
    if (c.photo) $("#candPreview").src = c.photo;
  } else {
    $("#candModalTitle").textContent = "Add Candidate";
    $("#candId").value = "";
    $("#candName").value = "";
    posSel.value = names[0];
    $("#candParty").value = "";
    $("#candYear").value = settings.academicYear;
  }
  $("#candPhoto").value = "";
  openModal("candidateModal");
};

$("#candPhoto")?.addEventListener("change", async (e) => {
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
function filteredStudents() {
  const term = stuTerm.toLowerCase();
  return students.filter((s) => {
    const name = D.fullName(s).toLowerCase();
    return !term || name.includes(term) || (s.code || "").toLowerCase().includes(term) ||
      (s.gradeLevel || "").toLowerCase().includes(term) || (s.section || "").toLowerCase().includes(term);
  });
}

function statusButton(status) {
  if (status === "Casted") return `<button class="casted"><i class="ph ph-calendar-slash"></i>Casted</button>`;
  return `<button class="available"><i class="ph ph-check-circle"></i>Available</button>`;
}

function renderStudents() {
  const body = $("#table-body-student");
  if (!body) return;
  const rows = filteredStudents();
  const shown = rows.slice(0, stuPage * STU_PAGE);
  body.innerHTML = shown.length ? shown.map((s) => {
    const qr = JSON.stringify({ verification_code: s.code || "", grade_level: s.gradeLevel || "", student_name: `${s.lastName || ""}, ${s.firstName || ""} ${s.middleName || ""}.`.replace(/\s+/g, " ").trim(), section: s.section || "" });
    return `
    <tr>
      <td><i class="fa-regular fa-pen-to-square editicon" style="cursor:pointer" data-id="${s.id}" onclick="openStudent(this.dataset.id)"></i></td>
      <td>${escapeHtml(String(s.id).slice(0, 8))}</td>
      <td>${escapeHtml(s.code || "-")}</td>
      <td><button class="qrbtn" title="Show QR" data-qr="${escapeHtml(qr)}" onclick="openQr(this.dataset.qr)"><i class="ph ph-qr-code"></i></button></td>
      <td>${escapeHtml(s.firstName || "-")}</td>
      <td>${escapeHtml(s.middleName || "")}</td>
      <td>${escapeHtml(s.lastName || "-")}</td>
      <td>${escapeHtml(s.gender || "-")}</td>
      <td>${escapeHtml(s.gradeLevel || "-")}</td>
      <td>${escapeHtml(s.section || "-")}</td>
      <td>${statusButton(s.voteStatus)}</td>
      <td><i class="fa-solid fa-trash-can btn-delete-table" style="cursor:pointer" data-id="${s.id}" data-name="${escapeHtml(D.fullName(s))}" onclick="removeStudent(this.dataset.id, this.dataset.name)"></i></td>
    </tr>`;
  }).join("") : `<tr><td colspan="12" class="no-data">No data found!</td></tr>`;
  if ($("#stuMore")) $("#stuMore").style.display = shown.length < rows.length ? "" : "none";
  if ($("#stuCount")) $("#stuCount").textContent = `Showing ${shown.length} of ${rows.length} student(s)`;
}
window.loadMoreStudents = () => { stuPage += 1; renderStudents(); };

function renderCasted() {
  const body = $("#table-body-casted");
  if (!body) return;
  const rows = students.filter((s) => s.voteStatus === "Casted");
  body.innerHTML = rows.length ? rows.map((s) => `
    <tr>
      <td>${escapeHtml(s.code || "-")}</td>
      <td>${escapeHtml(s.firstName || "-")}</td>
      <td>${escapeHtml(s.middleName || "")}</td>
      <td>${escapeHtml(s.lastName || "-")}</td>
      <td>${escapeHtml(s.gender || "-")}</td>
      <td>${escapeHtml(s.gradeLevel || "-")}</td>
      <td>${escapeHtml(s.section || "-")}</td>
      <td>${statusButton(s.voteStatus)}</td>
    </tr>`).join("") : `<tr><td colspan="8" class="no-data">No data found!</td></tr>`;
}

window.generateCode = function () { $("#stuCode").value = genCode(); };

window.openStudent = function (id) {
  if (id) {
    const s = students.find((x) => x.id === id);
    if (!s) return;
    $("#stuModalTitle").textContent = "Update Student Information";
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
    if (await D.isCodeTaken(payload.code, id || null)) throw new Error("That verification code is already used by another student.");
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

// QR modal
window.openQr = function (json) {
  const host = $("#qr-picture");
  host.innerHTML = "";
  try {
    const data = JSON.parse(json);
    new window.QRCode(host, { text: JSON.stringify(data), width: 240, height: 240 });
  } catch (_) {
    host.innerHTML = `<p class="muted">Could not render QR code.</p>`;
  }
  $("#qr-pop").classList.add("show");
};
window.closeQr = function () { $("#qr-pop").classList.remove("show"); };

// Bulk import
$("#bulkFile")?.addEventListener("change", (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const nameEl = $("#csvFileName"); if (nameEl) nameEl.textContent = file.name;
  const reader = new FileReader();
  reader.onload = () => { $("#bulkText").value = String(reader.result || "").trim(); };
  reader.onerror = () => toast("Could not read that file.", "error");
  reader.readAsText(file);
});

window.importBulk = async function () {
  const raw = $("#bulkText").value.trim();
  const alertEl = $("#bulkAlert");
  const btn = $("#bulkSave");
  if (!raw) { toast("Paste or upload at least one student.", "error"); return; }
  const lines = raw.split(/\r?\n/).map((l) => l.trim()).filter(Boolean);
  btnLoading(btn, true);
  let ok = 0, fail = 0;
  const usedCodes = new Set(students.map((s) => (s.code || "").trim()).filter(Boolean));
  for (let i = 0; i < lines.length; i++) {
    let line = lines[i];
    if (i === 0 && /first.?name/i.test(line)) continue;
    const parts = line.split(",").map((p) => p.trim());
    const [firstName, middleName, lastName, gender, gradeLevel, section] = parts;
    let code = parts[6] || "";
    if (!firstName || !lastName || !gradeLevel) { fail++; continue; }
    if (!code) { do { code = genCode(); } while (usedCodes.has(code)); }
    if (usedCodes.has(code)) { fail++; continue; }
    try {
      if (await D.isCodeTaken(code, null)) { fail++; continue; }
      await D.addStudent({ firstName, middleName: middleName || "", lastName, gender: gender || "", gradeLevel, section: section || "", code });
      usedCodes.add(code);
      ok++;
    } catch (_) { fail++; }
  }
  btnLoading(btn, false, '<i class="ph ph-download-simple"></i> Import Students');
  showAlert(alertEl, `Imported ${ok} student(s)${fail ? `, ${fail} skipped` : ""}.`, fail ? "warning" : "success");
  toast(`Imported ${ok} students.`, "success");
};
window.resetImport = function () {
  if ($("#bulkText")) $("#bulkText").value = "";
  if ($("#bulkFile")) $("#bulkFile").value = "";
  if ($("#csvFileName")) $("#csvFileName").textContent = "";
  hideAlert($("#bulkAlert"));
};
window.downloadTemplate = function () {
  const csv = "FIRST NAME,MIDDLE INITIAL,LAST NAME,GENDER,GRADE LEVEL,SECTION\nJUAN,D,DELA CRUZ,Male,Grade 7,SAMPAGUITA\n";
  const blob = new Blob([csv], { type: "text/csv" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "students-template.csv";
  a.click();
  URL.revokeObjectURL(a.href);
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
  const body = $("#table-body-account");
  if (!body) return;
  const term = accTerm.toLowerCase();
  const rows = staffList.filter((s) => !term || (s.name || "").toLowerCase().includes(term) || (s.username || "").toLowerCase().includes(term));
  body.innerHTML = rows.length ? rows.map((s) => `
    <tr>
      <td><i class="fa-regular fa-pen-to-square editaccount" style="cursor:pointer" data-id="${s.id}" onclick="openStaff(this.dataset.id)"></i></td>
      <td>${escapeHtml(s.name || "-")}</td>
      <td>${escapeHtml(s.username || "-")}</td>
      <td><button class="status status-btn" style="background:${s.status === "Approved" ? "#10b981" : "#ff5252"}" data-id="${s.id}" data-status="${escapeHtml(s.status || "")}" onclick="toggleStaffStatus(this.dataset.id, this.dataset.status)">${escapeHtml(s.status || "-")}</button></td>
      <td>${escapeHtml(s.role || "-")}</td>
      <td>${fmtDate(s.createdAt)}</td>
      <td>${s.id !== profile.id ? `<i class="fa-solid fa-trash-can btn-delete-acc" style="cursor:pointer" data-id="${s.id}" data-name="${escapeHtml(s.name || s.username || "")}" onclick="removeStaff(this.dataset.id, this.dataset.name)"></i>` : ""}</td>
    </tr>`).join("") : `<tr><td colspan="7" class="no-data">No data found!</td></tr>`;
}

window.openStaff = function (id) {
  const s = staffList.find((x) => x.id === id);
  if (!s) return;
  $("#staffId").value = s.id;
  $("#staffName").value = s.name || "";
  $("#staffRole").value = s.role || "Adviser";
  openModal("staffModal");
};
window.saveStaff = async function () {
  const id = $("#staffId").value;
  const btn = $("#staffSave");
  btnLoading(btn, true);
  try {
    await D.updateStaffProfile(id, { name: $("#staffName").value.trim(), role: $("#staffRole").value });
    toast("Account updated.", "success");
    closeModal("staffModal");
  } catch (err) { toast(err.message || "Update failed.", "error"); }
  finally { btnLoading(btn, false, "Save"); }
};

window.setStaffStatus = async function (uid, status) {
  try { await D.updateStaffStatus(uid, status); toast(`Account ${status.toLowerCase()}.`, "success"); }
  catch (err) { toast(err.message || "Update failed.", "error"); }
};
window.toggleStaffStatus = function (uid, current) {
  const next = current === "Approved" ? "Deactivated" : "Approved";
  window.setStaffStatus(uid, next);
};
window.removeStaff = function (uid, name) {
  confirmAction("Delete staff account?", `Remove ${name}'s profile? Their login will stop working.`, async () => {
    try { await D.deleteStaffProfile(uid); toast("Account removed.", "success"); }
    catch (err) { toast(err.message || "Delete failed.", "error"); }
  });
};

// ---------------------------------------------------------------- login log
async function renderLoginLog() {
  if (!profile || profile.role !== "Admin") return;
  const body = $("#table-body-log");
  if (!body) return;
  try { loginLog = await D.listLoginLog(); }
  catch (err) { body.innerHTML = `<tr><td colspan="5" class="no-data">${escapeHtml(err.message || "Could not load log.")}</td></tr>`; return; }
  const term = logTerm.toLowerCase();
  const rows = loginLog.filter((l) =>
    !term || (l.username || "").toLowerCase().includes(term) || (l.name || "").toLowerCase().includes(term) || (l.action || "").toLowerCase().includes(term));
  body.innerHTML = rows.length ? rows.map((l) => `
    <tr>
      <td>${fmtDate(l.at)}</td>
      <td>${escapeHtml(l.username || "-")}</td>
      <td>${escapeHtml(l.name || "-")}</td>
      <td>${escapeHtml(l.role || "-")}</td>
      <td>${escapeHtml(l.action || "login")}</td>
    </tr>`).join("") : `<tr><td colspan="5" class="no-data">No login records yet.</td></tr>`;
}

// ---------------------------------------------------------------- password
window.openPassword = function () {
  $("#newPassword").value = ""; $("#confirmPassword").value = ""; hideAlert($("#pwAlert"));
  openModal("passwordModal");
};
window.savePassword = async function () {
  const pw = $("#newPassword").value, cf = $("#confirmPassword").value;
  const alertEl = $("#pwAlert");
  if (pw.length < 6) { showAlert(alertEl, "Password must be at least 6 characters.", "error"); return; }
  if (pw !== cf) { showAlert(alertEl, "Passwords do not match.", "error"); return; }
  const btn = $("#pwSave");
  btnLoading(btn, true);
  try {
    await D.changeOwnPassword(pw);
    showAlert(alertEl, "Password updated.", "success");
    toast("Password updated.", "success");
    setTimeout(() => closeModal("passwordModal"), 1200);
  } catch (err) { showAlert(alertEl, err.message || "Update failed.", "error"); }
  finally { btnLoading(btn, false, "Update Password"); }
};

// ---------------------------------------------------------------- settings & manage
async function refreshLookups() {
  try {
    partiesList = await D.listParties();
    if ($("#partyList")) $("#partyList").innerHTML = partiesList.map((p) => `<option value="${escapeHtml(p.name)}"></option>`).join("");
  } catch (_) { partiesList = []; }
  try {
    sectionsList = await D.listSections();
    if ($("#sectionList")) $("#sectionList").innerHTML = sectionsList.map((s) => `<option value="${escapeHtml(s.name)}"></option>`).join("");
  } catch (_) { sectionsList = []; }
  renderManage();
}

function fillSettingsForm(s) {
  if ($("#setSchool")) $("#setSchool").value = s.schoolName;
  if ($("#setElection")) $("#setElection").value = s.electionName;
  if ($("#setYear")) $("#setYear").value = s.academicYear;
  if ($("#setRepsRule")) $("#setRepsRule").value = s.repsRule;
  if ($("#setVoting")) $("#setVoting").value = String(s.votingOpen);
  if ($("#setResultsPublic")) $("#setResultsPublic").value = String(s.resultsPublic);
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

window.seedDefaults = function () {
  confirmAction("Initialize default data?", "Creates default settings, positions, and academic year if they do not exist. Existing entries are kept.", async () => {
    try {
      const created = await D.ensureSeed();
      toast(created.length ? `Initialized: ${created.join(", ")}.` : "Everything is already set up.", "success");
      await loadPositions();
      settings = await D.getSettings();
      fillSettingsForm(settings);
      renderOverview();
      renderManage();
    } catch (err) { toast(err.message || "Failed.", "error"); }
  });
};

function rowsHtml(items, kind, cols) {
  if (!items.length) return `<tr><td colspan="${cols}" class="no-data">No data found!</td></tr>`;
  return items.map((i) => {
    const editCell = `<td><i class="fa-regular fa-pen-to-square" style="cursor:pointer" data-kind="${kind}" data-id="${i.id}" data-name="${escapeHtml(i.name)}" onclick="editLookupEntry(this.dataset.kind, this.dataset.id, this.dataset.name)"></i></td>`;
    const delCell = `<td><i class="fa-solid fa-trash-can btn-delete" style="cursor:pointer" data-kind="${kind}" data-id="${i.id}" onclick="delLookupEntry(this.dataset.kind, this.dataset.id)"></i></td>`;
    let mid;
    if (kind === "positions") mid = `<td>${escapeHtml(i.name)}</td><td>${i.slots || 1}</td>`;
    else if (kind === "sections") mid = `<td>${escapeHtml(i.gradeLevel || "-")}</td><td>${escapeHtml(i.name)}</td>`;
    else mid = `<td>${escapeHtml(i.name)}</td>`;
    return `<tr>${editCell}${mid}${delCell}</tr>`;
  }).join("");
}

async function renderManage() {
  try { yearsList = await D.listYears(); } catch (_) { yearsList = []; }
  try { partiesList = await D.listParties(); } catch (_) { partiesList = []; }
  try { sectionsList = await D.listSections(); } catch (_) { sectionsList = []; }
  try { positionsList = await D.listPositions(); } catch (_) { if (!positionsList.length) positionsList = D.DEFAULT_POSITIONS.map((p, i) => ({ id: `default-${i}`, ...p })); }

  if ($("#add-pos-scope") && !$("#add-pos-scope").options.length)
    $("#add-pos-scope").innerHTML = D.POSITION_SCOPES.map((s) => `<option value="${s.value}">${s.label}</option>`).join("");

  if ($("#party-table-body")) $("#party-table-body").innerHTML = rowsHtml(partiesList, "parties", 3);
  if ($("#year-table-body")) $("#year-table-body").innerHTML = rowsHtml(yearsList, "years", 3);
  if ($("#section-table-body")) $("#section-table-body").innerHTML = rowsHtml(sectionsList, "sections", 4);
  if ($("#position-table-body")) $("#position-table-body").innerHTML = rowsHtml(positionsList, "positions", 4);
}

window.addPartyInline = async function () {
  const name = $("#add-party").value.trim();
  if (!name) return;
  try { await D.addLookup("parties", { name }); $("#add-party").value = ""; await refreshLookups(); toast("Party added.", "success"); }
  catch (err) { toast(err.message || "Failed.", "error"); }
};
window.addPositionInline = async function () {
  const name = $("#add-pos-name").value.trim();
  if (!name) return;
  try {
    await D.addPosition({ name, scope: $("#add-pos-scope").value || "all", slots: Number($("#add-pos-slots").value) || 1 });
    $("#add-pos-name").value = "";
    await loadPositions(); await renderManage(); toast("Position added.", "success");
  } catch (err) { toast(err.message || "Failed.", "error"); }
};
window.addYearInline = async function () {
  const name = $("#add-year").value.trim();
  if (!name) return;
  try { await D.addLookup("years", { name }); $("#add-year").value = ""; await refreshLookups(); toast("Year added.", "success"); }
  catch (err) { toast(err.message || "Failed.", "error"); }
};
window.addSectionInline = async function () {
  const name = $("#add-sec-name").value.trim();
  const gradeLevel = $("#add-sec-grade").value;
  if (!name) return;
  try { await D.addLookup("sections", { name, gradeLevel }); $("#add-sec-name").value = ""; await refreshLookups(); toast("Section added.", "success"); }
  catch (err) { toast(err.message || "Failed.", "error"); }
};

window.editLookupEntry = async function (kind, id, oldName) {
  const name = window.prompt("Rename entry:", oldName);
  if (!name || name.trim() === oldName) return;
  try {
    await D.updateLookup(kind, id, { name: name.trim() });
    await refreshLookups();
    if (kind === "positions") { await loadPositions(); await renderManage(); }
    toast("Renamed.", "success");
  } catch (err) { toast(err.message || "Failed.", "error"); }
};

window.delLookupEntry = function (kind, id) {
  confirmAction("Delete entry?", "Remove this entry? Candidates or students referencing it are not changed.", async () => {
    try {
      await D.deleteLookup(kind, id);
      await refreshLookups();
      if (kind === "positions") { await loadPositions(); await renderManage(); }
      toast("Removed.", "success");
    } catch (err) { toast(err.message || "Failed.", "error"); }
  });
};

// ---------------------------------------------------------------- archive
window.archiveNow = function () {
  confirmAction("Archive current election?", "This saves a snapshot of the current results. Voters and votes remain untouched.", async () => {
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

let archiveItems = [];
async function renderArchive() {
  const body = $("#archive-list-body");
  if (!body) return;
  try {
    archiveItems = await D.listArchive();
    body.innerHTML = archiveItems.length ? archiveItems.map((a) => `
      <tr>
        <td>${escapeHtml(a.electionName)}</td>
        <td>${escapeHtml(a.academicYear)}</td>
        <td>${a.stats ? a.stats.registered : 0}</td>
        <td>${a.stats ? a.stats.casted : 0}</td>
        <td>${fmtDate(a.archivedAt)}</td>
        <td><div class="actions">
          <button class="btn btn-ghost btn-sm" onclick="viewArchive('${a.id}')"><i class="ph ph-eye"></i> View</button>
          <button class="btn btn-danger btn-sm" onclick="removeArchive('${a.id}')"><i class="ph ph-trash"></i></button>
        </div></td>
      </tr>`).join("") : `<tr><td colspan="6" class="no-data">No archived elections yet.</td></tr>`;
  } catch (err) { body.innerHTML = `<tr><td colspan="6" class="no-data">${escapeHtml(err.message || "Could not load archive.")}</td></tr>`; }
}

window.viewArchive = function (id) {
  const a = archiveItems.find((x) => x.id === id);
  if (!a) return;
  $("#arcTitle").textContent = `${a.electionName} ${a.academicYear}`;
  $("#arcSub").textContent = `Archived by ${a.archivedBy} · ${fmtDate(a.archivedAt)}`;
  const results = a.results || [];
  const winners = results.length ? D.computeWinners(results) : [];
  $("#arcBody").innerHTML = `
    <div class="grid cols-3" style="margin-bottom:14px">
      <div class="stat"><div class="val">${a.stats ? a.stats.registered : 0}</div><div class="lbl2">Registered</div></div>
      <div class="stat"><div class="val">${a.stats ? a.stats.casted : 0}</div><div class="lbl2">Ballots Cast</div></div>
      <div class="stat"><div class="val">${a.stats ? a.stats.turnout : 0}%</div><div class="lbl2">Turnout</div></div>
    </div>
    <div class="section-title">Winners</div>
    ${winners.length ? winners.map((w) => `<div class="lookup-item"><span>${escapeHtml(w.position)} — <b>${escapeHtml(w.name)}</b></span><span class="muted">${w.total}</span></div>`).join("") : '<p class="muted">No winners recorded.</p>'}`;
  openModal("archiveModal");
};

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
$("#confirmYes")?.addEventListener("click", async () => {
  const fn = confirmHandler; confirmHandler = null; closeModal("confirmModal");
  if (fn) await fn();
});

// ---------------------------------------------------------------- search
$("#search-candidate")?.addEventListener("input", (e) => { candTerm = e.target.value.trim(); renderCandidates(); });
$("#search-student")?.addEventListener("input", (e) => { stuTerm = e.target.value.trim(); stuPage = 1; renderStudents(); });
$("#search-account")?.addEventListener("input", (e) => { accTerm = e.target.value.trim(); renderAccounts(); });
$("#search-log")?.addEventListener("input", (e) => { logTerm = e.target.value.trim(); renderLoginLog(); });

main().catch((err) => {
  console.error(err);
  $("#loading").innerHTML = `<div class="empty"><i class="ph ph-warning-circle"></i>${escapeHtml(err.message || "Failed to load dashboard.")}</div>`;
});
