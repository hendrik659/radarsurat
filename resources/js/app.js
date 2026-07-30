import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const inputId = button.getAttribute('aria-controls');
        const input = inputId ? document.getElementById(inputId) : null;
        const icon = button.querySelector('[data-password-icon]');

        if (!input) {
            return;
        }

        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';
        button.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        icon?.classList.toggle('fa-eye', !isHidden);
        icon?.classList.toggle('fa-eye-slash', isHidden);
    });
});

document.querySelectorAll('[data-status-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const confirmationMessage = form.dataset.statusConfirm;

        if (confirmationMessage) {
            if (!window.confirm(confirmationMessage)) {
                event.preventDefault();
            }

            return;
        }

        const action = form.dataset.statusAction;
        const userName = form.dataset.userName;

        if (!action || !userName) {
            return;
        }

        if (!window.confirm(`Yakin ingin ${action} akun ${userName}?`)) {
            event.preventDefault();
        }
    });
});
