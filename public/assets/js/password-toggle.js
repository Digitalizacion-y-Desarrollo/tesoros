(function () {
    'use strict';

    document.querySelectorAll('[data-password-field]').forEach(function (field) {
        var input = field.querySelector('[data-password-input]');
        var toggle = field.querySelector('[data-password-toggle]');

        if (!input || !toggle) {
            return;
        }

        var labelShow = toggle.dataset.labelShow || 'Mostrar';
        var labelHide = toggle.dataset.labelHide || 'Ocultar';

        toggle.hidden = false;
        field.classList.add('auth-password-toggleable');

        toggle.addEventListener('click', function () {
            var visible = input.type === 'text';

            input.type = visible ? 'password' : 'text';
            toggle.textContent = visible ? labelShow : labelHide;
            toggle.setAttribute('aria-pressed', visible ? 'false' : 'true');
            input.focus();
        });
    });
})();
