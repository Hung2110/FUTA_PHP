document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';
            }
        });
    });

    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordInput = document.getElementById('passwordInput');
            const icon = this.querySelector('i');
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }

    // Toggle current password visibility
    const btnToggleCurrentPass = document.getElementById('btnToggleCurrentPass');
    if (btnToggleCurrentPass) {
        btnToggleCurrentPass.addEventListener('click', function() {
            const masked = document.getElementById('currentPassMasked');
            const text = document.getElementById('currentPassText');
            const icon = this.querySelector('i');
            const isHidden = text.classList.contains('d-none');
            if (isHidden) {
                text.classList.remove('d-none');
                masked.classList.add('d-none');
                icon.className = 'fas fa-eye-slash';
            } else {
                text.classList.add('d-none');
                masked.classList.remove('d-none');
                icon.className = 'fas fa-eye';
            }
        });
    }
});

