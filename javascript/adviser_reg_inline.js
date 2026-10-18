window.addEventListener('DOMContentLoaded', function() {
    var cfg = window.__PAGE_CONFIG__;
    if (!cfg) return;

    if (cfg.messageType === 'success') {
        showMessage('success', '<i class="ph ph-check-circle"></i> Registration complete! You can now cast your votes.', 5000);
    } else if (cfg.messageType === 'try-again') {
        showMessage('error', '<i class="ph ph-x-circle"></i> Registration failed. Please try again.', 5000);
    }
});

function backPageRegister() {
    window.location.href = '../pages/adviser_registered_table.php';
}
