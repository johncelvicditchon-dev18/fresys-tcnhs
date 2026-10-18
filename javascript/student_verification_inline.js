function displayPage() {
    window.location.href = "../pages/index.php";
}

window.addEventListener('DOMContentLoaded', function() {
    var cfg = window.__PAGE_CONFIG__;
    if (!cfg) return;

    if (cfg.messageType === 'success') {
        showMessage('success', '<i class="ph ph-check-circle"></i> Verified! Redirecting to ballots...', 6000);
    } else if (cfg.messageType === 'error') {
        showMessage('error', '<i class="ph ph-x-circle"></i> Invalid verification code or grade level. Please try again.', 6000);
    } else if (cfg.messageType === 'alreadycasted') {
        showMessage('error', '<i class="ph ph-x-circle"></i> This verification code has already been used.', 6000);
    }
});
