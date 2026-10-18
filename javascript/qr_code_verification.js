let html5QrcodeScanner = null;


const student_name0 = document.getElementById("student_name");
const verification_code0 = document.getElementById("verification_code");
const grade_level0 = document.getElementById("grade_level");
const section0 = document.getElementById("section");

function onScanSuccess(decodedText) {

    try {
        const student = JSON.parse(decodedText);
       // console.log("QR Code raw text:", decodedText);
        console.log("STUDENT INFORMATIONS:", student);

        student_name0.value = student.student_name || "";
        verification_code0.value = student.verification_code || "";
        grade_level0.value = student.grade_level || "";
        section0.value = student.section || "";


        fetch("../auth/student_verification_qr.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "code=" + encodeURIComponent(student.verification_code) +
                "&grade_level=" + encodeURIComponent(student.grade_level)
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === "alreadycasted") {
                    alert("This verification code has already been used.");
                } else if (data.status === "error") {
                    alert("Invalid code or grade level. Please try again.");
                } else if (data.status === "success" && data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(err => console.error(err));

    } catch (err) {
        ///    console.error("Invalid QR Code format", err);
        alert("Invalid QR code.");
    }
}


const qrBtn = document.getElementById('qrBtn');
const manualBtn = document.getElementById('manualBtn');
const qrVerify = document.getElementById('qrVerify');
const manualVerify = document.getElementById('manualVerify');

manualBtn.onclick = () => {
    manualBtn.classList.add("active");
    qrBtn.classList.remove("active");
    manualVerify.style.display = "block";
    qrVerify.style.display = "none";

};

qrBtn.onclick = () => {
    qrBtn.classList.add("active");
    manualBtn.classList.remove("active");
    manualVerify.style.display = "none";
    qrVerify.style.display = "block";

    if (html5QrcodeScanner) return;

    html5QrcodeScanner = new Html5Qrcode("reader");

    html5QrcodeScanner.start(
        { facingMode: "environment" },
        {
            fps: 30,
            qrbox: 250,
            aspectRatio: 1
        },
        onScanSuccess
    ).catch(err => console.error("Camera start failed", err));
};