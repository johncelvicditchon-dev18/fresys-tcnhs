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
    window.location.href = "../auth/login.php";
}

function displaySignUp() {
    window.location.href = "../auth/sign-up.php";
}
function displayVerification(){
    window.location.href = "../auth/student_verification.php";
}

