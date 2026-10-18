import { loginStaff } from "./auth.js";
import { logLogin } from "./data.js";
import { $, escapeHtml, btnLoading } from "./ui.js";

const successEl = $("#success");
const errorEl = $("#error");
const form = $("#loginForm");
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
  const username = $("#username").value.trim();
  const password = $("#password").value;
  if (!username || !password) { showMsg("Please enter your username and password.", "error"); return; }
  btnLoading(submitBtn, true);
  try {
    const profile = await loginStaff(username, password);
    logLogin({ username, name: profile?.name, role: profile?.role, action: "login" });
    const target = sessionStorage.getItem("fresys_after_login") || "dashboard.html";
    sessionStorage.removeItem("fresys_after_login");
    location.href = target;
  } catch (err) {
    console.error(err);
    let msg = "Login failed. Please try again.";
    if (err.code === "auth/invalid-credential" || err.code === "auth/wrong-password" || err.code === "auth/user-not-found")
      msg = "Incorrect username or password.";
    else if (err.code === "pending") msg = "Your account is still pending admin approval.";
    else if (err.code === "deactivated") msg = "Your account has been deactivated.";
    else if (err.message) msg = err.message;
    showMsg(msg, "error");
  } finally {
    btnLoading(submitBtn, false, '<i class="fa-solid fa-right-to-bracket"></i> Login');
  }
});
