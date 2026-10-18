function initLoginPage() {
    var password = document.getElementById('password');
    var show = document.getElementById('show');
    var donotshow = document.getElementById('donotshow');

    if (password && show && donotshow) {
        password.addEventListener('input', function() {
            if (password.value.trim() === "") {
                show.style.display = "none";
                donotshow.style.display = "none";
                password.type = "password";
            } else {
                if (password.type === "password") {
                    show.style.display = "flex";
                    donotshow.style.display = "none";
                } else {
                    show.style.display = "none";
                    donotshow.style.display = "flex";
                }
            }
        });

        show.addEventListener('click', function() {
            password.type = "text";
            show.style.display = "none";
            donotshow.style.display = "flex";
        });
        donotshow.addEventListener('click', function() {
            password.type = "password";
            donotshow.style.display = "none";
            show.style.display = "flex";
        });
    }
}

function displayPage() {
    window.location.href = "../pages/index.php";
}

window.addEventListener('DOMContentLoaded', function() {
    initLoginPage();
    var cfg = window.__PAGE_CONFIG__;
    if (!cfg) return;

    if (cfg.messageType === 'success_admin') {
        showMessage('success', '<i class="ph ph-check-circle"></i> Welcome, Admin! Login successful.', 2000);
        var u = document.getElementById('username');
        var p = document.getElementById('password');
        if (u) u.value = '';
        if (p) p.value = '';
        redirectTo('../pages/dashboard.php', 3000);
    } else if (cfg.messageType === 'success_adviser') {
        showMessage('success', '<i class="ph ph-check-circle"></i> Welcome, Adviser! Login successful.', 2000);
        var u = document.getElementById('username');
        var p = document.getElementById('password');
        if (u) u.value = '';
        if (p) p.value = '';
        redirectTo('../auth/adviser_registration_form.php', 3000);
    } else if (cfg.messageType === 'error') {
        showMessage('error', '<i class="ph ph-x-circle"></i> Incorrect password. Please try again.', 6000);
    } else if (cfg.messageType === 'notfound') {
        showMessage('error', '<i class="ph ph-x-circle"></i> Account not found. Please check or sign up.', 6000);
    } else if (cfg.messageType === 'pending_account') {
        showMessage('error', '<i class="ph ph-x-circle"></i> This account has not yet been approved by an admin.', 6000);
    } else if (cfg.messageType === 'deactivated_account') {
        showMessage('error', '<i class="ph ph-x-circle"></i> Your account is currently deactivated.', 6000);
    }
});
