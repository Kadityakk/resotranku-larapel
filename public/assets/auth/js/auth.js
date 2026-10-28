/**
 * Password visibility toggles on the sign-in screens.
 *
 * Each toggle names its input through `data-password-toggle="#selector"`.
 */
(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-password-toggle]');

        if (!toggle) {
            return;
        }

        var input = document.querySelector(toggle.getAttribute('data-password-toggle'));

        if (!input) {
            return;
        }

        var revealing = input.type === 'password';

        input.type = revealing ? 'text' : 'password';
        toggle.setAttribute('aria-label', revealing ? 'Sembunyikan password' : 'Tampilkan password');

        var icon = toggle.querySelector('i');

        if (icon) {
            icon.classList.toggle('fa-eye', !revealing);
            icon.classList.toggle('fa-eye-slash', revealing);
        }
    });
})();
