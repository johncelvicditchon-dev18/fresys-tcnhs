document.getElementById('grade-print').addEventListener('change', function() {
    const grade = this.value;
    const sectionSelect = document.getElementById('section-print');

    sectionSelect.innerHTML = '<option value="" disabled selected>Select Section</option>';
    fetch('../api/get_section.php?grade=' + encodeURIComponent(grade))
        .then(response => response.json())
        .then(data => {
            data.forEach(section => {
                const option = document.createElement('option');
                option.value = section; 
                option.textContent = section;
                sectionSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching sections:', error);
            sectionSelect.innerHTML = '<option value="" disabled selected>Error loading sections</option>';
        });
});