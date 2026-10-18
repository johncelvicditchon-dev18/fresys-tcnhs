// table declation
const party_table = document.getElementById('party_table');
const year_table = document.getElementById('year_table');
const section_table = document.getElementById('section_table');
const school_table = document.getElementById('school_table');
// pop up
const update_party_pop = document.getElementById('update-party');
const update_year_pop = document.getElementById('update-year');
const update_section_pop = document.getElementById('update-section');
const update_school_pop = document.getElementById('update-school');
//close x
const partyclose = document.getElementById('update-party-close-x');
const yearclose = document.getElementById('update-year-close-x');
const sectionclose = document.getElementById('update-section-close-x');
const schoolclose = document.getElementById('update-school-close-x');

////////-----------------UPDATE PARTY 
party_table.addEventListener('click', function (e) {
    const updateicon = e.target.closest('.editparty');
    if (!updateicon) return;

    const row = updateicon.closest('tr');
    if (!row) return;

    document.getElementById('id-for-party-update').value = row.children[1].textContent;
    document.getElementById('input-for-party-update').value = row.children[2].textContent;

    delete_pop_up.style.display = "block";
    update_party_pop.style.display = "block";
});


function updateParty() {

    const partyID = document.getElementById('id-for-party-update').value;
    const partyInput = document.getElementById('input-for-party-update').value;

    fetch("../handlers/manage_election_update.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=updateparty` +
            `&id=${encodeURIComponent(partyID)}` +
            `&party=${encodeURIComponent(partyInput)}`
    })
        .then(res => res.text())
        .then(result => {
            if (result.trim() === "success") {
                const row = document.querySelector(`tr[data-id='${partyID}']`);
                if (!row) return;
                row.children[2].textContent = partyInput;
                alert("Record updated successfully.");

                delete_pop_up.style.display = "none";
                update_party_pop.style.display = "none";
            }
            else {
                alert("Please fill in required field before submitting.");

            }
        })
}

////////-----------------UPDATE ACADEMIC YEAR

year_table.addEventListener('click', function (e) {
    const updateicon = e.target.closest('.editacadyear');
    if (!updateicon) return;

    const row = updateicon.closest('tr');
    if (!row) return;

    document.getElementById('id-for-year-update').value = row.children[1].textContent;
    document.getElementById('input-for-update-year').value = row.children[2].textContent;

    delete_pop_up.style.display = "block";
    update_year_pop.style.display = "block";
});

function updateYear() {

    const yearID = document.getElementById('id-for-year-update').value;
    const yearInput = document.getElementById('input-for-update-year').value;

    fetch("../handlers/manage_election_update.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=updateyear` +
            `&id=${encodeURIComponent(yearID)}` +
            `&year=${encodeURIComponent(yearInput)}`
    })
        .then(res => res.text())
        .then(result => {
            if (result.trim() === "success") {
                const row = document.querySelector(`tr[data-id='${yearID}']`);
                if (!row) return;
                row.children[2].textContent = yearInput;
                alert("Record updated successfully.");

                delete_pop_up.style.display = "none";
                update_year_pop.style.display = "none";
            }
            else {
                alert("Please fill in required field before submitting.");

            }
        })
}


////////-----------------GRADE AND SECTION FOR

section_table.addEventListener('click', function (e) {
    const updateicon = e.target.closest('.editsection');
    if (!updateicon) return;

    const row = updateicon.closest('tr');
    if (!row) return;

    document.getElementById('id-for-section-update').value = row.children[1].textContent;
    document.getElementById('grade__section').value = row.children[2].textContent;
    document.getElementById('input-for-update-section').value = row.children[3].textContent;

    delete_pop_up.style.display = "block";
    update_section_pop.style.display = "block";
});

function updateSectionandGrade() {

    const sectionID = document.getElementById('id-for-section-update').value;
    const gradeselect = document.getElementById('grade__section').value;
    const sectioninput = document.getElementById('input-for-update-section').value;

    fetch("../handlers/manage_election_update.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=updatesection` +
            `&id=${encodeURIComponent(sectionID)}` +
            `&gradeforsection=${encodeURIComponent(gradeselect)}` +
            `&section=${encodeURIComponent(sectioninput)}`
    })
        .then(res => res.text())
        .then(result => {
            if (result.trim() === "success") {
                const row = document.querySelector(`tr[data-id='${sectionID}']`);
                if (!row) return;
                row.children[2].textContent = gradeselect;
                row.children[3].textContent = sectioninput;
                alert("Record updated successfully.");

                delete_pop_up.style.display = "none";
                update_section_pop.style.display = "none";
            }
            else {
                alert("Please fill in required field before submitting.");

            }
        })
}

////////-----------------SCHOOL NAME

school_table.addEventListener('click', function (e) {
    const updateicon = e.target.closest('.editschoolname');
    if (!updateicon) return;

    const row = updateicon.closest('tr');
    if (!row) return;

    document.getElementById('id-for-school-update').value = row.children[1].textContent;
    document.getElementById('input-for-update-school').value = row.children[2].textContent;

    delete_pop_up.style.display = "block";
    update_school_pop.style.display = "block";
});

function updateSchool() {

    const schoolID = document.getElementById('id-for-school-update').value;
    const schoolInput = document.getElementById('input-for-update-school').value;

    fetch("../handlers/manage_election_update.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=updateschool` +
            `&id=${encodeURIComponent(schoolID)}` +
            `&schoolname=${encodeURIComponent(schoolInput)}`
    })
        .then(res => res.text())
        .then(result => {
            if (result.trim() === "success") {
                const row = document.querySelector(`tr[data-id='${schoolID}']`);
                if (!row) return;
                row.children[2].textContent = schoolInput;
                alert("Record updated successfully.");

                delete_pop_up.style.display = "none";
                update_school_pop.style.display = "none";
            }
            else {
                alert("Please fill in required field before submitting.");

            }
        })
}


//// close pop up
function cancelUpdateParty() {
    delete_pop_up.style.display = "none";
    update_party_pop.style.display = "none";
}
function cancelUpdateYear() {
    delete_pop_up.style.display = "none";
    update_year_pop.style.display = "none";
}
function cancelUpdateSection() {
    delete_pop_up.style.display = "none";
    update_section_pop.style.display = "none";
}
function cancelUpdateSchool() {
    delete_pop_up.style.display = "none";
    update_school_pop.style.display = "none";
}

partyclose.addEventListener('click', function () {
    cancelUpdateParty();
});
yearclose.addEventListener('click', function () {
    cancelUpdateYear();
});
sectionclose.addEventListener('click', function () {
    cancelUpdateSection();
});
schoolclose.addEventListener('click', function () {
    cancelUpdateSchool();
});


