(() => {
    const button = document.querySelector('#resend-code');

    if (!button) {
        return;
    }

    let remaining = Number.parseInt(button.dataset.retrySeconds || '0', 10);
    const label = 'Reenviar código';

    const render = () => {
        if (remaining <= 0) {
            button.disabled = false;
            button.textContent = label;
            return;
        }

        button.disabled = true;
        button.textContent = `${label} (${remaining} s)`;
        remaining -= 1;
        window.setTimeout(render, 1000);
    };

    render();
})();
