/**
 * central_box — Auth JavaScript
 * Login/Register form validation, password toggle, demo fill
 */

document.addEventListener('DOMContentLoaded', function() {
    initPasswordToggle();
    initPasswordStrength();
    initFormValidation();
});

/* ============================================================
   Password visibility toggle
   ============================================================ */
function initPasswordToggle() {
    const toggle = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (!toggle || !passwordInput) return;

    toggle.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';

        // Update icon
        const icon = toggle.querySelector('i');
        if (icon) {
            icon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
            if (window.lucide) lucide.createIcons();
        }
    });
}

/* ============================================================
   Password strength meter (register only)
   ============================================================ */
function initPasswordStrength() {
    const passwordInput = document.getElementById('password');
    const strengthFill  = document.getElementById('strengthFill');
    const strengthText  = document.getElementById('strengthText');

    if (!passwordInput || !strengthFill || !strengthText) return;

    passwordInput.addEventListener('input', function() {
        const value = this.value;
        let score = 0;

        if (value.length >= 6) score++;
        if (value.length >= 10) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const levels = [
            { width: '0%',   color: 'transparent',        text: '' },
            { width: '20%',  color: 'var(--color-danger)', text: 'Muy débil' },
            { width: '40%',  color: 'var(--color-danger)', text: 'Débil' },
            { width: '60%',  color: 'var(--color-warning)', text: 'Aceptable' },
            { width: '80%',  color: 'var(--color-info)',    text: 'Buena' },
            { width: '100%', color: 'var(--color-success)', text: 'Excelente' },
        ];

        const level = levels[score] || levels[0];
        strengthFill.style.width = level.width;
        strengthFill.style.background = level.color;
        strengthText.textContent = level.text;
        strengthText.style.color = level.color;
    });
}

/* ============================================================
   Form validation
   ============================================================ */
function initFormValidation() {
    // Register form — password match
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm  = document.getElementById('password_confirm').value;

            if (password !== confirm) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
                document.getElementById('password_confirm').focus();
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres.');
                document.getElementById('password').focus();
                return false;
            }
        });
    }

    // Add visual feedback to inputs
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            if (this.value.trim() !== '' && this.checkValidity()) {
                this.style.borderColor = 'var(--color-success)';
            } else if (this.value.trim() !== '' && !this.checkValidity()) {
                this.style.borderColor = 'var(--color-danger)';
            } else {
                this.style.borderColor = '';
            }
        });

        input.addEventListener('focus', function() {
            this.style.borderColor = '';
        });
    });
}

/* ============================================================
   Fill demo credentials (login page)
   ============================================================ */
function fillDemo(email, password) {
    const emailInput    = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    if (emailInput) {
        emailInput.value = email;
        emailInput.style.borderColor = 'var(--color-success)';
    }
    if (passwordInput) {
        passwordInput.value = password;
        passwordInput.style.borderColor = 'var(--color-success)';
    }
}
