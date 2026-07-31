(function () {
    'use strict';

    const modal = document.getElementById('document-preview-modal');
    if (!modal) {
        return;
    }

    const title = modal.querySelector('#document-preview-title');
    const image = modal.querySelector('[data-preview-image]');
    const frame = modal.querySelector('[data-preview-frame]');
    const loading = modal.querySelector('[data-preview-loading]');
    const unsupported = modal.querySelector('[data-preview-unsupported]');
    const openLink = modal.querySelector('[data-preview-open]');

    const reset = function () {
        image.classList.add('d-none');
        frame.classList.add('d-none');
        unsupported.classList.add('d-none');
        loading.classList.remove('d-none');
        image.removeAttribute('src');
        image.alt = '';
        frame.src = 'about:blank';
    };

    document.querySelectorAll('[data-document-preview]').forEach(function (button) {
        button.addEventListener('click', function () {
            const url = button.dataset.previewUrl || '';
            const mime = (button.dataset.previewMime || '').toLowerCase();
            const name = button.dataset.previewName || 'Documento';

            reset();
            title.textContent = name;
            openLink.href = url;

            if (mime === 'application/pdf') {
                frame.title = 'Vista previa de ' + name;
                frame.src = url;
                frame.classList.remove('d-none');
            } else if (mime === 'image/jpeg' || mime === 'image/jpg') {
                image.alt = 'Vista previa de ' + name;
                image.src = url;
                image.classList.remove('d-none');
            } else {
                loading.classList.add('d-none');
                unsupported.classList.remove('d-none');
            }
        });
    });

    image.addEventListener('load', function () {
        loading.classList.add('d-none');
    });
    image.addEventListener('error', function () {
        loading.classList.add('d-none');
        image.classList.add('d-none');
        unsupported.classList.remove('d-none');
    });
    frame.addEventListener('load', function () {
        if (frame.src !== 'about:blank') {
            loading.classList.add('d-none');
        }
    });
    modal.addEventListener('hidden.bs.modal', reset);
}());
