const form = document.getElementById('registerForm');
if (form) {
    form.addEventListener('submit', function (ev) {
        const name = form.full_name.value.trim();
        const email = form.email.value.trim();
        const pass = form.password.value;
        const confirm = form.confirm_password.value;
        const out = document.getElementById('clientError');
        let msg = '';

        if (!name) msg = 'Full name is required.';
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) msg = 'Enter a valid email address.';
        else if (pass.length < 8) msg = 'Password must be at least 8 characters.';
        else if (pass !== confirm) msg = 'Passwords do not match.';

        out.textContent = msg;
        if (msg) ev.preventDefault();
    });
}

document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
        if (!confirm(f.getAttribute('data-confirm'))) ev.preventDefault();
    });
});
