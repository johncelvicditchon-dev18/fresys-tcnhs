import { signInAnonymously, auth, onAuthStateChanged } from "./firebase.js";
import { verifyStudent } from "./data.js";
import { $, toast, showAlert, hideAlert, btnLoading } from "./ui.js";

const alertEl = $("#alert");
const form = $("#codeForm");
const submitBtn = $("#submitBtn");

function goBallot(student, code, gradeLevel) {
  sessionStorage.setItem("fresys_student", JSON.stringify({
    id: student.id,
    firstName: student.firstName,
    middleName: student.middleName,
    lastName: student.lastName,
    gradeLevel: student.gradeLevel || gradeLevel,
    section: student.section || "",
    gender: student.gender || "",
    code
  }));
  location.href = "ballot.html";
}

async function ensureAnon() {
  if (auth.currentUser) return;
  await signInAnonymously(auth);
}

async function attemptVerify(code, gradeLevel) {
  hideAlert(alertEl);
  btnLoading(submitBtn, true);
  try {
    await ensureAnon();
    const res = await verifyStudent(code, gradeLevel);
    if (res.ok) {
      toast("Identity verified. Loading your ballot...", "success");
      setTimeout(() => goBallot(res.student, code, gradeLevel), 500);
      return;
    }
    if (res.reason === "alreadycasted") showAlert(alertEl, "This student has already cast a vote.", "warning");
    else if (res.reason === "missing") showAlert(alertEl, "Please enter your verification code.", "error");
    else showAlert(alertEl, "Invalid code or grade level. Please check and try again.", "error");
  } catch (err) {
    console.error(err);
    showAlert(alertEl, err.message || "Verification failed. Please try again.", "error");
  } finally {
    btnLoading(submitBtn, false, '<i class="ph ph-sign-in"></i> Continue to Ballot');
  }
}

form.addEventListener("submit", (e) => {
  e.preventDefault();
  attemptVerify($("#code").value.trim(), $("#gradeLevel").value);
});

// ------------------------------- QR tab ------------------------------------
const maniBtn = $("#maniBtn"), qrBtn = $("#qrBtn");
const codePane = form, qrPane = $("#qrPane");
let scanner = null;

maniBtn.addEventListener("click", () => {
  maniBtn.classList.add("active"); qrBtn.classList.remove("active");
  codePane.style.display = ""; qrPane.style.display = "none";
  stopScanner();
});
qrBtn.addEventListener("click", () => {
  qrBtn.classList.add("active"); maniBtn.classList.remove("active");
  codePane.style.display = "none"; qrPane.style.display = "";
  startScanner();
});

function parseQr(text) {
  try {
    const obj = JSON.parse(text);
    if (obj && obj.code) return { code: String(obj.code).trim(), grade: obj.grade || obj.gradeLevel || "" };
  } catch (_) { /* not JSON */ }
  const parts = String(text).split("|").map((s) => s.trim());
  if (parts.length >= 2 && /^grade/i.test(parts[0])) return { code: parts[1], grade: parts[0] };
  if (parts.length >= 2 && /^grade/i.test(parts[1])) return { code: parts[0], grade: parts[1] };
  return { code: String(text).trim(), grade: $("#gradeLevel").value };
}

function startScanner() {
  if (scanner) return;
  if (!window.Html5Qrcode) { showAlert(alertEl, "QR scanner failed to load. Please enter your code manually.", "error"); return; }
  scanner = new window.Html5Qrcode("reader");
  scanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 240 },
    (decoded) => {
      const { code, grade } = parseQr(decoded);
      stopScanner();
      if (!grade) {
        maniBtn.click();
        showAlert(alertEl, "QR read. Please select your grade level and press Continue.", "info");
        $("#code").value = code;
        return;
      }
      attemptVerify(code, grade);
    },
    () => {}
  ).catch(() => showAlert(alertEl, "Unable to access the camera. Please enter your code manually.", "error"));
}

async function stopScanner() {
  if (!scanner) return;
  try { await scanner.stop(); await scanner.clear(); } catch (_) {}
  scanner = null;
}

// If already verified this session, jump straight in.
onAuthStateChanged(auth, () => {});
