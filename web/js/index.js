const dev = document.getElementById("popUpGwapo");
const closex = document.getElementById('closex');

document.getElementById("developer").addEventListener('click', () => {
  dev.style.display = "block";
});

dev.addEventListener('click', () => {
  dev.style.display = 'none';
});
closex.addEventListener('click', () => {
  dev.style.display = 'none';
});

function displayLogin() {
  window.location.href = "login.html";
}

function displaySignUp() {
  window.location.href = "signup.html";
}

function displayVerification() {
  window.location.href = "verify.html";
}
