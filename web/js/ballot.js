import { auth, signOut, signInAnonymously } from "./firebase.js";
import { getSettings, listCandidates, ballotPositionsForGrade, submitVotes, fullName } from "./data.js";
import { $, escapeHtml, toast, btnLoading } from "./ui.js";

let student = null;
let positionsForGrade = [];
const candidatesByPosition = {};
const selections = {}; // position -> [candidateId]

// position -> { container, slots }
const containers = {};

const NAME_CLASS = {
  "President": "president-name",
  "Vice-President": "vice-president-name",
  "Secretary": "secretary",
  "Treasurer": "treasurer",
  "Auditor": "auditor",
  "P.I.O(SHS)": "pio-shs",
  "P.I.O(JHS)": "pio-jhs",
  "Protocol Officer(SHS)": "protocol-shs",
  "Protocol Officer(JHS)": "protocol-jhs"
};
const nameClassFor = (pos) => NAME_CLASS[pos] || `${pos.toLowerCase().replace(/\s+/g, "-")}`;

const REP_REVIEW = {
  "Grade 8 Representative": ["review_grade8_1", "review_grade8_2"],
  "Grade 9 Representative": ["review_grade9_1", "review_grade9_2"],
  "Grade 10 Representative": ["review_grade10_1", "review_grade10_2"],
  "Grade 11 Representative": ["review_grade11_1", "review_grade11_2"],
  "Grade 12 Representative": ["review_grade12_1", "review_grade12_2"]
};
const SINGLE_REVIEW = {
  "President": "review_president",
  "Vice-President": "review_vice_president",
  "Secretary": "review_secretary",
  "Treasurer": "review_treasurer",
  "Auditor": "review_auditor",
  "P.I.O(SHS)": "review_pio_shs",
  "P.I.O(JHS)": "review_pio_jhs",
  "Protocol Officer(SHS)": "review_protocol_shs",
  "Protocol Officer(JHS)": "review_protocol_jhs"
};
const REVIEW_CARD = {
  "P.I.O(SHS)": "cardpio-shs",
  "P.I.O(JHS)": "cardpio-jhs",
  "Protocol Officer(SHS)": "cardprotocol-shs",
  "Protocol Officer(JHS)": "cardprotocol-jhs",
  "Grade 8 Representative": "card-grade8",
  "Grade 9 Representative": "card-grade9",
  "Grade 10 Representative": "card-grade10",
  "Grade 11 Representative": "card-grade11",
  "Grade 12 Representative": "card-grade12"
};

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

  const settings = await getSettings();
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

  positionsForGrade = ballotPositionsForGrade(student.gradeLevel, settings.repsRule);

  const candidates = await listCandidates();
  for (const c of candidates) {
    (candidatesByPosition[c.position] ||= []).push(c);
  }

  renderCandidates();
  applyGradeVisibility();

  $("#loadingNotice").style.display = "none";
  $("#app").style.display = "";
}

function renderCandidates() {
  document.querySelectorAll("[data-position]").forEach((container) => {
    const position = container.dataset.position;
    const slots = Number(container.dataset.slots) || 1;
    containers[position] = { container, slots };
    selections[position] = selections[position] || [];

    const cands = candidatesByPosition[position] || [];
    if (!cands.length) {
      container.innerHTML = `<div class="empty-candidates">No candidates registered.</div>`;
      return;
    }

    const repClass = position.includes("Representative")
      ? `${position.split(" ")[1].toLowerCase()}-input` : "";
    container.innerHTML = cands.map((c) => `
      <label class="candidate-card" data-id="${c.id}">
        <input type="checkbox" data-position="${escapeHtml(position)}"
          data-name="${escapeHtml(c.name)}"
          ${repClass ? `class="${repClass}"` : ""}
          value="${c.id}">
        <img src="${c.photo || "assets/pictures/user_candidate.jpg"}" alt="Candidate Photo" onerror="this.src='assets/pictures/user_candidate.jpg'">
        <span class="${escapeHtml(nameClassFor(position))}">${escapeHtml(c.name)}</span>
        <span class="partyname">${escapeHtml(c.party || "")}</span>
      </label>`).join("");

    container.querySelectorAll("input[type='checkbox']").forEach((cb) => {
      cb.addEventListener("change", () => onSelect(position, cb));
    });
  });
}

