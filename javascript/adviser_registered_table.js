function proceedToRegistration() {
    window.location.href = "../auth/adviser_registration_form.php"
}
function backtoPage() {
    const confirmed = confirm("Are you sure you want to log out?");

    if (!confirmed) return;
    window.location.href = "../auth/logout.php";

}


//// adviser_registered_table.php search bar
const searchStudentAdviser = document.getElementById('search-student-in-adviser');
const tableBodyStudentAdviser = document.getElementById('table-body-adviser-student');


searchStudentAdviser.addEventListener('input', function () {
    const value = this.value;

    fetch(`../api/search_student_adviser.php?search=${encodeURIComponent(value)}`)
        .then(res => res.text())
        .then(data => {
            tableBodyStudentAdviser.innerHTML = data;

        });
});


///// click the delete i in the student table
const table_student = document.getElementById('table-body-adviser-student');
const updatebox = document.getElementById('update_student');
const delete_box = document.getElementById('delete-box');
const delete_pop_up = document.getElementById('delete-pop-up');

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

});

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
            sectionSelect.innerHTML = '<option value="" disabled selected>Select Section</option>';

            if (sections.length === 0) {
                sectionSelect.innerHTML += '<option value="" disabled>No sections found</option>';
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
    document.getElementById('fname').value = row.children[3].textContent.trim();
    document.getElementById('mname').value = row.children[4].textContent.trim();
    document.getElementById('lname').value = row.children[5].textContent.trim();
    document.getElementById('gender').value = row.children[6].textContent.trim();

    const grade = row.children[7].textContent.trim();
    const section = row.children[8].textContent.trim();


    gradeSelect.value = grade;


    loadSections(grade, section);

    delete_pop_up.style.display = 'block';
    updatebox.style.display = 'flex';
});


gradeSelect.addEventListener("change", function () {
    loadSections(this.value);
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
///CANCEL UPDATING ACCOUNT
function cancelUpdatingStudent() {
    delete_pop_up.style.display = 'none';
    updatebox.style.display = 'none';
}
///cancel to delete student
function cancelDeletingStudent() {
    delete_pop_up.style.display = "none";
    delete_box.style.display = "none";
}





document.getElementById('close').addEventListener('click', function () {
    document.getElementById('update_student').style.display = "none";
    document.getElementById('delete-pop-up').style.display = "none";

});

// Profile dropdown toggle
const headerProfile = document.getElementById('header-profile');
const headerDropdown = document.getElementById('header-dropdown');

headerProfile.addEventListener('click', (e) => {
    e.stopPropagation();
    headerProfile.classList.toggle('open');
});

document.addEventListener('click', (e) => {
    if (!headerProfile.contains(e.target)) {
        headerProfile.classList.remove('open');
    }
});