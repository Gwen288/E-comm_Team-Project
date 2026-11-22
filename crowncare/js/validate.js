/*
 * Live help on password fields (sign up, reset password, change password):
 * ticks each password rule as it is met, and checks that the two passwords match.
 * The server checks the same rules (core/validation.php).
 */
(function () {
    'use strict';

    var rules = {
        length: function (v) { return v.length >= 8; },
        upper: function (v) { return /[A-Z]/.test(v); },
        lower: function (v) { return /[a-z]/.test(v); },
        number: function (v) { return /[0-9]/.test(v); },
        symbol: function (v) { return /[^A-Za-z0-9]/.test(v); }
    };

    document.querySelectorAll('input[data-password-rules]').forEach(function (input) {
        var list = document.getElementById(input.dataset.passwordRules);
        if (!list) return;

        function update() {
            list.querySelectorAll('[data-rule]').forEach(function (item) {
                var check = rules[item.dataset.rule];
                item.classList.toggle('is-met', !!check && check(input.value));
            });
        }

        input.addEventListener('input', update);
        update();
    });

    document.querySelectorAll('input[data-match]').forEach(function (confirmInput) {
        var original = document.getElementById(confirmInput.dataset.match);
        var holder = confirmInput.form
            ? confirmInput.form.querySelector('[data-error-for="' + confirmInput.name + '"]')
            : null;
        if (!original) return;

        function check() {
            if (confirmInput.value === '') {
                if (holder) holder.textContent = '';
                confirmInput.removeAttribute('aria-invalid');
                return;
            }
            var same = confirmInput.value === original.value;
            if (holder) holder.textContent = same ? '' : 'The two passwords do not match.';
            if (same) {
                confirmInput.removeAttribute('aria-invalid');
            } else {
                confirmInput.setAttribute('aria-invalid', 'true');
            }
        }

        confirmInput.addEventListener('input', check);
        original.addEventListener('input', function () {
            if (confirmInput.value !== '') check();
        });
    });
})();
