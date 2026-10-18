import { auth, signOut, signInAnonymously } from "./firebase.js";
import {
  getSettings, listCandidates, getPositions, positionAppliesToGrade,
  submitVotes, fullName
} from "./data.js";
import { $, escapeHtml, toast, btnLoading } from "./ui.js";

let student = null;
let settings = null;
let positionsForBallot = [];
let candidatesByPosition = {};
let slotsByPosition = {};
const selections = {}; // position -> [candidateId]

function slotCount(position) { return slotsByPosition[position] || 1; }

function labelFor(position) {
  return position
    .replace("P.I.O(SHS)", "P.I.O - SHS")
    .replace("P.I.O(JHS)", "P.I.O - JHS")
    .replace("Protocol Officer(SHS)", "Protocol Officer - SHS")
    .replace("Protocol Officer(JHS)", "Protocol Officer - JHS");
}

function redirectOut() {
  sessionStorage.removeItem("fresys_student");
  signOut(auth).finally(() => location.replace("index.html"));
}
window.ballotLogout = redirectOut;

function readSession() {
  try { return JSON.parse(sessionStorage.getItem("fresys_student") || "null"); }
  catch (_) { return null; }
}

// header profile dropdown toggle
const profile = $("#header-profile");
if (profile) profile.addEventListener("click", (e) => {
  e.stopPropagation();
  profile.classList.toggle("open");
});
document.addEventListener("click", () => { if (profile) profile.classList.remove("open"); });

async function init() {
  student = readSession();
  if (!student || !student.id) { location.replace("verify.html"); return; }

  settings = await getSettings();
  $("#schoolName").textContent = settings.schoolName;
  $("#electionName").textContent = settings.electionName;
  $("#academicYear").textContent = settings.academicYear;
  const displayName = `${student.firstName || ""} ${student.lastName || ""}`.trim() || "Student";
  const meta = `${student.gender || ""} | ${student.gradeLevel || ""} - ${student.section || ""}`.replace(/^\s*\|\s*/, "");
  $("#stuName").textContent = displayName;
  $("#stuMeta").textContent = meta;
  $("#ddName").textContent = displayName;
  $("#ddMeta").textContent = meta;
  $("#ballot_id").value = student.id;
  $("#ballot_name").value = fullName(student, "first");
  $("#ballot_gender").value = student.gender || "";
  $("#ballot_grade").value = student.gradeLevel || "";
  $("#ballot_section").value = student.section || "";

  if (settings.votingOpen === false) {
    document.body.innerHTML = `<div id="loadingNotice" style="margin-top:40vh">
      <i class="ph ph-lock-key" style="font-size:56px;color:#5B4CFF"></i>
      <h1 style="color:#fff;font-family:Inter,sans-serif">Voting is currently closed</h1>
      <p>Please wait for the election committee to open the polls.</p>
      <a href="index.html" style="color:#5B4CFF;font-weight:600">Return Home</a></div>`;
    return;
  }

  const candidates = await listCandidates();
  const positions = await getPositions();
  slotsByPosition = {};
  for (const p of positions) slotsByPosition[p.name] = p.slots || 1;
  positionsForBallot = positions
    .filter((p) => positionAppliesToGrade(p.scope, student.gradeLevel, p.name, settings.repsRule))
    .sort((a, b) => (a.order || 0) - (b.order || 0))
    .map((p) => p.name);

  candidatesByPosition = {};
  for (const c of candidates) {
    if (!positionsForBallot.includes(c.position)) continue;
    (candidatesByPosition[c.position] ||= []).push(c);
  }

  render();
  $("#loadingNotice").style.display = "none";
  $("#app").style.display = "";
}

function render() {
  const host = $("#sections");
  host.innerHTML = "";
  let rendered = 0;
  for (const position of positionsForBallot) {
    const cands = candidatesByPosition[position] || [];
    if (!cands.length) continue; // skip positions with no candidates
    rendered++;
    selections[position] = selections[position] || [];
    const slots = slotCount(position);
    const section = document.createElement("section");
    section.className = "candidate-section" + (rendered === 1 ? " ballot-first" : "");
    section.dataset.position = position;
    section.dataset.slots = slots;
    section.innerHTML = `
      <div class="headbox">
        <h2>${escapeHtml(labelFor(position))}</h2>
        <h2 class="pos-count">0/${slots}</h2>
      </div>
      <div class="candidate-grid">
        ${cands.map((c) => `
          <label class="candidate-card" data-id="${c.id}">
            <input type="${slots > 1 ? "checkbox" : "radio"}" name="pos_${escapeHtml(position)}" value="${c.id}" />
            <img src="${c.photo || "assets/pictures/user_candidate.jpg"}" alt="Candidate Photo" onerror="this.src='assets/pictures/user_candidate.jpg'" />
            <span class="candidate-name">${escapeHtml(c.name)}</span>
            <span class="partyname">${escapeHtml(c.party || "")}</span>
          </label>`).join("")}
      </div>`;
    host.appendChild(section);
  }

  if (!rendered) {
    host.innerHTML = `<div class="candidate-section ballot-first" style="text-align:center;color:rgba(255,255,255,0.6)">No candidates are registered for your grade level yet. Please inform the election committee.</div>`;
  }

  host.querySelectorAll("input").forEach((input) => input.addEventListener("change", onChange));
}

