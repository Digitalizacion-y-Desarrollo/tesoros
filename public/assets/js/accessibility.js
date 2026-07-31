(function () {
    'use strict';

    const root = document.documentElement;
    const storageKey = 'tesoros-accessibility';
    const allowedModes = ['high-contrast', 'grayscale', 'underline-links', 'readable-font', 'reduce-motion'];
    let preferences = { fontScale: 1, modes: [] };

    try {
        preferences = Object.assign(preferences, JSON.parse(localStorage.getItem(storageKey) || '{}'));
    } catch (error) {
        preferences = { fontScale: 1, modes: [] };
    }

    function applyPreferences() {
        ['90', '100', '110', '120', '130'].forEach(function (size) {
            root.classList.remove('a11y-font-' + size);
        });
        root.classList.add('a11y-font-' + String(Math.round(Number(preferences.fontScale) * 100)));
        allowedModes.forEach(function (mode) {
            root.classList.toggle('a11y-' + mode, preferences.modes.includes(mode));
        });

        document.querySelectorAll('[data-a11y-toggle]').forEach(function (button) {
            const active = preferences.modes.includes(button.dataset.a11yToggle);
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', String(active));
        });
    }

    function savePreferences() {
        localStorage.setItem(storageKey, JSON.stringify(preferences));
        applyPreferences();
    }

    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-a11y-toggle]');
        const action = event.target.closest('[data-a11y-action]');

        if (toggle) {
            const mode = toggle.dataset.a11yToggle;
            preferences.modes = preferences.modes.includes(mode)
                ? preferences.modes.filter(function (item) { return item !== mode; })
                : preferences.modes.concat(mode);
            savePreferences();
        }

        if (!action) {
            return;
        }

        if (action.dataset.a11yAction === 'font-increase') {
            preferences.fontScale = Math.min(1.3, Number(preferences.fontScale) + 0.1);
        } else if (action.dataset.a11yAction === 'font-decrease') {
            preferences.fontScale = Math.max(0.9, Number(preferences.fontScale) - 0.1);
        } else if (action.dataset.a11yAction === 'reset') {
            preferences = { fontScale: 1, modes: [] };
        }

        savePreferences();
    });

    applyPreferences();
}());
