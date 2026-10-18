
const table_candidates = document.getElementById('table-body-candidate');
const background_blurd = document.getElementById('delete-pop-up');
const update_candidate_box = document.getElementById('update-candidate-box');


///// click the delete i in the candidate table
document.addEventListener("DOMContentLoaded", function () {
    table_candidates.addEventListener("click", function (event) {
        const icondelete = event.target.closest(".btn-delete-candidate");
        if (!icondelete) return;
        deleteRowCandidate = icondelete.closest("tr");
        deleteIdCandidate = icondelete.dataset.id;

        const confirmed = confirm("Are you sure you want to delete this candidate?");
        if (!confirmed) return;

        fetch("../handlers/candidate_actions.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=delete&id=" + deleteIdCandidate
        })
            .then(res => res.text())
            .then(result => {
                if (result.trim() === "success") {
                    deleteRowCandidate.remove();
                    alert("Candidate deleted successfully.");
                }
                else {
                    alert("Failed to delete candidate.");
                }
            });


    });

});




function cancelUpdateCandidate() {
    background_blurd.style.display = "none";
    update_candidate_box.style.display = "none";
}



/// update candidate click in the table candidate

table_candidates.addEventListener('click', function (event) {
    const updateicon = event.target.closest('.editcandidate');
    if (!updateicon) return;

    const row = updateicon.closest('tr');
    if (!row) return;
    document.getElementById('candidate-id').value = row.children[1].textContent;
    document.getElementById('candidate-update').value = row.children[3].textContent;
    document.getElementById('position-update').value = row.children[4].textContent;
    document.getElementById('year-update').value = row.children[6].textContent;
    document.getElementById('party-update').value = row.children[5].textContent;

    const photo = row.getAttribute('data-photo');
    document.getElementById('photodis').src = photo;



    background_blurd.style.display = "block";
    update_candidate_box.style.display = "block";

});


//fucntion to update candidate info
function updateCandidate() {
    const id = document.getElementById('candidate-id').value;
    const candidatename = document.getElementById('candidate-update').value;
    const position = document.getElementById('position-update').value;
    const year = document.getElementById('year-update').value;
    const party = document.getElementById('party-update').value;
    const photoInput = document.getElementById('photo');

    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', id);
    formData.append('candidatename', candidatename);
    formData.append('position', position);
    formData.append('year', year);
    formData.append('party', party);

    if (photoInput.files && photoInput.files[0]) {
        formData.append('photo', photoInput.files[0]);
    }

    fetch("../handlers/candidate_actions.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(result => {
            if (result.trim() === "success") {

                const row = document.querySelector(`tr[data-id='${id}']`);
                if (!row) return;
                const newPic = document.getElementById('photodis').src;
                row.children[2].querySelector('img').src = newPic;
                row.children[3].textContent = candidatename;
                row.children[4].textContent = position;
                row.children[6].textContent = year;
                row.children[5].textContent = party;

                alert("Candidate updated successfully.");

                background_blurd.style.display = "none";
                update_candidate_box.style.display = "none";

            } else {
                alert("Update failed: " + result.trim());

            }
        })

        .catch(err => {
            console.error(err);
            alert("Server error");
        });
}

