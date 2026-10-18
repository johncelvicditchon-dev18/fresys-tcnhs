import { auth, onAuthStateChanged, onSnapshot, collection, db } from "./firebase.js";
import {
  getSettings, getStaffProfile, computeResults, computeWinners, computeGradeWinners,
  getOverviewStats, getPublicStats, REP_POSITIONS, GRADES
} from "./data.js";
import { $, escapeHtml, toast } from "./ui.js";

let allResults = [];
let viewMode = "overall";
let searchTerm = "";
let isStaff = false;

function currentUserPromise() {
  return new Promise((resolve) => {
    const stop = onAuthStateChanged(auth, (u) => { stop(); resolve(u); });
  });
}

async function staffStatus() {
  const user = await currentUserPromise();
  if (user && !user.isAnonymous) {
    const profile = await getStaffProfile(user.uid).catch(() => null);
    if (profile && profile.status === "Approved") return true;
  }
  return false;
}

async function init() {
  const settings = await getSettings();
  $("#title").textContent = `${settings.electionName} ${settings.academicYear}`;
  $("#subtitle").textContent = settings.schoolName || "Live tally";

  isStaff = await staffStatus();
  if (!settings.resultsPublic && !isStaff) {
    sessionStorage.setItem("fresys_after_login", "results.html");
    location.replace("login.html");
    return;
  }
  // Staff can jump back to the dashboard; the public cannot.
  $("#backLink").style.display = isStaff ? "" : "none";

  let candidates = [], votes = [];
  const rerender = () => { allResults = computeResults(candidates, votes); render(); };

  onSnapshot(collection(db, "candidates"), (snap) => {
    candidates = snap.docs.map((d) => ({ id: d.id, ...d.data() }));
    rerender();
  });
  onSnapshot(collection(db, "votes"), (snap) => {
    votes = snap.docs.map((d) => ({ id: d.id, ...d.data() }));
    rerender();
  });

  loadStats();
  $("#loading").style.display = "none";
  $("#app").style.display = "";
}

async function loadStats() {
  const host = $("#stats");
  let casted = null, registered = null, turnout = null;
  try {
    const s = await getOverviewStats();
    registered = s.registered; casted = s.casted; turnout = s.turnout;
  } catch (_) {
    try { casted = (await getPublicStats()).casted; } catch (_) { casted = null; }
  }
  const cards = [];
  if (registered != null) cards.push(`<div class="stat"><div class="ico"><i class="ph ph-users"></i></div><div class="val">${registered}</div><div class="lbl2">Registered Voters</div></div>`);
  cards.push(`<div class="stat"><div class="ico"><i class="ph ph-check-square"></i></div><div class="val">${casted == null ? "–" : casted}</div><div class="lbl2">Ballots Cast</div></div>`);
  if (turnout != null) cards.push(`<div class="stat"><div class="ico"><i class="ph ph-percent"></i></div><div class="val">${turnout}%</div><div class="lbl2">Voter Turnout</div></div>`);
  host.innerHTML = cards.join("");
}

function barWidth(votes, max) { return max > 0 ? Math.max(2, Math.round((votes / max) * 100)) : 0; }

function render() { renderWinners(); renderTally(); }

function renderWinners() {
  const host = $("#winners");
  const winners = computeWinners(allResults);
  if (!winners.length) { host.innerHTML = `<div class="empty card" style="grid-column:1/-1"><i class="ph ph-trophy"></i>No votes have been counted yet.</div>`; return; }
  host.innerHTML = winners.map((w) => `
    <div class="card">
      <div class="winner-card">
        <img src="${w.photo || "assets/pictures/user_candidate.jpg"}" onerror="this.src='assets/pictures/user_candidate.jpg'" alt="">
        <div style="min-width:0">
          <span class="lbl" style="margin:0">${escapeHtml(w.position)}</span>
          <b>${escapeHtml(w.name)}</b>
          <span>${escapeHtml(w.party || "")} &middot; ${w.total} vote${w.total === 1 ? "" : "s"}</span>
        </div>
      </div>
    </div>`).join("");
}

