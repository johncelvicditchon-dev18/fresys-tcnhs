// Staff (Admin/Adviser) authentication using Firebase Auth email/password.
// Usernames are mapped to synthetic emails so staff can keep using usernames.
import {
  auth, signInWithEmailAndPassword, createUserWithEmailAndPassword,
  signOut, onAuthStateChanged
} from "./firebase.js";
import { usernameToEmail, createStaffProfile, getStaffProfile } from "./data.js";

export async function registerStaff({ name, username, password, role }) {
  const email = usernameToEmail(username);
  const cred = await createUserWithEmailAndPassword(auth, email, password);
  await createStaffProfile(cred.user.uid, { name, username, role });
  await signOut(auth);
  return cred.user.uid;
}

export async function loginStaff(username, password) {
  const cred = await signInWithEmailAndPassword(auth, usernameToEmail(username), password);
  const profile = await getStaffProfile(cred.user.uid);
  if (!profile) {
    await signOut(auth);
    const err = new Error("No staff profile found for this account.");
    err.code = "no-profile";
    throw err;
  }
  if (profile.status !== "Approved") {
    await signOut(auth);
    const err = new Error(profile.status === "Pending"
      ? "Your account is still pending approval."
      : "Your account has been deactivated.");
    err.code = profile.status.toLowerCase();
    throw err;
  }
  return profile;
}

export function logoutStaff() { return signOut(auth); }

export function currentUser() { return auth.currentUser; }

// Resolve the approved staff profile or redirect to the login page.
export function requireStaff(requiredRole) {
  return new Promise((resolve) => {
    const stop = onAuthStateChanged(auth, async (user) => {
      stop();
      if (!user) { location.replace("login.html"); return; }
      const profile = await getStaffProfile(user.uid);
      if (!profile || profile.status !== "Approved") {
        await signOut(auth);
        location.replace("login.html");
        return;
      }
      if (requiredRole && profile.role !== requiredRole) {
        // Advisers may view results but not manage everything; enforced per view.
        if (!(requiredRole === "Admin" && profile.role === "Adviser")) {
          location.replace("dashboard.html");
          return;
        }
      }
      resolve(profile);
    });
  });
}
