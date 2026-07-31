(() => {
  const fileInput = document.querySelector('#video-file');
  const urlInput = document.querySelector('#form-video-url');
  const removeInput = document.querySelector('#remove-video');
  const form = document.querySelector('#draft-form');
  const progressContainer = document.querySelector('#video-upload-progress');
  const progressBar = document.querySelector('#video-upload-progress-bar');
  const progressLabel = progressContainer?.querySelector('[data-progress-label]');
  const progressMessage = progressContainer?.querySelector('[data-progress-message]');
  const maxFileBytes = 500 * 1024 * 1024;
  const maxRequestBytes = Number(form?.dataset.maxRequestBytes || maxFileBytes);

  if (!fileInput || !urlInput) {
    return;
  }

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      urlInput.value = '';
      if (removeInput) removeInput.checked = false;
    }
  });

  urlInput.addEventListener('input', () => {
    if (urlInput.value.trim() !== '') {
      fileInput.value = '';
      if (removeInput) removeInput.checked = false;
    }
  });

  if (removeInput) {
    removeInput.addEventListener('change', () => {
      fileInput.disabled = removeInput.checked;
      urlInput.disabled = removeInput.checked;
    });
  }

  if (!form || !progressContainer || !progressBar) {
    return;
  }

  const enableSubmitButtons = () => {
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
      button.disabled = false;
    });
  };

  const showError = (message) => {
    progressContainer.classList.remove('d-none');
    progressBar.value = 0;
    progressBar.textContent = '0%';
    if (progressLabel) progressLabel.textContent = '0%';
    if (progressMessage) progressMessage.textContent = message;
    enableSubmitButtons();
  };

  form.addEventListener('submit', (event) => {
    const selectedFiles = [...form.querySelectorAll('input[type="file"]')]
      .flatMap((input) => [...input.files]);
    if (selectedFiles.length === 0) {
      return;
    }

    event.preventDefault();
    const oversized = selectedFiles.find((file) => file.size > maxFileBytes);
    if (oversized) {
      showError(`${oversized.name} excede el límite de 500 MB.`);
      return;
    }

    const totalFileBytes = selectedFiles.reduce((total, file) => total + file.size, 0);
    if (totalFileBytes > maxRequestBytes) {
      showError('Los archivos seleccionados suman más de 500 MB. Guarda primero algunos documentos y después carga los restantes o el video.');
      return;
    }

    const submitter = event.submitter;
    const data = new FormData(form);
    if (submitter?.name) data.set(submitter.name, submitter.value);

    const request = new XMLHttpRequest();
    request.open('POST', form.action);
    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    request.setRequestHeader('Accept', 'application/json');
    progressContainer.classList.remove('d-none');
    if (progressMessage) progressMessage.textContent = 'No cierres esta ventana durante la carga.';
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
      button.disabled = true;
    });

    request.upload.addEventListener('progress', (progressEvent) => {
      if (!progressEvent.lengthComputable) return;
      const percent = Math.round((progressEvent.loaded / progressEvent.total) * 100);
      progressBar.value = percent;
      progressBar.textContent = `${percent}%`;
      if (progressLabel) progressLabel.textContent = `${percent}%`;
    });

    request.addEventListener('load', () => {
      let response = null;
      try {
        response = JSON.parse(request.responseText);
      } catch (error) {
        response = null;
      }

      if (response?.csrf?.name && response?.csrf?.hash) {
        const csrfInput = form.elements.namedItem(response.csrf.name);
        if (csrfInput instanceof HTMLInputElement) {
          csrfInput.value = response.csrf.hash;
        }
      }

      if (request.status >= 200 && request.status < 300 && response?.ok && response.redirect) {
        const redirect = new URL(response.redirect, window.location.origin);
        if (redirect.origin === window.location.origin && redirect.pathname.includes('/participante/')) {
          window.location.assign(redirect.href);
          return;
        }
      }

      if (response?.message) {
        const details = response.errors ? Object.values(response.errors).filter(Boolean) : [];
        showError(details.length > 0 ? `${response.message} ${details[0]}` : response.message);
      } else if (request.status === 403) {
        showError('La sesión o el formulario expiró. Recarga la página antes de volver a intentarlo.');
      } else if (request.status === 413) {
        showError('El tamaño total excede el límite del servidor. Guarda los documentos y el video en varias cargas de máximo 500 MB cada una.');
      } else {
        showError('No fue posible guardar los archivos. Revisa tu sesión e inténtalo nuevamente.');
      }
    });

    request.addEventListener('timeout', () => {
      showError('La carga tardó demasiado. Verifica tu conexión antes de volver a intentarlo.');
    });

    request.addEventListener('abort', () => {
      showError('La carga fue cancelada. Puedes volver a intentarlo.');
    });

    request.addEventListener('error', () => {
      showError('La carga se interrumpió. Puedes volver a intentarlo sin perder el borrador guardado.');
    });

    request.send(data);
  });
})();
