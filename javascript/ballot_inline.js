const headerProfile = document.getElementById('header-profile');
headerProfile.addEventListener('click', (e) => {
    e.stopPropagation();
    headerProfile.classList.toggle('open');
});
document.addEventListener('click', (e) => {
    if (!headerProfile.contains(e.target)) {
        headerProfile.classList.remove('open');
    }
});
function ballotLogout() {
    const confirmed = confirm("Are you sure you want to log out?");
    if (!confirmed) return;
    window.location.href = "../auth/logout.php";
}
