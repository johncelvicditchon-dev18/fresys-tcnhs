function backtoDash() {
    window.location.href = "../pages/dashboard.php";
}

function filterTable() {
    const searchInput = document.getElementById('search').value.toLowerCase();
    const rows = document.querySelectorAll('#table-body tr');

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 1) return;

        const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
        row.style.display = rowText.includes(searchInput) ? '' : 'none';
    });
}

function toggleDropdown(e) {
    e.stopPropagation();
    document.querySelector('.header-profile').classList.toggle('open');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.header-profile')) {
        document.querySelector('.header-profile').classList.remove('open');
    }
});
