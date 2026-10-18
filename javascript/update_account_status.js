
let row = null, id_account = null;

table_account.addEventListener('click', function (event) {
    const button_update = event.target.closest('.status-btn');
    if (!button_update) return;

    row = button_update.closest('tr');
    id_account = button_update.dataset.id;

    const current_status = button_update.textContent.trim();
    let newStatus = "";

    if (current_status === "Pending") {
        newStatus = "Approved";
    } else if (current_status === "Approved") {
        newStatus = "Deactivated";
    }
    else if (current_status === "Deactivated") {
        newStatus = "Approved";
    }

    button_update.textContent = newStatus;

    button_update.style.background = (newStatus === "Approved") ? "#10b981" : "#ff5252";

    fetch("../handlers/update_account_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id_account}&status=${newStatus}`
    })
        .then(res => res.text())
        .then(data => console.log(data))
        .catch(err => console.error(err));
});
