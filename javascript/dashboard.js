
function gotoPage() {
    window.location.href = "index.php";
}
function nothing() {
    document.getElementById('logout').style.display = "none";
}
function proceedToRegistration() {
    window.location.href = "../auth/admin_registration_form.php";
}
function proceedToHistory() {
    window.location.href = "account_datestamp.php";
}
const logout_pop = document.querySelector('.logout-pop');
const delete_pop = document.querySelector('.delete-pop-up');

function popUpLogout() {
    const confirmed = confirm("Are your sure you want to log out?");
    if (!confirmed) return;

    window.location.href = "../auth/logout.php";
}


function proceedRegistrationCandidates() {
    window.location.href = "candidates_registration.php";
}

document.getElementById('close-update-account').addEventListener('click', function () {
    delete_pop_up.style.display = 'none';
    updatebox_acc.style.display = 'none';


});

///// dashboard.php search bar function in registered students table
const searchInput = document.getElementById('search-student');
const tableBody = document.getElementById('table-body-student');

searchInput.addEventListener('input', function () {
    const searchValue = this.value;

    fetch(`../api/search_student_admin.php?search=${encodeURIComponent(searchValue)}`)
        .then(response => response.text())
        .then(data => {
            tableBody.innerHTML = data;
        });
});

///// dashboard.php search bar function in account table
const search_account = document.getElementById('search-account');
const tableBody_account = document.getElementById('table-body-account');

search_account.addEventListener('input', function () {
    const search_account_value = this.value;

    fetch(`../api/search_account.php?search-account=${encodeURIComponent(search_account_value)}`)
        .then(response => response.text())
        .then(data => {
            tableBody_account.innerHTML = data;
        });
});


const table_student = document.getElementById('table-student');
const delete_pop_up = document.getElementById('delete-pop-up');
const table_account = document.getElementById('table-account');
let deleteId = null, deleteRow = null;
let deleteRowAccount = null, deleteIdAccount = null;

///// click the delete i in the student table
document.addEventListener("DOMContentLoaded", function () {
    table_student.addEventListener("click", function (event) {
        const icondelete = event.target.closest(".btn-delete-table");
        if (!icondelete) return;
        deleteRow = icondelete.closest("tr");
        deleteId = icondelete.dataset.id;

        const confirmed = confirm("Are you sure you want to delete this student?");
        if (!confirmed) return;

        fetch("../handlers/student_action.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=delete&id=" + deleteId
        })
            .then(res => res.text())
            .then(result => {
                if (result.trim() === "success") {
                    deleteRow.remove();
                    alert("Student deleted successfully.");
                } else {
                    alert("Failed to delete student.");
                }
            });
    });

    table_student.addEventListener("click", function (event) {
        if (event.target.closest(".btn-delete-table") || event.target.closest(".editicon") || event.target.closest(".qr-code") || event.target.closest("button")) return;
        const row = event.target.closest("tr");
        if (!row) return;
        const studentId = row.getAttribute("data-id");
        if (studentId) showStudentVotes(studentId);
    });

});

///// click the delete i in the account table
document.addEventListener("DOMContentLoaded", function () {
    table_account.addEventListener("click", function (event) {
        const icondelete = event.target.closest(".btn-delete-acc");
        if (!icondelete) return;
        deleteRowAccount = icondelete.closest("tr");
        deleteIdAccount = icondelete.dataset.id;

        const confirmed = confirm("Are you sure you want to delete this account?");
        if (!confirmed) return;

        fetch("../handlers/account_action.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=delete&id=" + deleteIdAccount
        })
            .then(res => res.text())
            .then(result => {
                if (result.trim() === "success") {
                    deleteRowAccount.remove();
                    alert("Account deleted successfully.");
                }
                else {
                    alert("Failed to delete account.");
                }
            });
    });

});

/// click update icon in the table student and dispay pop up
const updatebox = document.getElementById('update_student');
const gradeSelect = document.getElementById("grade");
const sectionSelect = document.getElementById("section");


