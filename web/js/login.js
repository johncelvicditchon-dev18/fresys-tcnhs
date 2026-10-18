import { loginStaff } from "./auth.js";
import { logLogin } from "./data.js";
import { $, showAlert, hideAlert, btnLoading } from "./ui.js";

const alertEl = $("#alert");
const form = $("#loginForm");
const submitBtn = $("#submitBtn");

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  hideAlert(alertEl);
  const username = $("#username").value.trim();
  const password = $("#password").value;
  if (!username || !password) { showAlert(alertEl, "Please enter your username and password.", "error"); return; }
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
    showAlert(alertEl, msg, "error");
  } finally {
    btnLoading(submitBtn, false, '<i class="fa-solid fa-right-to-bracket"></i> Login');
  }
});