function renderGradeWinners() {
  const host = $("#gradeWinners");
  if (!host) return;
  const show = viewMode === "grade";
  $("#gradeWinnersTitle").style.display = show ? "" : "none";
  host.style.display = show ? "" : "none";
  if (!show) return;
  const rows = computeGradeWinners(allResults)
    .filter((r) => (searchTerm ? (r.name || "").toLowerCase().includes(searchTerm.toLowerCase()) || (r.position || "").toLowerCase().includes(searchTerm.toLowerCase()) : true));
  host.innerHTML = rows.length ? rows.map((w) => `
    <div class="card">
      <div class="winner-card">
        <img src="${w.photo || "assets/pictures/user_candidate.jpg"}" onerror="this.src='assets/pictures/user_candidate.jpg'" alt="">
        <div style="min-width:0">
          <span class="lbl" style="margin:0">${escapeHtml(w.grade)} &middot; ${escapeHtml(w.position)}</span>
          <b>${escapeHtml(w.name)}</b>
          <span>${escapeHtml(w.party || "")} &middot; ${w.total} vote${w.total === 1 ? "" : "s"} in ${escapeHtml(w.grade)}</span>
        </div>
      </div>
    </div>`).join("") : `<div class="empty card" style="grid-column:1/-1"><i class="ph ph-trophy"></i>No grade-level votes yet.</div>`;
}

function renderTally() {
  const host = $("#tally");
  const term = searchTerm.toLowerCase();
  const groups = allResults
    .map((g) => ({
      position: g.position,
      candidates: g.candidates.filter((c) =>
        !term || g.position.toLowerCase().includes(term) || c.name.toLowerCase().includes(term))
    }))
    .filter((g) => g.candidates.length);

  if (!groups.length) { host.innerHTML = `<div class="empty card"><i class="ph ph-magnifying-glass"></i>No matching results.</div>`; return; }

  host.innerHTML = groups.map((g) => {
    const max = Math.max(1, ...g.candidates.map((c) => c.total));
    const rows = g.candidates.map((c, i) => {
      const gradeLine = viewMode === "grade"
        ? `<div class="grade-chip" style="margin-top:6px;flex-wrap:wrap">${GRADES.map((gr) => `<span class="badge badge-blue">${gr.replace("Grade ", "G")}: ${c.byGrade[gr] || 0}</span>`).join("")}</div>`
        : "";
      return `
        <div class="rank-row">
          <div class="rk ${i === 0 && c.total > 0 ? "top" : ""}">${i + 1}</div>
          <div style="display:flex;gap:12px;align-items:center;min-width:0">
            <img class="rank-photo" src="${c.photo || "assets/pictures/user_candidate.jpg"}" onerror="this.src='assets/pictures/user_candidate.jpg'" alt="">
            <div class="rank-main">
              <div class="nm">${escapeHtml(c.name)}</div>
              <div class="pt">${escapeHtml(c.party || "")}</div>
              <div class="progress bar"><span style="width:${barWidth(c.total, max)}%"></span></div>
              ${gradeLine}
            </div>
          </div>
          <div class="vote"><b>${c.total}</b><span>votes</span></div>
        </div>`;
    }).join("");
    return `<div class="card" style="margin-bottom:16px"><div class="section-title">${escapeHtml(g.position)}${REP_POSITIONS.includes(g.position) ? ' <span class="badge badge-amber">2 slots</span>' : ""}</div>${rows}</div>`;
  }).join("");
  renderGradeWinners();
}

window.exportCsv = function () {
  const lines = [["Position", "Rank", "Candidate", "Party", ...GRADES, "Total"].join(",")];
  for (const g of allResults) {
    g.candidates.forEach((c, i) => {
      lines.push([
        g.position, i + 1, c.name, c.party || "",
        ...GRADES.map((gr) => c.byGrade[gr] || 0), c.total
      ].map((v) => `"${String(v).replace(/"/g, '""')}"`).join(","));
    });
  }
  if (lines.length === 1) { toast("No results to export yet.", "error"); return; }
  const blob = new Blob([lines.join("\r\n")], { type: "text/csv;charset=utf-8" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = `fresys-results-${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
  URL.revokeObjectURL(a.href);
};

$("#tabOverall").addEventListener("click", () => {
  viewMode = "overall";
  $("#tabOverall").classList.add("active"); $("#tabGrade").classList.remove("active");
  renderTally();
});
$("#tabGrade").addEventListener("click", () => {
  viewMode = "grade";
  $("#tabGrade").classList.add("active"); $("#tabOverall").classList.remove("active");
  renderTally();
});
$("#search").addEventListener("input", (e) => { searchTerm = e.target.value.trim(); renderTally(); });

init();
