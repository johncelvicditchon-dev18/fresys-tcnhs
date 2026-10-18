
const student_card = document.getElementById('student-card');
const student_card_print = document.getElementById('student-card-print');
const account_card = document.getElementById('account-card');
const overview_card = document.getElementById('overview-card');
const overview = document.getElementById('overview');
const candidate_card = document.getElementById('candidate-card');
const vote_status_card = document.getElementById('student-card-vote-status');
const manage_e_one = document.getElementById('manage-e-one');
const manage_e_two = document.getElementById('manage-e-two');
const results_card = document.getElementById('results-card');
const bulk_import_card = document.getElementById('bulk-import-card');
const election_history_card = document.getElementById('election-history-card');
const ballot_preview_card = document.getElementById('ballot-preview-card');

const registered = document.getElementById('registered');
const printstudent = document.getElementById('printstudent');
const votestudentcasted = document.getElementById('votestudentcasted');
const manage_election = document.getElementById('manage_election');
const candidate_click = document.getElementById('candidate-sidebar');
const gotoresult = document.getElementById('gotoresult');
const bulkimport = document.getElementById('bulkimport');
const electionhistory = document.getElementById('election-history');

function hideAllSections() {
    candidate_card.style.display = "none";
    manage_e_one.style.display = "none";
    manage_e_two.style.display = "none";
    vote_status_card.style.display = "none";
    student_card.style.display = "none";
    overview_card.style.display = "none";
    account_card.style.display = "none";
    student_card_print.style.display = "none";
    results_card.style.display = "none";
    bulk_import_card.style.display = "none";
    election_history_card.style.display = "none";
    ballot_preview_card.style.display = "none";
}

gotoresult.addEventListener('click', () => {
    hideAllSections();
    results_card.style.display = "block";
});

candidate_click.addEventListener('click', () => {
    hideAllSections();
    candidate_card.style.display = "block";
});

manage_election.addEventListener('click', () => {
    hideAllSections();
    manage_e_one.style.display = "grid";
    manage_e_two.style.display = "grid";
});

votestudentcasted.addEventListener('click', () => {
    hideAllSections();
    vote_status_card.style.display = "block";
});

registered.addEventListener('click', () => {
    hideAllSections();
    student_card.style.display = "block";
});

printstudent.addEventListener('click', () => {
    hideAllSections();
    student_card_print.style.display = "block";
});

document.getElementById('overview').addEventListener('click', () => {
    hideAllSections();
    overview_card.style.display = "block";
});

document.getElementById('account').addEventListener('click', () => {
    hideAllSections();
    account_card.style.display = "block";
});

bulkimport.addEventListener('click', () => {
    hideAllSections();
    bulk_import_card.style.display = "block";
});

electionhistory.addEventListener('click', () => {
    hideAllSections();
    election_history_card.style.display = "block";
    loadElectionHistory();
});

const hamburger = document.querySelector('.hamburger');
const sidebar = document.querySelector('.sidebar');
const layout = document.querySelector('.layout');

hamburger.addEventListener('click', () => {
    if (window.innerWidth > 768) {
        sidebar.classList.toggle('collapsed');
        layout.classList.toggle('sidebar-collapsed');
    } else {
        sidebar.classList.toggle('active');
    }
});

const studentLi = document.getElementById("student");
studentLi.addEventListener("click", () => {
    studentLi.classList.toggle('active');
});

const dropdownItems = document.querySelectorAll('.dropdown li');
dropdownItems.forEach(item => {
    item.addEventListener('click', function (e) {
        e.stopPropagation();
    });
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
