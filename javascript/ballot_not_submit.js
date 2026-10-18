// (1) selection
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".president-box, .vice-president-box, .secretary-box, .treasurer-box, .auditor-box, .pioshs-box, .piojhs-box, .protocol-shs-box, .protocol-jhs-box").forEach(section => {

        const counter = section.querySelector("h2[id$='count']");
        const checkboxes = section.querySelectorAll("input[type='checkbox']");

        checkboxes.forEach(cb => {
            cb.addEventListener("change", () => {

                if (cb.checked) {
                    checkboxes.forEach(other => {
                        if (other !== cb) other.checked = false;
                    });
                    counter.textContent = "1/1";
                    counter.style.color = "#00a300";
                } else {
                    counter.textContent = "0/1";
                    counter.style.color = "#0f172a";
                }
            });
        });
    });


    const grades = [8, 9, 10, 11, 12];
    const MAX = 2;

    grades.forEach(grade => {
        const Inputs = document.querySelectorAll(`.grade-${grade}-input`);
        const counterforvotes = document.getElementById(`grade-${grade}-representative-count`);



        Inputs.forEach(cb => {
            cb.addEventListener("change", () => {

                const checked = document.querySelectorAll(`.grade-${grade}-input:checked`);

                counterforvotes.textContent = `${checked.length}/${MAX}`;
                counterforvotes.style.color = checked.length === MAX ? "#00A300" : "#0f172a";

                Inputs.forEach(input => {
                    input.disabled = checked.length >= MAX && !input.checked;
                });
            });
        });
    });

});


// location for every grade
const ballot_grade = document.getElementById('ballot_grade');
//// row in the ballot removeee
const pioshs = document.querySelector('.pioshs-box');
const piojhs = document.querySelector('.piojhs-box');

const protocolshs = document.querySelector('.protocol-shs-box');
const protocoljhs = document.querySelector('.protocol-jhs-box');

const grade8row = document.querySelector('.grade-8-representative-box');
const grade9row = document.querySelector('.grade-9-representative-box');
const grade10row = document.querySelector('.grade-10-representative-box');
const grade11row = document.querySelector('.grade-11-representative-box');
const grade12row = document.querySelector('.grade-12-representative-box');

///// review card submission
const cardpio_shs = document.getElementById('cardpio-shs');
const cardpio_jhs = document.getElementById('cardpio-jhs');

const cardprotocol_shs = document.getElementById('cardprotocol-shs');
const cardprotocol_jhs = document.getElementById('cardprotocol-jhs');

const card_grade8 = document.getElementById('card-grade8');
const card_grade9 = document.getElementById('card-grade9');
const card_grade10 = document.getElementById('card-grade10');
const card_grade11 = document.getElementById('card-grade11');
const card_grade12 = document.getElementById('card-grade12');

if (ballot_grade.value.trim() === "Grade 7") {

    pioshs.style.display = "none";
    protocolshs.style.display = "none";
    grade9row.style.display = "none";
    grade10row.style.display = "none";
    grade11row.style.display = "none";
    grade12row.style.display = "none";

    cardpio_shs.style.display = "none";
    cardprotocol_shs.style.display = "none";
    card_grade9.style.display = "none";
    card_grade10.style.display = "none";
    card_grade11.style.display = "none";
    card_grade12.style.display = "none";

}
else if (ballot_grade.value.trim() === "Grade 8") { // go to grade 9 ballot

    pioshs.style.display = "none";
    protocolshs.style.display = "none";
    grade8row.style.display = "none";
    grade10row.style.display = "none";
    grade11row.style.display = "none";
    grade12row.style.display = "none";

    cardpio_shs.style.display = "none";
    cardprotocol_shs.style.display = "none";
    card_grade8.style.display = "none";
    card_grade10.style.display = "none";
    card_grade11.style.display = "none";
    card_grade12.style.display = "none";

}
else if (ballot_grade.value.trim() === "Grade 9") { // go to grade 10 ballot

    pioshs.style.display = "none";
    protocolshs.style.display = "none";
    grade8row.style.display = "none";
    grade9row.style.display = "none";
    grade11row.style.display = "none";
    grade12row.style.display = "none";

    cardpio_shs.style.display = "none";
    cardprotocol_shs.style.display = "none";
    card_grade8.style.display = "none";
    card_grade9.style.display = "none";
    card_grade11.style.display = "none";
    card_grade12.style.display = "none";

}
else if (ballot_grade.value.trim() === "Grade 10") { // go to grade 11 ballot

    piojhs.style.display = "none";
    protocoljhs.style.display = "none";
    grade8row.style.display = "none";
    grade9row.style.display = "none";
    grade10row.style.display = "none";
    grade12row.style.display = "none";

    cardpio_jhs.style.display = "none";
    cardprotocol_jhs.style.display = "none";
    card_grade8.style.display = "none";
    card_grade9.style.display = "none";
    card_grade10.style.display = "none";
    card_grade12.style.display = "none";

}
else if (ballot_grade.value.trim() === "Grade 11") { // go to grade 12 ballot

    piojhs.style.display = "none";
    protocoljhs.style.display = "none";
    grade8row.style.display = "none";
    grade9row.style.display = "none";
    grade10row.style.display = "none";
    grade11row.style.display = "none";

    cardpio_jhs.style.display = "none";
    cardprotocol_jhs.style.display = "none";
    card_grade8.style.display = "none";
    card_grade9.style.display = "none";
    card_grade10.style.display = "none";
    card_grade11.style.display = "none";

}
else if (ballot_grade.value.trim() === "Grade 12") { // president down to auditor only.

    piojhs.style.display = "none";
    protocoljhs.style.display = "none";
    grade8row.style.display = "none";
    grade9row.style.display = "none";
    grade10row.style.display = "none";
    grade11row.style.display = "none";
    grade12row.style.display = "none";

    cardpio_jhs.style.display = "none";
    cardprotocol_jhs.style.display = "none";
    card_grade8.style.display = "none";
    card_grade9.style.display = "none";
    card_grade10.style.display = "none";
    card_grade11.style.display = "none";
    card_grade12.style.display = "none";

}


function review() {
    const modal = document.getElementById('reviewModal');
    modal.style.display = 'block';

}

function closeReview() {
    document.getElementById('reviewModal').style.display = 'none';
}


window.onclick = function (event) {
    const modal = document.getElementById('reviewModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

function cancel() {
    const confirmed = confirm("Are you sure you want to cancel?");
    if (!confirmed) return;
    window.location.href = "../pages/index.php";
}

function submitVote() {
    document.querySelector("form").submit();
}

