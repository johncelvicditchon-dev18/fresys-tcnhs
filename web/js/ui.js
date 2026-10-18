// Small UI helpers shared by every page.
export const $ = (sel, root = document) => root.querySelector(sel);
export const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

export function escapeHtml(str) {
  return String(str == null ? "" : str)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

let toastHost;
export function toast(message, type = "info", timeout = 3800) {
  if (!toastHost) {
    toastHost = document.createElement("div");
    toastHost.className = "toast-host";
    document.body.appendChild(toastHost);
  }
  const el = document.createElement("div");
  el.className = `toast ${type}`;
  const icon = type === "success" ? "ph-check-circle" : type === "error" ? "ph-warning-circle" : "ph-info";
  el.innerHTML = `<i class="ph ${icon}" style="font-size:18px"></i><span>${escapeHtml(message)}</span>`;
  toastHost.appendChild(el);
  setTimeout(() => {
    el.style.opacity = "0";
    el.style.transform = "translateX(30px)";
    el.style.transition = "0.3s";
    setTimeout(() => el.remove(), 320);
  }, timeout);
}

export function showAlert(el, message, type = "error") {
  if (!el) return;
  el.className = `alert alert-${type} show`;
  el.innerHTML = `<i class="ph ${type === "success" ? "ph-check-circle" : type === "warning" ? "ph-warning" : "ph-warning-circle"}"></i><span>${escapeHtml(message)}</span>`;
}
export function hideAlert(el) { if (el) { el.className = "alert"; el.innerHTML = ""; } }

export function openModal(id) { const m = document.getElementById(id); if (m) m.classList.add("open"); }
export function closeModal(id) { const m = document.getElementById(id); if (m) m.classList.remove("open"); }

export function btnLoading(btn, loading, textWhenIdle) {
  if (!btn) return;
  if (loading) {
    btn.dataset.idle = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner"></span> Please wait...`;
  } else {
    btn.disabled = false;
    btn.innerHTML = textWhenIdle || btn.dataset.idle || btn.innerHTML;
  }
}

export function fmtDate(value) {
  if (!value) return "-";
  const d = value.toDate ? value.toDate() : (value.seconds ? new Date(value.seconds * 1000) : new Date(value));
  if (isNaN(d)) return "-";
  return d.toLocaleString("en-PH", { month: "short", day: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" });
}

export function initials(name) {
  return (name || "?").split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0].toUpperCase()).join("");
}

// Downscale an image File to a base64 data URL (keeps Firestore docs small).
export function fileToDataUrl(file, maxSize = 400) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onerror = reject;
    reader.onload = () => {
      const img = new Image();
      img.onerror = reject;
      img.onload = () => {
        const scale = Math.min(1, maxSize / Math.max(img.width, img.height));
        const canvas = document.createElement("canvas");
        canvas.width = Math.round(img.width * scale);
        canvas.height = Math.round(img.height * scale);
        canvas.getContext("2d").drawImage(img, 0, 0, canvas.width, canvas.height);
        resolve(canvas.toDataURL("image/jpeg", 0.82));
      };
      img.src = reader.result;
    };
    reader.readAsDataURL(file);
  });
}

export function guardSession(requiredRole) {
  return import("./auth.js").then(({ requireStaff }) => requireStaff(requiredRole));
}
