const grade = document.getElementById('grade');
const sec = document.getElementById('section');


const previousSection = sec.dataset.selectedSection;

function loadSections(selectedGrade, preselectSection = '') {
    sec.innerHTML = '<option disabled selected>Loading...</option>';

    fetch("../api/get_all_section.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "gradeselected=" + encodeURIComponent(selectedGrade)
    })
        .then(res => res.json())
        .then(sections => {
            sec.innerHTML = '<option disabled selected value="">Select Section</option>';

            if (sections.length === 0) {
                sec.innerHTML += '<option disabled>No sections found</option>';
                return;
            }
            sections.forEach(sec_added => {
                const option = document.createElement('option');
                option.value = sec_added;
                option.textContent = sec_added;
                if (sec_added === preselectSection) {
                    option.selected = true;
                }
                sec.appendChild(option);
            });
        })
        .catch(err => {
            console.error(err);
            sec.innerHTML = '<option disabled>Error loading sections</option>';
        });
}

if (grade.value) {
    loadSections(grade.value, previousSection);
}
grade.addEventListener('change', function () {
    loadSections(this.value);
});
