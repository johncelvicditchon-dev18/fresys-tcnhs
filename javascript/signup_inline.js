function initSignupPage() {
    var password = document.getElementById('password');
    var show = document.getElementById('show');
    var donotshow = document.getElementById('donotshow');

    if (password && show && donotshow) {
        password.addEventListener('input', function() {
            if (password.value.trim() === "") {
                show.style.display = "none";
                donotshow.style.display = "none";
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
    initSignupPage();
    var cfg = window.__PAGE_CONFIG__;
    if (!cfg) return;

    if (cfg.messageType === 'success') {
        showMessage('success', '<i class="ph ph-check-circle"></i>Account created successfully!', 6000);
        var t = document.getElementById('teachername');
        var u = document.getElementById('username');
        var r = document.getElementById('role');
        var p = document.getElementById('password');
        if (t) t.value = '';
        if (u) u.value = '';
        if (r) r.value = '';
        if (p) p.value = '';
    } else if (cfg.messageType === 'error') {
        showMessage('error', '<i class="ph ph-x-circle"></i> This username is already taken. Please choose another.', 6000);
    } else if (cfg.messageType === 'try') {
        showMessage('error', '<i class="ph ph-x-circle"></i> Login failed. Please check your credentials and try again.', 6000);
    }
});
