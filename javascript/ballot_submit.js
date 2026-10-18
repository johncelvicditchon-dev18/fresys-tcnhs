document.querySelectorAll("input[type='checkbox']").forEach(cb => {
    cb.addEventListener("change", () => {
        const position = cb.dataset.position;
        const name = cb.dataset.name;

        // SINGLE SELECTION POSITIONS
        if (
            position === "President" ||
            position === "Vice-President" ||
            position === "Secretary" ||
            position === "Treasurer" ||
            position === "Auditor" ||
            position === "P.I.O(SHS)" ||
            position === "P.I.O(JHS)" ||
            position === "Protocol Officer(SHS)" ||
            position === "Protocol Officer(JHS)"
        ) {
            // uncheck others in same position
            document.querySelectorAll(
                `input[data-position="${position}"]`
            ).forEach(other => {
                if (other !== cb) other.checked = false;
            });

            const map = {
                "President": "review_president",
                "Vice-President": "review_vice_president",
                "Secretary": "review_secretary",
                "Treasurer": "review_treasurer",
                "Auditor": "review_auditor",
                "P.I.O(SHS)": "review_pio_shs",
                "P.I.O(JHS)": "review_pio_jhs",
                "Protocol Officer(SHS)": "review_protocol_shs",
                "Protocol Officer(JHS)": "review_protocol_jhs"
            };

            const reviewInput = document.getElementById(map[position]);

            reviewInput.value = cb.checked ? name : "No Selection";

            reviewInput.style.color = reviewInput.value === "No Selection" ? "#f87171" : "#ffffff";
        }
    });
});



function handleRepresentative(position, input1, input2) {
    const checked = Array.from(
        document.querySelectorAll(
            `input[data-position="${position}"]:checked`
        )
    ).map(cb => cb.dataset.name);

    document.getElementById(input1).value = checked[0] || "";
    document.getElementById(input2).value = checked[1] || "";
}

function setupRepresentative(gradeClass, position, input1, input2, label) {
    document.querySelectorAll(`.${gradeClass}`).forEach(cb => {
        cb.addEventListener("change", () => {
            const checked = document.querySelectorAll(
                `.${gradeClass}:checked`
            );

            if (checked.length > 2) {
                cb.checked = false;
                alert(`You can only select 2 ${label} representatives.`);
                return;
            }

            handleRepresentative(position, input1, input2);
        });
    });
}
setupRepresentative(
    "grade-8-input",
    "Grade 8 Representative",
    "review_grade8_1",
    "review_grade8_2",
    "Grade 8"
);

setupRepresentative(
    "grade-9-input",
    "Grade 9 Representative",
    "review_grade9_1",
    "review_grade9_2",
    "Grade 9"
);

setupRepresentative(
    "grade-10-input",
    "Grade 10 Representative",
    "review_grade10_1",
    "review_grade10_2",
    "Grade 10"
);

setupRepresentative(
    "grade-11-input",
    "Grade 11 Representative",
    "review_grade11_1",
    "review_grade11_2",
    "Grade 11"
);

setupRepresentative(
    "grade-12-input",
    "Grade 12 Representative",
    "review_grade12_1",
    "review_grade12_2",
    "Grade 12"
);

/// submit votes

