
const photo = document.getElementById("photo").addEventListener("change", function () {
   
   
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById("preview").src = e.target.result;
    };
    reader.readAsDataURL(file);
});
function backRegisteredCandidate(){
 window.location.href = "../pages/dashboard.php";
}