function loadSections(gradeselected, selectedSection = null) {
    if (!gradeselected) return;


    fetch("../api/get_all_section.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "gradeselected=" + encodeURIComponent(gradeselected)
    })
        .then(response => response.json())
        .then(sections => {
            sectionSelect.innerHTML = '<option  value="" disabled selected>Select Section</option>';

            if (sections.length === 0) {
                sectionSelect.innerHTML += '<option  value="" disabled>No sections found</option>';
                return;
            }

            sections.forEach(section => {
                const option = document.createElement("option");
                option.value = section;
                option.textContent = section;


                if (selectedSection && section === selectedSection) {
                    option.selected = true;
                }

                sectionSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error("Error loading sections:", error);
            sectionSelect.innerHTML =
                '<option value="" disabled>Loading sections</option>';
        });
}


table_student.addEventListener('click', function (event) {
    const updateicon = event.target.closest('.editicon');
    if (!updateicon) return;

    const row = updateicon.closest('tr');
    if (!row) return;

    document.getElementById('id-student').value = row.children[1].textContent.trim();
    document.getElementById('fname').value = row.children[4].textContent.trim();
    document.getElementById('mname').value = row.children[5].textContent.trim();
    document.getElementById('lname').value = row.children[6].textContent.trim();
    document.getElementById('gender').value = row.children[7].textContent.trim();

    const grade = row.children[8].textContent.trim();
    const section = row.children[9].textContent.trim();


    gradeSelect.value = grade;


    loadSections(grade, section);

    delete_pop_up.style.display = 'block';
    updatebox.style.display = 'flex';
});


gradeSelect.addEventListener("change", function () {
    loadSections(this.value);
});



/// update account click in the table account
const updatebox_acc = document.getElementById('update-account');

table_account.addEventListener('click', function (event) {
    const updateicon = event.target.closest('.editaccount');
    if (!updateicon) return;

    const row = updateicon.closest('tr');
    if (!row) return;
    document.getElementById('id-account').value = row.children[1].textContent;
    document.getElementById('teacher-name').value = row.children[2].textContent;
    document.getElementById('username').value = row.children[3].textContent;
    document.getElementById('account-access').value = row.children[5].textContent;

    delete_pop_up.style.display = 'block';
    updatebox_acc.style.display = 'block';

});



//fucntion to update students info
function UpdateStudent() {
    const id = document.getElementById('id-student').value;
    const firstn = document.getElementById('fname').value;
    const middlen = document.getElementById('mname').value;
    const lastn = document.getElementById('lname').value;
    const gender = document.getElementById('gender').value;
    const grade = document.getElementById('grade').value;
    const section = document.getElementById('section').value;


    fetch("../handlers/student_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
            `action=update` +
            `&id=${encodeURIComponent(id)}` +
            `&firstname=${encodeURIComponent(firstn)}` +
            `&middlename=${encodeURIComponent(middlen)}` +
            `&lastname=${encodeURIComponent(lastn)}` +
            `&gender=${encodeURIComponent(gender)}` +
            `&gradelevel=${encodeURIComponent(grade)}` +
            `&section=${encodeURIComponent(section)}`
    })
        .then(res => res.text())
        .then(result => {
            if (result.trim() === "success") {

                const row = document.querySelector(`tr[data-id='${id}']`);
                if (!row) return;
                row.children[3].textContent = firstn;
                row.children[4].textContent = middlen;
                row.children[5].textContent = lastn;
                row.children[6].textContent = gender;
                row.children[7].textContent = grade;
                row.children[8].textContent = section;
                alert("Student updated successfully.");

                delete_pop_up.style.display = 'none';
                updatebox.style.display = 'none';

            } else {
                alert("Please fill in all required fields before submitting.");

            }
        })

        .catch(err => {
            console.error(err);
            alert("Server error");
        });
}

///function calling account_action.php to save the changes
function updateAccount() {
    const id_account = document.getElementById('id-account').value;
    const teacher_name = document.getElementById('teacher-name').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const acc_access = document.getElementById('account-access').value;

    let body = `action=update` +
        `&id=${encodeURIComponent(id_account)}` +
        `&name=${encodeURIComponent(teacher_name)}` +
        `&username=${encodeURIComponent(username)}` +
        `&access=${encodeURIComponent(acc_access)}`;
    if (password.trim() !== '') {
        body += `&password=${encodeURIComponent(password)}`;
    }

    fetch("../handlers/account_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: body
    })
        .then(res => res.text())
        .then(result => {
            if (result.trim() === "success") {
                const row = document.querySelector(`tr[data-id='${id_account}']`)
                if (!row) return;

                row.children[2].textContent = teacher_name;
                row.children[3].textContent = username;
                row.children[5].textContent = acc_access;
                alert("Account updated successfully.");

                delete_pop_up.style.display = 'none';
                updatebox_acc.style.display = 'none';
            }
            else {
                alert("Please fill in all required fields before submitting.");

            }
        })

        .catch(err => {
            console.error(err);
            alert("Server error");
        });
}


//// cancel updating student
function cancelUpdatingStudent() {
    delete_pop_up.style.display = 'none';
    updatebox.style.display = 'none';
}

function cancelUpdatingAccount() {
    delete_pop_up.style.display = 'none';
    updatebox_acc.style.display = 'none';
}
document.querySelector('.header-update-student .ph-x').addEventListener('click', function () {
    document.getElementById('update_student').style.display = "none";
    document.getElementById('delete-pop-up').style.display = "none";

});



