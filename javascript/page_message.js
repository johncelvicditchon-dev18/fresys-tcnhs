function showMessage(elementId, html, duration) {
    var el = document.getElementById(elementId);
    if (!el) return;
    el.style.display = 'flex';
    el.innerHTML = html;
    setTimeout(function() { el.style.display = 'none'; }, duration || 6000);
}

function clearField() {
    for (var i = 0; i < arguments.length; i++) {
        var el = document.getElementById(arguments[i]);
        if (el) el.value = '';
    }
}

function redirectTo(url, delay) {
    setTimeout(function() { window.location.href = url; }, delay || 3000);
}
