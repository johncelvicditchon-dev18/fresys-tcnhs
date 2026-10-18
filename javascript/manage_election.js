
/// add hereee

function addParty() {
    const partyInput = document.getElementById('add-party');
    const party = partyInput.value.trim();

    if (!party) {
        alert("Please provide a new party.");
        return;
    }

    fetch("../handlers/manage_election_add.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `add=party&party=${encodeURIComponent(party)}`
    })
        .then(res => res.text())
        .then(result => {
            result = result.trim();
            if (result === "success") {
                alert("Added successfully.");
                partyInput.value = "";
            } else {
                alert("Failed to add party");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Server Error");
        });
}

function addYear() {
    const yearInput = document.getElementById('add-year');
    const year = yearInput.value.trim();

    if (!year) {
        alert("Please provide a new academic year.");
        return;
    }

    fetch("../handlers/manage_election_add.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `add=year&year=${encodeURIComponent(year)}`
    })
        .then(res => res.text())
        .then(result => {
            result = result.trim();
            if (result === "success") {
                alert("Added successfully.");
                yearInput.value = "";
            } else {
                alert("Failed to add year");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Server Error");
        });


}

function addSection() {
    const sectionInput = document.getElementById('add-section');
    const gradelevelinsection = document.getElementById('gradelevelinsection');
    const levellevel = gradelevelinsection.value;
    const section = sectionInput.value.trim();

    if (!section || !levellevel) {
        alert("Please select grade and provide a new section.");
        return;
    }

    fetch("../handlers/manage_election_add.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `add=section&section=${encodeURIComponent(section)}&gradeLevel=${encodeURIComponent(levellevel)}`
    })
        .then(res => res.text())
        .then(result => {
            result = result.trim();
            if (result === "success") {
                alert("Added successfully.");
                sectionInput.value = "";
            } else {
                alert("Failed to add section");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Server Error");
        });
}

function addSchool() {
    const schoolInput = document.getElementById('add-school');
    const school = schoolInput.value.trim();

    if (!school) {
        alert("Please provide a new school name.");
        return;
    }

    fetch("../handlers/manage_election_add.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `add=school&school=${encodeURIComponent(school)}`
    })
        .then(res => res.text())
        .then(result => {
            result = result.trim();
            if (result === "success") {
                alert("Added successfully.");
                schoolInput.value = "";
            } else {
                alert("Failed to add school name");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Server Error");
        });
}

/// delete heree 
const table_party_here = document.getElementById('table-party-here');
const table_year_here = document.getElementById('table-year-here');
const table_section_here = document.getElementById('table-section-here');
const table_school_here = document.getElementById('table-school-here');

// delete party
document.addEventListener("DOMContentLoaded", function () {
    table_party_here.addEventListener("click", function (event) {
        const icondelete = event.target.closest(".btn-delete-party");
        if (!icondelete) return;

        const deleteRow = icondelete.closest("tr");
        const deleteId = icondelete.dataset.id;

        const confirmed = confirm("Are you sure you want to delete this record?");
        if (!confirmed) return;

        fetch("../handlers/manage_election_delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "delete=deleteparty&idparty=" + encodeURIComponent(deleteId)
        })
            .then(res => res.text())
            .then(result => {
                if (result.trim() === "success") {
                    deleteRow.remove();
                    alert("Deleted successfully.");
                } else {
                    alert("Cannot delete record when the candidates registration started.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong.");
            });
    });
});


// delete year 
document.addEventListener("DOMContentLoaded", function () {
    table_year_here.addEventListener("click", function (event) {
        const icondelete = event.target.closest(".btn-delete-year");
        if (!icondelete) return;

        const deleteRow = icondelete.closest("tr");
        const deleteId = icondelete.dataset.id;

        const confirmed = confirm("Are you sure you want to delete this record?");
        if (!confirmed) return;

        fetch("../handlers/manage_election_delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "delete=deleteyear&idyear=" + encodeURIComponent(deleteId)
        })
            .then(res => res.text())
            .then(result => {
                if (result.trim() === "success") {
                    deleteRow.remove();
                    alert("Deleted successfully.");
                } else {
                    alert("Cannot delete record when the candidates registration started.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong.");
            });
    });
});


// delete section 
document.addEventListener("DOMContentLoaded", function () {
    table_section_here.addEventListener("click", function (event) {
        const icondelete = event.target.closest(".btn-delete-section");
        if (!icondelete) return;

        const deleteRow = icondelete.closest("tr");
        const deleteId = icondelete.dataset.id;

        const confirmed = confirm("Are you sure you want to delete this record?");
        if (!confirmed) return;

        fetch("../handlers/manage_election_delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "delete=deletesection&idsection=" + encodeURIComponent(deleteId)
        })
            .then(res => res.text())
            .then(result => {
                if (result.trim() === "success") {
                    deleteRow.remove();
                    alert("Deleted successfully.");
                } else {
                    alert("Cannot delete record when the candidates registration started.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong.");
            });
    });
});


// delete school 
document.addEventListener("DOMContentLoaded", function () {
    table_school_here.addEventListener("click", function (event) {
        const icondelete = event.target.closest(".btn-delete-school");
        if (!icondelete) return;

        const deleteRow = icondelete.closest("tr");
        const deleteId = icondelete.dataset.id;

        const confirmed = confirm("Are you sure you want to delete this record?");
        if (!confirmed) return;

        fetch("../handlers/manage_election_delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "delete=deleteschool&idschool=" + encodeURIComponent(deleteId)
        })
            .then(res => res.text())
            .then(result => {
                if (result.trim() === "success") {
                    deleteRow.remove();
                    alert("Deleted successfully.");
                } else {
                    alert("Cannot delete record when the candidates registration started.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong.");
            });
    });
});


