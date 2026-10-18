window.addEventListener('DOMContentLoaded', function() {
    var cfg = window.__PAGE_CONFIG__;
    if (!cfg) return;
    if (cfg.messageType === 'success') {
        alert("Registered Successfully");
    } else if (cfg.messageType === 'error') {
        alert("Error: Please try again");
    }
});