document.addEventListener("DOMContentLoaded", () => {

    const president = document.getElementById('review_president');
    const vice_president = document.getElementById('review_vice_president');
    const secretary = document.getElementById('review_secretary');
    const treasurer = document.getElementById('review_treasurer');
    const auditor = document.getElementById('review_auditor');

    const pio_shs = document.getElementById('review_pio_shs');
    const pio_jhs = document.getElementById('review_pio_jhs');

    const protocol_shs = document.getElementById('review_protocol_shs');
    const protocol_jhs = document.getElementById('review_protocol_jhs');
    // representatives
    const grade8rep_1 = document.getElementById('review_grade8_1');
    const grade8rep_2 = document.getElementById('review_grade8_2');

    const grade9rep_1 = document.getElementById('review_grade9_1');
    const grade9rep_2 = document.getElementById('review_grade9_2');

    const grade10rep_1 = document.getElementById('review_grade10_1');
    const grade10rep_2 = document.getElementById('review_grade10_2');

    const grade11rep_1 = document.getElementById('review_grade11_1');
    const grade11rep_2 = document.getElementById('review_grade11_2');

    const grade12rep_1 = document.getElementById('review_grade12_1');
    const grade12rep_2 = document.getElementById('review_grade12_2');

    window.confirm_submit = function () {
        const student_id = document.getElementById('ballot_id').value;
        const student_fullname = document.getElementById('ballot_name').value;
        const student_grade = document.getElementById('ballot_grade').value;

        if (student_grade === 'Grade 7') {

            fetch("../handlers/submit_votes.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    student_id,
                    student_fullname,
                    student_grade,
                    president: president.value,
                    vice_president: vice_president.value,
                    secretary: secretary.value,
                    treasurer: treasurer.value,
                    auditor: auditor.value,
                    pio_jhs: pio_jhs.value,
                    protocol_jhs: protocol_jhs.value,
                    grade_8_rep1: grade8rep_1.value,
                    grade_8_rep2: grade8rep_2.value
                })
            })
                .then(res => res.json())
                .then(data => {
                    console.log(data);
                    alert(data.message);
                    window.location.href = "../auth/student_verification.php";
                })
                .catch(err => console.error(err));

        }
        else if (student_grade === 'Grade 8') {

            fetch("../handlers/submit_votes.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    student_id,
                    student_fullname,
                    student_grade,
                    president: president.value,
                    vice_president: vice_president.value,
                    secretary: secretary.value,
                    treasurer: treasurer.value,
                    auditor: auditor.value,
                    pio_jhs: pio_jhs.value,
                    protocol_jhs: protocol_jhs.value,
                    grade_9_rep1: grade9rep_1.value,
                    grade_9_rep2: grade9rep_2.value
                })
            })
                .then(res => res.json())
                .then(data => {
                    console.log(data);
                    alert(data.message);
                    window.location.href = "../auth/student_verification.php";
                })
                .catch(err => console.error(err));
        }
        else if (student_grade === 'Grade 9') {

            fetch("../handlers/submit_votes.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    student_id,
                    student_fullname,
                    student_grade,
                    president: president.value,
                    vice_president: vice_president.value,
                    secretary: secretary.value,
                    treasurer: treasurer.value,
                    auditor: auditor.value,
                    pio_jhs: pio_jhs.value,
                    protocol_jhs: protocol_jhs.value,
                    grade_10_rep1: grade10rep_1.value,
                    grade_10_rep2: grade10rep_2.value

                })
            })
                .then(res => res.json())
                .then(data => {
                    console.log(data);
                    alert(data.message);
                    window.location.href = "../auth/student_verification.php";
                })
                .catch(err => console.error(err));

        }
        else if (student_grade === 'Grade 10') {

            fetch("../handlers/submit_votes.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    student_id,
                    student_fullname,
                    student_grade,
                    president: president.value,
                    vice_president: vice_president.value,
                    secretary: secretary.value,
                    treasurer: treasurer.value,
                    auditor: auditor.value,
                    pio_shs: pio_shs.value,
                    protocol_shs: protocol_shs.value,
                    grade_11_rep1: grade11rep_1.value,
                    grade_11_rep2: grade11rep_2.value

                })
            })
                .then(res => res.json())
                .then(data => {
                    console.log(data);
                    alert(data.message);
                    window.location.href = "../auth/student_verification.php";
                })
                .catch(err => console.error(err));

        }
        else if (student_grade === 'Grade 11') {

            fetch("../handlers/submit_votes.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    student_id,
                    student_fullname,
                    student_grade,
                    president: president.value,
                    vice_president: vice_president.value,
                    secretary: secretary.value,
                    treasurer: treasurer.value,
                    auditor: auditor.value,
                    pio_shs: pio_shs.value,
                    protocol_shs: protocol_shs.value,
                    grade_12_rep1: grade12rep_1.value,
                    grade_12_rep2: grade12rep_2.value

                })
            })
                .then(res => res.json())
                .then(data => {
                    console.log(data);
                    alert(data.message);
                    window.location.href = "../auth/student_verification.php";
                })
                .catch(err => console.error(err));

        }
        else if (student_grade === 'Grade 12') {

            fetch("../handlers/submit_votes.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    student_id,
                    student_fullname,
                    student_grade,
                    president: president.value,
                    vice_president: vice_president.value,
                    secretary: secretary.value,
                    treasurer: treasurer.value,
                    auditor: auditor.value,
                    pio_shs: pio_shs.value,
                    protocol_shs: protocol_shs.value,

                })
            })
                .then(res => res.json())
                .then(data => {
                    console.log(data);
                    alert(data.message);
                    window.location.href = "../auth/student_verification.php";
                })
                .catch(err => console.error(err));

        }







    }
});