function onSelect(position, cb) {
  const info = containers[position];
  if (!info) return;
  const slots = info.slots;
  const inputs = [...info.container.querySelectorAll("input[type='checkbox']")];
  const picked = selections[position];

  if (slots === 1) {
    inputs.forEach((o) => { if (o !== cb) o.checked = false; });
    selections[position] = cb.checked ? [cb.value] : [];
  } else {
    if (cb.checked) {
      const checked = inputs.filter((i) => i.checked);
      if (checked.length > slots) {
        cb.checked = false;
        alert(`You can only select ${slots} ${position.replace(" Representative", "")} representatives.`);
        return;
      }
    }
    selections[position] = inputs.filter((i) => i.checked).map((i) => i.value);
    inputs.forEach((i) => { i.disabled = selections[position].length >= slots && !i.checked; });
  }

  updateCounter(position);
  syncCardHighlight(position);
  updateReview(position);
}

function updateCounter(position) {
  const info = containers[position];
  const count = info.container.closest("section").querySelector(".headbox h2:last-child");
  const picked = (selections[position] || []).length;
  if (count) {
    count.textContent = `${picked}/${info.slots}`;
    count.style.color = picked >= info.slots ? "#00a300" : "#0f172a";
  }
}

function syncCardHighlight(position) {
  const info = containers[position];
  const picked = selections[position] || [];
  info.container.querySelectorAll(".candidate-card").forEach((el) => {
    el.classList.toggle("selected", picked.includes(el.dataset.id));
  });
}

function candidateName(position, id) {
  const c = (candidatesByPosition[position] || []).find((x) => x.id === id);
  return c ? c.name : "";
}

function updateReview(position) {
  const picked = selections[position] || [];
  if (SINGLE_REVIEW[position]) {
    const input = document.getElementById(SINGLE_REVIEW[position]);
    if (input) {
      const val = picked.length ? candidateName(position, picked[0]) : "No Selection";
      input.value = val;
      input.style.color = val === "No Selection" ? "#f87171" : "#ffffff";
    }
  } else if (REP_REVIEW[position]) {
    const [a, b] = REP_REVIEW[position];
    const el1 = document.getElementById(a);
    const el2 = document.getElementById(b);
    if (el1) el1.value = picked[0] ? candidateName(position, picked[0]) : "";
    if (el2) el2.value = picked[1] ? candidateName(position, picked[1]) : "";
  }
}

function applyGradeVisibility() {
  document.querySelectorAll("[data-position]").forEach((container) => {
    const position = container.dataset.position;
    const applies = positionsForGrade.includes(position);
    const section = container.closest("section");
    if (section) section.style.display = applies ? "" : "none";
    const cardId = REVIEW_CARD[position];
    if (cardId) {
      const card = document.getElementById(cardId);
      if (card) card.style.display = applies ? "" : "none";
    }
  });
}

window.review = function () {
  positionsForGrade.forEach(updateReview);
  document.getElementById("reviewModal").style.display = "block";
};

window.closeReview = function () {
  document.getElementById("reviewModal").style.display = "none";
};

window.onclick = function (event) {
  const modal = document.getElementById("reviewModal");
  if (event.target === modal) modal.style.display = "none";
};

window.cancel = function () {
  if (!confirm("Are you sure you want to cancel? Your selections will not be saved.")) return;
  sessionStorage.removeItem("fresys_student");
  signOut(auth).finally(() => location.replace("index.html"));
};

window.ballotLogout = function () {
  sessionStorage.removeItem("fresys_student");
  signOut(auth).finally(() => location.replace("index.html"));
};

function selectionsPayload() {
  const payload = {};
  for (const position of positionsForGrade) {
    const ids = selections[position] || [];
    if (!ids.length) continue;
    payload[position] = ids.map((id) => ({ id, name: candidateName(position, id) }));
  }
  return payload;
}

window.confirm_submit = async function () {
  const btn = document.querySelector("#reviewModal .btn-confirm");
  if (btn && btn.disabled) return;
  if (btn) btnLoading(btn, true);
  try {
    if (!auth.currentUser) await signInAnonymously(auth);
    const fresh = await getSettings();
    if (fresh.votingOpen === false) throw new Error("Voting has been closed by the administrator.");
    await submitVotes(student.id, student.gradeLevel, selectionsPayload());
    sessionStorage.removeItem("fresys_student");
    sessionStorage.setItem("fresys_flash", JSON.stringify({ type: "success", message: "Votes successfully recorded" }));
    location.replace("verify.html");
  } catch (err) {
    console.error(err);
    const msg = (err.code === "permission-denied")
      ? "Your vote could not be saved. You may have already voted, or voting is closed."
      : (err.message || "Something went wrong while submitting your vote.");
    toast(msg, "error", 5000);
    if (btn) btnLoading(btn, false, "CONFIRM & SUBMIT");
  }
};

init();
