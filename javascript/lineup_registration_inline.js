function previewPhoto(input, containerId) {
    var container = document.getElementById(containerId);
    var file = input.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var existingImg = container.querySelector('img');
        if (existingImg) existingImg.remove();
        var img = document.createElement('img');
        img.src = e.target.result;
        container.prepend(img);
        container.querySelector('i').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

document.getElementById('lineupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var inputs = this.querySelectorAll('input[type="text"]');
    inputs.forEach(function(input) { input.value = input.value.toUpperCase(); });

    var slots = document.querySelectorAll('.candidate-slot');
    var errors = [];
    slots.forEach(function(slot) {
        var nameInput = slot.querySelector('input[type="text"]');
        var fileInput = slot.querySelector('input[type="file"]');
        if (!nameInput || !fileInput) return;
        var name = nameInput.value.trim();
        var hasFile = fileInput.files && fileInput.files.length > 0;
        if (name && !hasFile) {
            var pos = slot.closest('.position-card');
            var posName = pos ? pos.querySelector('.pos-name').textContent : 'Unknown';
            errors.push(posName + ': Photo is required when name is filled.');
            fileInput.style.borderColor = '#EF4444';
        } else if (hasFile && !name) {
            var pos = slot.closest('.position-card');
            var posName = pos ? pos.querySelector('.pos-name').textContent : 'Unknown';
            errors.push(posName + ': Name is required when photo is uploaded.');
            nameInput.style.borderColor = '#EF4444';
        } else {
            fileInput.style.borderColor = '';
            nameInput.style.borderColor = '';
        }
    });

    if (errors.length > 0) {
        var msg = document.getElementById('resultMsg');
        msg.className = 'result-msg error';
        msg.innerHTML = '<i class="ph ph-x-circle"></i> ' + errors.join('<br>');
        return;
    }

    var btn = document.getElementById('submitBtn');
    var msg = document.getElementById('resultMsg');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner" style="margin-right:6px;"></i>Registering...';

    var formData = new FormData(this);

    fetch('../handlers/lineup_register_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) {
        if (!r.ok) throw new Error('Server returned HTTP ' + r.status);
        return r.json();
    })
    .then(function(res) {
        msg.className = 'result-msg ' + (res.status === 'success' ? 'success' : 'error');
        msg.innerHTML = (res.status === 'success' ? '<i class="ph ph-check-circle"></i> ' : '<i class="ph ph-x-circle"></i> ') + res.message;
        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-rocket-launch" style="margin-right:6px;"></i>Register Entire Lineup';
        if (res.status === 'success') {
            setTimeout(function() { location.reload(); }, 2000);
        }
    })
    .catch(function(err) {
        msg.className = 'result-msg error';
        msg.innerHTML = '<i class="ph ph-x-circle"></i> Failed: ' + err.message;
        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-rocket-launch" style="margin-right:6px;"></i>Register Entire Lineup';
    });
});
