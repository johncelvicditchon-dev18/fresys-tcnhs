const photo = document.getElementById("photo").addEventListener("change", function () {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById("photodis").src = e.target.result;
    };
    reader.readAsDataURL(file);


});


const candidateImages = document.querySelectorAll('.candidate-image');
const popup = document.getElementById('pop-images-candidate');
const blurd = document.getElementById('delete-pop-up');
const div = document.getElementById('div-for-images');
const close = document.querySelector('.close-popup');
candidateImages.forEach(img => {
    img.addEventListener('click', function () {
        popup.src = this.src;
        popup.style.display = "block";
        div.style.display = 'block';
        blurd.style.display = 'block'
    });
});
close.addEventListener('click', function () {
    popup.style.display = "none";
    div.style.display = 'none';
    blurd.style.display = 'none'

});






