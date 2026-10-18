import { auth, signOut, signInAnonymously } from "./firebase.js";
import {
  getSettings, listCandidates, getPositions, positionAppliesToGrade,
  submitVotes, fullName, REP_POSITIONS
} from "./data.js";
import { $, escapeHtml, toast, openModal, btnLoading } from "./ui.js";

let student = null;
let settings = null;
let positionsForBallot = [];
let candidatesByPosition = {};
let slotsByPosition = {};
const selections = {}; // position -> [candidateId]

function slotCount(position) { return slotsByPosition[position] || (REP_POSITIONS.includes(position) ? 2 : 1); }

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

async function init() {
  student = readSession();
  if (!student || !student.id) { location.replace("verify.html"); return; }

  settings = await getSettings();
  $("#schoolName").textContent = settings.schoolName;
  $("#electionName").textContent = `${settings.electionName} ${settings.academicYear}`;
  $("#stuName").textContent = fullName(student, "first");
  $("#stuMeta").textContent = `${student.gender || ""} | ${student.gradeLevel} - ${student.section || ""}`.replace(/^\s*\|\s*/, "");

  if (settings.votingOpen === false) {
    document.body.innerHTML = `<div class="center-screen">
      <i class="ph ph-lock-key" style="font-size:56px;color:var(--secondary)"></i>
      <h1 style="color:#fff">Voting is currently closed</h1>
      <p class="muted">Please wait for the election committee to open the polls.</p>
      <a class="btn btn-ghost" href="index.html">Return Home</a></div>`;
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
  $("#loading").style.display = "none";
  $("#app").style.display = "";
}

function render() {
  const host = $("#positions");
  host.innerHTML = "";
  let rendered = 0;
  for (const position of positionsForBallot) {
    const cands = candidatesByPosition[position] || [];
    if (!cands.length) continue; // skip positions with no candidates
    rendered++;
    selections[position] = selections[position] || [];
    const slots = slotCount(position);
    const section = document.createElement("section");
    section.className = "pos-card card";
    section.dataset.position = position;
    section.dataset.slots = slots;
    section.innerHTML = `
      <div class="pos-head">
        <h3>${escapeHtml(labelFor(position))}</h3>
        <span class="count">0/${slots}</span>
      </div>
      <div class="cand-grid">
        ${cands.map((c) => `
          <label class="cand" data-id="${c.id}">
            <input type="${slots > 1 ? "checkbox" : "radio"}" name="pos_${escapeHtml(position)}" value="${c.id}" />
            <span class="check"><i class="ph ph-check"></i></span>
            <img src="${c.photo || "assets/pictures/user_candidate.jpg"}" alt="${escapeHtml(c.name)}" onerror="this.src='assets/pictures/user_candidate.jpg'" />
            <span class="cname">${escapeHtml(c.name)}</span>
            <span class="cparty">${escapeHtml(c.party || "")}</span>
          </label>`).join("")}
      </div>`;
    host.appendChild(section);
  }

  if (!rendered) {
    host.innerHTML = `<div class="empty card"><i class="ph ph-ballot"></i>No candidates are registered for your grade level yet. Please inform the election committee.</div>`;
  }

  host.querySelectorAll("input").forEach((input) => input.addEventListener("change", onChange));
  updateSummary();
}

function onChange(e) {
  const input = e.target;
  const card = input.closest(".pos-card");
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
  updateSummary();
}

function syncCard(card) {
  const position = card.dataset.position;
  const slots = Number(card.dataset.slots);
  const picked = selections[position] || [];
  card.querySelectorAll(".cand").forEach((el) => {
    const on = picked.includes(el.dataset.id);
    el.classList.toggle("selected", on);
    const input = el.querySelector("input");
    input.checked = on;
  });
  const count = card.querySelector(".count");
  count.textContent = `${picked.length}/${slots}`;
  count.classList.toggle("done", picked.length === slots);
}

function updateSummary() {
  const needed = positionsForBallot.filter((p) => (candidatesByPosition[p] || []).length);
  const done = needed.filter((p) => (selections[p] || []).length > 0);
  $("#summary").innerHTML = `<b>${done.length}</b> of <b>${needed.length}</b> positions selected`;
}

window.openReview = function () {
  const list = $("#reviewList");
  list.innerHTML = "";
  const needed = positionsForBallot.filter((p) => (candidatesByPosition[p] || []).length);
  for (const position of needed) {
    const picked = (selections[position] || []).map((id) => {
      const c = (candidatesByPosition[position] || []).find((x) => x.id === id);
      return c ? c.name : "";
    }).filter(Boolean);
    const missing = picked.length === 0;
    list.insertAdjacentHTML("beforeend", `
      <div class="review-row ${missing ? "missing" : ""}">
        <span class="rpos">${escapeHtml(labelFor(position))}</span>
        <span class="rname">${missing ? "No selection" : escapeHtml(picked.join(", "))}</span>
      </div>`);
  }
  openModal("reviewModal");
};

window.confirmSubmit = async function () {
  const btn = $("#confirmBtn");
  // Guard against double-submits and re-check election state.
  if (btn.disabled) return;
  btnLoading(btn, true);
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
    btnLoading(btn, false, '<i class="ph ph-check-circle"></i> Confirm &amp; Submit');
  }
};

function showThanks() {
  sessionStorage.removeItem("fresys_student");
  document.body.innerHTML = `
    <div class="bggg-backdrop"></div>
    <div class="center-screen">
      <div class="thanks">
        <div class="tick"><i class="ph ph-check"></i></div>
        <h1>Vote Submitted!</h1>
        <p>Thank you for voting, <b style="color:#fff">${escapeHtml(fullName(student, "first"))}</b>. Your ballot has been recorded securely and cannot be changed.</p>
        <div style="margin-top:26px"><a class="btn btn-primary" style="width:auto" href="index.html"><i class="ph ph-house"></i> Return Home</a></div>
      </div>
    </div>`;
}

init();
