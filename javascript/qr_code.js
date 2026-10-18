const modal = document.getElementById("qr-pop");
const qrPic = document.getElementById("qr-picture");
const closeBtn = document.getElementById("closeqrcode");
const tbody = document.getElementById("table-body-student");

document.querySelectorAll('.qr-code').forEach(qrDiv => {
    const json_data = qrDiv.dataset.qr;
    new QRCode(qrDiv, { text: json_data, width: 70, height: 70, correctLevel: QRCode.CorrectLevel.H });

});



tbody.addEventListener("click", (e) => {
    const qrImg = e.target.closest(".qr-code");
    if (!qrImg) return;

    const jsonData = qrImg.dataset.qr;

    qrPic.innerHTML = "";
    new QRCode(qrPic, {
        text: jsonData,
        width: 250,
        height: 250,
        correctLevel: QRCode.CorrectLevel.H
    });


    modal.style.display = "flex";
});


closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
});


modal.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});