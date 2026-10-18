import { registerStaff } from "./auth.js";
import { $, escapeHtml, btnLoading } from "./ui.js";

const successEl = $("#success");
const errorEl = $("#error");
const form = $("#signupForm");
const submitBtn = $("#submitBtn");

function hideMsgs() {
  if (successEl) { successEl.className = "success"; successEl.innerHTML = ""; }
  if (errorEl) { errorEl.className = "error"; errorEl.innerHTML = ""; }
}
function showMsg(message, type = "error") {
  hideMsgs();
  const el = type === "success" ? successEl : errorEl;
  if (!el) return;
  el.className = (type === "success" ? "success" : "error") + " show";
  el.innerHTML = `<i class="ph ${type === "success" ? "ph-check-circle" : "ph-warning-circle"}"></i><span>${escapeHtml(message)}</span>`;
}

// password visibility toggle
(function () {
  const pw = $("#password"), show = $("#show"), hide = $("#donotshow");
  if (!pw || !show || !hide) return;
  show.style.display = "block";
  show.addEventListener("click", () => { pw.type = "text"; show.style.display = "none"; hide.style.display = "block"; });
  hide.addEventListener("click", () => { pw.type = "password"; hide.style.display = "none"; show.style.display = "block"; });
})();

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  hideMsgs();
  const name = $("#name").value.trim();
  const role = $("#role").value;
  const username = $("#username").value.trim();
  const password = $("#password").value;

  if (!name || !role || !username || !password) { showMsg("Please complete all fields.", "error"); return; }
  if (password.length < 6) { showMsg("Password must be at least 6 characters.", "error"); return; }
  if (!/^[a-zA-Z0-9._-]{3,}$/.test(username)) { showMsg("Username must be at least 3 characters (letters, numbers, . _ -).", "error"); return; }

  btnLoading(submitBtn, true);
  try {
    await registerStaff({ name, username, password, role });
    showMsg("Account created! Wait for an Admin to approve your access, then log in.", "success");
    form.reset();
    setTimeout(() => (location.href = "login.html"), 2600);
  } catch (err) {
    console.error(err);
    let msg = "Could not create the account. Please try again.";
    if (err.code === "auth/email-already-in-use") msg = "That username is already taken.";
    else if (err.code === "auth/weak-password") msg = "Password is too weak.";
    else if (err.message) msg = err.message;
    showMsg(msg, "error");
  } finally {
    btnLoading(submitBtn, false, '<i class="fa-solid fa-right-to-bracket"></i> Create Account');
  }
});
