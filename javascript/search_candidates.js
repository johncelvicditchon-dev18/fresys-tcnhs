const search_candidates = document.getElementById('search-candidate');
const tableBody_candidate = document.getElementById('table-body-candidate');

search_candidates.addEventListener('input', function () {
    const candidate = this.value;

    fetch(`../api/search_candidates.php?search-candidate=${encodeURIComponent(candidate)}`)
        .then(response => response.text())
        .then(data => {
            tableBody_candidate.innerHTML = data;
        });
});
