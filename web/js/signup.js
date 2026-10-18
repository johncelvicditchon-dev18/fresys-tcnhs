import { registerStaff } from "./auth.js";
import { $, showAlert, hideAlert, btnLoading, toast } from "./ui.js";

const alertEl = $("#alert");
const form = $("#signupForm");
const submitBtn = $("#submitBtn");

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  hideAlert(alertEl);
  const name = $("#name").value.trim();
  const role = $("#role").value;
  const username = $("#username").value.trim();
  const password = $("#password").value;

  if (!name || !role || !username || !password) { showAlert(alertEl, "Please complete all fields.", "error"); return; }
  if (password.length < 6) { showAlert(alertEl, "Password must be at least 6 characters.", "error"); return; }
  if (!/^[a-zA-Z0-9._-]{3,}$/.test(username)) { showAlert(alertEl, "Username must be at least 3 characters (letters, numbers, . _ -).", "error"); return; }

  btnLoading(submitBtn, true);
  try {
    await registerStaff({ name, username, password, role });
    showAlert(alertEl, "Account created! Wait for an Admin to approve your access, then log in.", "success");
    toast("Registration submitted for approval.", "success");
    form.reset();
    setTimeout(() => (location.href = "login.html"), 2600);
  } catch (err) {
    console.error(err);
    let msg = "Could not create the account. Please try again.";
    if (err.code === "auth/email-already-in-use") msg = "That username is already taken.";
    else if (err.code === "auth/weak-password") msg = "Password is too weak.";
    else if (err.message) msg = err.message;
    showAlert(alertEl, msg, "error");
  } finally {
    btnLoading(submitBtn, false, '<i class="ph ph-user-plus"></i> Create Account');
  }
});