function onChange(e) {
  const input = e.target;
  const card = input.closest(".candidate-section");
  const position = card.dataset.position;
  const slots = Number(card.dataset.slots);
  const picked = selections[position] || [];

  if (slots === 1) {
    selections[position] = input.checked ? [input.value] : [];
  } else {
    if (input.checked) {
      if (picked.length >= slots && !picked.includes(input.value)) {
        input.checked = false;
        toast(`You can only select ${slots} candidate(s) for ${labelFor(position)}.`, "error", 2600);
        return;
      }
      if (!picked.includes(input.value)) picked.push(input.value);
    } else {
      const idx = picked.indexOf(input.value);
      if (idx > -1) picked.splice(idx, 1);
    }
    selections[position] = picked;
  }
  syncCard(card);
}

function syncCard(card) {
  const position = card.dataset.position;
  const slots = Number(card.dataset.slots);
  const picked = selections[position] || [];
  card.querySelectorAll(".candidate-card").forEach((el) => {
    const on = picked.includes(el.dataset.id);
    el.classList.toggle("selected", on);
    const input = el.querySelector("input");
    if (input) input.checked = on;
  });
  const count = card.querySelector(".pos-count");
  if (count) count.textContent = `${picked.length}/${slots}`;
}

window.review = function () {
  const grid = $("#reviewGrid");
  grid.innerHTML = "";
  const needed = positionsForBallot.filter((p) => (candidatesByPosition[p] || []).length);
  for (const position of needed) {
    const picked = (selections[position] || []).map((id) => {
      const c = (candidatesByPosition[position] || []).find((x) => x.id === id);
      return c ? c.name : "";
    }).filter(Boolean);
    const value = picked.length ? picked.join(", ") : "No selection";
    grid.insertAdjacentHTML("beforeend", `
      <div class="review-category card">
        <h3>${escapeHtml(labelFor(position))}</h3>
        <input type="text" value="${escapeHtml(value)}" readonly>
      </div>`);
  }
  $("#reviewModal").style.display = "block";
};

window.closeReview = function () {
  $("#reviewModal").style.display = "none";
};

window.confirm_submit = async function () {
  const btn = document.querySelector("#reviewModal .btn-confirm");
  if (btn && btn.disabled) return;
  if (btn) btnLoading(btn, true);
  try {
    if (!auth.currentUser) await signInAnonymously(auth);
    const fresh = await getSettings();
    if (fresh.votingOpen === false) throw new Error("Voting has been closed by the administrator.");
    await submitVotes(student.id, student.gradeLevel, selections);
    showThanks();
  } catch (err) {
    console.error(err);
    const msg = (err.code === "permission-denied")
      ? "Your vote could not be saved. You may have already voted, or voting is closed."
      : (err.message || "Something went wrong while submitting your vote.");
    toast(msg, "error", 5000);
    if (btn) btnLoading(btn, false, 'CONFIRM & SUBMIT');
  }
};

function showThanks() {
  sessionStorage.removeItem("fresys_student");
  document.body.innerHTML = `
    <div id="loadingNotice" style="margin-top:30vh">
      <div style="width:84px;height:84px;border-radius:50%;background:linear-gradient(135deg,#34d399,#10b981);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:42px;color:#fff">
        <i class="ph ph-check"></i>
      </div>
      <h1 style="color:#fff;font-family:Inter,sans-serif">Vote Submitted!</h1>
      <p>Thank you for voting, <b style="color:#fff">${escapeHtml(fullName(student, "first"))}</b>. Your ballot has been recorded securely and cannot be changed.</p>
      <div style="margin-top:26px">
        <a href="index.html" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:10px;background:linear-gradient(135deg,#2D11E9,#5B4CFF);color:#fff;font-weight:600;text-decoration:none">
          <i class="ph ph-house"></i> Return Home</a>
      </div>
    </div>`;
}

init();
