// Printable documents (replaces the legacy print/*.php scripts):
//   print.html?type=students[&grade=Grade 7][&section=Rizal]
//   print.html?type=codes[&grade=Grade 7]
//   print.html?type=candidates
//   print.html?type=results
import { requireStaff } from "./auth.js";
import {
  getSettings, listStudents, listCandidates, listVotes,
  computeResults, computeWinners, fullName, GRADES, REP_POSITIONS
} from "./data.js";
import { escapeHtml } from "./ui.js";

const params = new URLSearchParams(location.search);
const type = params.get("type") || "students";

let settings, students = [], candidates = [], votes = [];

function head(title) {
  return `<div class="doc-head">
    <h1>${escapeHtml(settings.schoolName || "TANGUB CITY NATIONAL HIGH SCHOOL")}</h1>
    <div class="sub">${escapeHtml(settings.electionName || "")} ${escapeHtml(settings.academicYear || "")}</div>
    <div class="type">${escapeHtml(title)}</div>
  </div>`;
}

function render() {
  const root = document.getElementById("root");
  const grade = document.getElementById("fGrade") ? document.getElementById("fGrade").value : (params.get("grade") || "");
  const section = params.get("section") || "";

  if (type === "students") {
    let rows = students.slice();
    if (grade) rows = rows.filter((s) => s.gradeLevel === grade);
    if (section) rows = rows.filter((s) => (s.section || "") === section);
    rows.sort((a, b) => GRADES.indexOf(a.gradeLevel) - GRADES.indexOf(b.gradeLevel) ||
      (a.section || "").localeCompare(b.section || "") || fullName(a).localeCompare(fullName(b)));
    root.innerHTML = `<div class="doc">${head("Masterlist of Registered Voters")}
      <table><thead><tr><th>#</th><th>Code</th><th>Name</th><th>Gender</th><th>Grade</th><th>Section</th><th>Status</th></tr></thead>
      <tbody>${rows.map((s, i) => `<tr><td>${i + 1}</td><td>${escapeHtml(s.code || "")}</td><td>${escapeHtml(fullName(s))}</td>
        <td>${escapeHtml(s.gender || "")}</td><td>${escapeHtml(s.gradeLevel || "")}</td><td>${escapeHtml(s.section || "")}</td>
        <td>${escapeHtml(s.voteStatus || "")}</td></tr>`).join("")}</tbody></table>
      <p style="font-size:12px;margin-top:10px">Total: ${rows.length}</p></div>`;
  } else if (type === "codes") {
    let rows = students.slice();
    if (grade) rows = rows.filter((s) => s.gradeLevel === grade);
    rows.sort((a, b) => GRADES.indexOf(a.gradeLevel) - GRADES.indexOf(b.gradeLevel) || fullName(a).localeCompare(fullName(b)));
    root.innerHTML = `<div class="doc">${head("Student Verification Code Slips")}
      <div class="slip-grid" id="slips">${rows.map((s) => `
        <div class="slip">
          <canvas data-code="${escapeHtml(s.code || "")}" data-grade="${escapeHtml(s.gradeLevel || "")}"></canvas>
          <div class="info">
            <div><b>${escapeHtml(fullName(s))}</b></div>
            <div>${escapeHtml(s.gradeLevel || "")} - ${escapeHtml(s.section || "")}</div>
            <div class="code">${escapeHtml(s.code || "")}</div>
            <div style="font-size:10.5px;color:#64748b">Scan or type this code to vote.</div>
          </div>
        </div>`).join("")}</div></div>`;
    drawQr(rows);
  } else if (type === "candidates") {
    const rows = candidates.slice().sort((a, b) => (a.position || "").localeCompare(b.position || "") || (a.name || "").localeCompare(b.name || ""));
    root.innerHTML = `<div class="doc">${head("Official List of Candidates")}
      <table><thead><tr><th>#</th><th>Name</th><th>Position</th><th>Party</th><th>Academic Year</th></tr></thead>
      <tbody>${rows.map((c, i) => `<tr><td>${i + 1}</td><td>${escapeHtml(c.name || "")}</td><td>${escapeHtml(c.position || "")}</td>
        <td>${escapeHtml(c.party || "")}</td><td>${escapeHtml(c.year || "")}</td></tr>`).join("")}</tbody></table></div>`;
  } else if (type === "results") {
    const results = computeResults(candidates, votes);
    const winners = computeWinners(results);
    const winnerByPos = {};
    winners.forEach((w) => { (winnerByPos[w.position] ||= []).push(w); });
    root.innerHTML = `<div class="doc">${head("Official Election Results")}
      <table style="margin-bottom:18px"><thead><tr><th>Position</th><th>Winner(s)</th><th>Votes</th></tr></thead>
      <tbody>${results.map((g) => {
        const wins = winnerByPos[g.position] || [];
        return `<tr><td>${escapeHtml(g.position)}</td><td>${wins.length ? wins.map((w) => escapeHtml(w.name)).join(", ") : "-"}</td>
          <td>${wins.length ? wins.map((w) => w.total).join(", ") : "-"}</td></tr>`;
      }).join("")}</tbody></table>
      <table><thead><tr><th>Position</th><th>Rank</th><th>Candidate</th><th>Party</th><th>Total</th>${GRADES.map((g) => `<th>${g.replace("Grade ", "G")}</th>`).join("")}</tr></thead>
      <tbody>${results.flatMap((g) => g.candidates.map((c, i) => `<tr><td>${i === 0 ? escapeHtml(g.position) : ""}</td><td>${i + 1}</td>
        <td>${escapeHtml(c.name)}</td><td>${escapeHtml(c.party || "")}</td><td><b>${c.total}</b></td>
        ${GRADES.map((gr) => `<td>${c.byGrade[gr] || 0}</td>`).join("")}</tr>`)).join("")}</tbody></table></div>`;
  }
}

function drawQr(rows) {
  if (typeof QRCode === "undefined") return;
  document.querySelectorAll("#slips canvas").forEach((canvas) => {
    const code = canvas.dataset.code, grade = canvas.dataset.grade;
    if (!code) return;
    const text = JSON.stringify({ code, grade });
    try { QRCode.toCanvas(canvas, text, { width: 88, margin: 1 }, () => {}); } catch (_) {}
  });
}

async function main() {
  document.getElementById("hint").textContent = "Opens in a new tab. Use your browser's Print to save as PDF.";
  await requireStaff();
  settings = await getSettings();
  document.title = `Print ${type} | FRESYS-TCNHS`;

  const filterWrap = document.getElementById("filterWrap");
  const fGrade = document.getElementById("fGrade");
  if (type === "students" || type === "codes") {
    filterWrap.style.display = "";
    fGrade.innerHTML = `<option value="">All Grades</option>` + GRADES.map((g) => `<option>${g}</option>`).join("");
    fGrade.value = params.get("grade") || "";
    fGrade.addEventListener("change", render);
  }

  if (type === "students" || type === "codes") students = await listStudents();
  if (type === "candidates" || type === "results") candidates = await listCandidates();
  if (type === "results") votes = await listVotes();

  render();
}

main().catch((err) => {
  document.getElementById("root").innerHTML = `<div class="doc"><h1>Cannot open print view</h1><p>${escapeHtml(err.message || "Sign in required.")}</p></div>`;
});
