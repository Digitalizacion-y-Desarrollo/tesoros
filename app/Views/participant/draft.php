<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$application = $context['application'];
$category = $context['category'];
$participants = old('participants');
$participants = is_array($participants) ? array_values($participants) : $context['participants'];
$formValues = old('form');
$formValues = is_array($formValues) ? $formValues : $context['form'];
$email = old('email');
$email = $email !== null ? (string) $email : (string) $application['email'];
$video = $context['video'];
?>
<section class="application-shell py-5">
    <div class="container-xxl">
        <div class="row g-4">
            <aside class="col-lg-4">
                <div class="application-aside">
                    <p class="eyebrow mb-2">Folio</p>
                    <p class="application-folio"><?= esc($application['folio']) ?></p>
                    <h1 class="h3 font-display text-wine-dark mt-4"><?= esc($category['name']) ?></h1>
                    <div class="application-step-list mt-4" aria-label="Etapas del registro">
                        <span class="complete">1. Identificación</span>
                        <span class="active">2. Formulario</span>
                        <span>3. Resumen y envío</span>
                    </div>
                    <p class="form-hint mt-4">Puedes guardar aunque falten campos. Todos serán obligatorios antes del envío, excepto los identificados como opcionales.</p>
                </div>
            </aside>

            <div class="col-lg-8">
                <div class="application-card">
                    <h2 class="h3 font-display text-wine-dark">Completa tu solicitud</h2>
                    <?php if ($context['convocation']['closed']): ?>
                        <div class="alert alert-warning mt-3" role="alert">
                            La convocatoria está cerrada. Ya no es posible guardar ni enviar borradores; la consulta y las correcciones expresamente habilitadas continúan disponibles.
                        </div>
                    <?php endif ?>
                    <?php if ($errors !== []): ?>
                        <div class="alert alert-danger mt-3" role="alert">
                            Revisa los campos señalados. El borrador no se modificó.
                        </div>
                    <?php endif ?>

                    <form id="draft-form" method="post" action="<?= url_to('participant.draft.save') ?>" class="mt-4" enctype="multipart/form-data" data-max-request-bytes="524288000">
                        <?= csrf_field() ?>
                        <input type="hidden" name="MAX_FILE_SIZE" value="524288000">
                        <fieldset>
                            <legend class="h4 font-display text-wine-dark">Datos de contacto</legend>
                            <label class="form-label" for="draft-email">Correo electrónico *</label>
                            <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="draft-email" name="email" type="email" maxlength="254" value="<?= esc($email) ?>" required>
                            <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif ?>
                        </fieldset>

                        <?php foreach ($participants as $index => $person): ?>
                            <fieldset class="border-top pt-4 mt-4">
                                <legend class="h4 font-display text-wine-dark">
                                    <?php if ($category['code'] === 'joven-talento-gastronomia' && $index === 0): ?>
                                        Persona participante
                                    <?php else: ?>
                                        <?= $index === 0 ? 'Persona responsable' : 'Integrante adicional' ?>
                                    <?php endif ?>
                                </legend>
                                <div class="row g-3">
                                    <?php
                                    $participantFields = [
                                        'first_name' => ['Nombre(s)', 100],
                                        'last_name' => ['Primer apellido', 150],
                                        'second_last_name' => ['Segundo apellido', 150],
                                        'curp' => ['CURP', 18],
                                    ];
                                    ?>
                                    <?php foreach ($participantFields as $name => [$label, $max]): ?>
                                        <?php $errorKey = "participants.{$index}.{$name}"; ?>
                                        <div class="col-md-6">
                                            <label class="form-label" for="<?= $name ?>-<?= $index ?>"><?= esc($label) ?><?= $name !== 'second_last_name' ? ' *' : '' ?></label>
                                            <input
                                                class="form-control <?= isset($errors[$errorKey]) ? 'is-invalid' : '' ?> <?= $name === 'curp' ? 'text-uppercase' : '' ?>"
                                                id="<?= $name ?>-<?= $index ?>"
                                                name="participants[<?= $index ?>][<?= $name ?>]"
                                                maxlength="<?= $max ?>"
                                                value="<?= esc((string) ($person[$name] ?? '')) ?>"
                                                <?= $name !== 'second_last_name' ? 'required' : '' ?>
                                            >
                                            <?php if (isset($errors[$errorKey])): ?><div class="invalid-feedback"><?= esc($errors[$errorKey]) ?></div><?php endif ?>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </fieldset>
                        <?php endforeach ?>

                        <fieldset class="border-top pt-4 mt-4">
                            <legend class="h4 font-display text-wine-dark">Información de la categoría</legend>
                            <div class="row g-3">
                                <?php foreach ($context['definition']['fields'] as $field): ?>
                                    <?php
                                    $name = (string) $field['name'];
                                    $errorKey = "form.{$name}";
                                    $value = (string) ($formValues[$name] ?? '');
                                    $column = in_array($field['type'], ['textarea', 'video'], true) ? 'col-12' : 'col-md-6';
                                    ?>
                                    <div class="<?= $column ?>">
                                        <?php if ($field['type'] === 'video'): ?>
                                            <fieldset class="video-choice border rounded-3 p-3">
                                                <legend class="form-label fw-semibold px-2">
                                                    <?= esc($field['label']) ?><?= ($context['definition']['video_required'] ?? false) ? ' *' : '' ?>
                                                </legend>
                                                <?php if ($video !== null): ?>
                                                    <div class="alert alert-info py-2" role="status">
                                                        <?php if ($video['source_type'] === 'file'): ?>
                                                            Video guardado: <strong><?= esc($video['original_name']) ?></strong>
                                                            (<?= number_format(((int) $video['size_bytes']) / 1048576, 1) ?> MB)
                                                        <?php else: ?>
                                                            Actualmente se utiliza un enlace HTTPS.
                                                        <?php endif ?>
                                                    </div>
                                                <?php endif ?>

                                                <p class="form-hint fw-semibold mb-3">
                                                    Elige solamente una opción: pega una URL HTTPS o sube un archivo MP4. Si utilizas la URL, no selecciones un archivo; si subes un archivo, deja vacía la URL.
                                                </p>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="form-video-url">Enlace HTTPS del video</label>
                                                        <input
                                                            class="form-control <?= isset($errors[$errorKey]) ? 'is-invalid' : '' ?>"
                                                            id="form-video-url"
                                                            name="form[video_url]"
                                                            type="url"
                                                            maxlength="2048"
                                                            placeholder="https://..."
                                                            value="<?= esc($value) ?>"
                                                        >
                                                        <?php if (isset($errors[$errorKey])): ?><div class="invalid-feedback"><?= esc($errors[$errorKey]) ?></div><?php endif ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="video-file">Archivo de video MP4</label>
                                                        <input
                                                            class="form-control <?= isset($errors['form.video_file']) ? 'is-invalid' : '' ?>"
                                                            id="video-file"
                                                            name="video_file"
                                                            type="file"
                                                            accept="video/mp4,.mp4"
                                                        >
                                                        <?php if (isset($errors['form.video_file'])): ?><div class="invalid-feedback"><?= esc($errors['form.video_file']) ?></div><?php endif ?>
                                                    </div>
                                                </div>
                                                <p class="form-hint mt-2 mb-0">
                                                    <?= esc($context['definition']['video_help'] ?? 'Puedes proporcionar el video como archivo MP4 o mediante un enlace HTTPS.') ?>
                                                    El archivo MP4 puede pesar hasta 500 MB y se almacenará de forma privada.
                                                </p>
                                                <?php if ($video !== null): ?>
                                                    <div class="form-check mt-3">
                                                        <input class="form-check-input" id="remove-video" name="remove_video" type="checkbox" value="1">
                                                        <label class="form-check-label" for="remove-video">Eliminar el video guardado</label>
                                                    </div>
                                                <?php endif ?>
                                            </fieldset>
                                        <?php else: ?>
                                        <label class="form-label" for="form-<?= esc($name) ?>">
                                            <?= esc($field['label']) ?><?= ($field['required'] ?? false) ? ' *' : '' ?>
                                        </label>
                                        <?php if ($field['type'] === 'textarea'): ?>
                                            <textarea
                                                class="form-control <?= isset($errors[$errorKey]) ? 'is-invalid' : '' ?>"
                                                id="form-<?= esc($name) ?>"
                                                name="form[<?= esc($name) ?>]"
                                                rows="5"
                                                maxlength="<?= (int) ($field['max'] ?? 5000) ?>"
                                                <?= ($field['required'] ?? false) ? 'required' : '' ?>
                                            ><?= esc($value) ?></textarea>
                                        <?php elseif ($field['type'] === 'select'): ?>
                                            <select
                                                class="form-select <?= isset($errors[$errorKey]) ? 'is-invalid' : '' ?>"
                                                id="form-<?= esc($name) ?>"
                                                name="form[<?= esc($name) ?>]"
                                                <?= ($field['required'] ?? false) ? 'required' : '' ?>
                                            >
                                                <option value="">Selecciona una opción</option>
                                                <?php foreach ($field['options'] ?? [] as $option): ?>
                                                    <option value="<?= esc($option, 'attr') ?>" <?= $value === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        <?php else: ?>
                                            <input
                                                class="form-control <?= isset($errors[$errorKey]) ? 'is-invalid' : '' ?>"
                                                id="form-<?= esc($name) ?>"
                                                name="form[<?= esc($name) ?>]"
                                                type="<?= esc($field['type']) ?>"
                                                value="<?= esc($value) ?>"
                                                <?= $name === 'municipality' ? 'list="edomex-municipalities"' : '' ?>
                                                <?= isset($field['max']) ? 'maxlength="' . (int) $field['max'] . '"' : '' ?>
                                                <?= isset($field['min']) ? 'min="' . (int) $field['min'] . '"' : '' ?>
                                                <?= isset($field['maxNumber']) ? 'max="' . (int) $field['maxNumber'] . '"' : '' ?>
                                                <?= ($field['required'] ?? false) ? 'required' : '' ?>
                                            >
                                        <?php endif ?>
                                        <?php if (isset($errors[$errorKey])): ?><div class="invalid-feedback"><?= esc($errors[$errorKey]) ?></div><?php endif ?>
                                        <?php endif ?>
                                    </div>
                                <?php endforeach ?>
                            </div>
                            <datalist id="edomex-municipalities">
                                <?php foreach ($municipalities as $municipality): ?>
                                    <option value="<?= esc($municipality) ?>"></option>
                                <?php endforeach ?>
                            </datalist>
                        </fieldset>

                        <fieldset class="border-top pt-4 mt-4">
                            <legend class="h4 font-display text-wine-dark">Documentos</legend>
                            <p class="form-hint">Formatos permitidos: PDF, JPG y JPEG. Máximo 500 MB por archivo. Si el video y los documentos suman más de 500 MB, guárdalos en varias cargas.</p>
                            <?php if (isset($context['definition']['documents_notice'])): ?>
                                <div class="alert provisional-notice" role="status"><?= esc($context['definition']['documents_notice']) ?></div>
                            <?php endif ?>
                            <div class="row g-3">
                                <?php foreach ($context['documents'] as $document): ?>
                                    <?php
                                    $type = (string) $document['type'];
                                    $current = $document['current'];
                                    $errorKey = "documents.{$type}";
                                    $accept = match ($document['accept'] ?? 'pdf,image') {
                                        'pdf' => 'application/pdf,.pdf',
                                        'image' => 'image/jpeg,.jpg,.jpeg',
                                        default => 'application/pdf,image/jpeg,.pdf,.jpg,.jpeg',
                                    };
                                    ?>
                                    <div class="col-12">
                                        <div class="border rounded-3 p-3">
                                            <label class="form-label fw-semibold" for="document-<?= esc($type) ?>">
                                                <?= esc($document['label']) ?><?= ! empty($document['required']) ? ' *' : ' (opcional)' ?>
                                            </label>
                                            <?php if ($current !== null && $current['version_id'] !== null): ?>
                                                <div class="alert alert-info py-2">
                                                    Guardado:
                                                    <a href="<?= url_to('participant.document', $current['version_id']) ?>" target="_blank"><?= esc($current['original_name']) ?></a>
                                                    (versión <?= (int) $current['active_version_number'] ?>)
                                                </div>
                                            <?php endif ?>
                                            <input class="form-control <?= isset($errors[$errorKey]) ? 'is-invalid' : '' ?>"
                                                id="document-<?= esc($type) ?>" name="documents[<?= esc($type) ?>]"
                                                type="file" accept="<?= esc($accept, 'attr') ?>">
                                            <?php if (isset($errors[$errorKey])): ?><div class="invalid-feedback"><?= esc($errors[$errorKey]) ?></div><?php endif ?>
                                            <?php if ($current !== null && $current['version_id'] !== null): ?>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" id="remove-document-<?= esc($type) ?>" name="remove_documents[]" type="checkbox" value="<?= esc($type) ?>">
                                                    <label class="form-check-label" for="remove-document-<?= esc($type) ?>">Quitar la versión activa del borrador</label>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </fieldset>

                        <div class="d-flex flex-wrap justify-content-between gap-3 border-top pt-4 mt-4">
                            <button class="btn btn-outline-wine" type="submit" formnovalidate <?= $context['convocation']['closed'] ? 'disabled' : '' ?>>Guardar borrador</button>
                            <button class="btn btn-wine" type="submit" name="next" value="summary" <?= $context['convocation']['closed'] ? 'disabled' : '' ?>>Guardar y revisar resumen</button>
                        </div>
                        <div id="video-upload-progress" class="mt-3 d-none" role="status" aria-live="polite">
                            <label class="form-label" for="video-upload-progress-bar">Cargando archivos: <span data-progress-label>0%</span></label>
                            <progress id="video-upload-progress-bar" class="w-100" max="100" value="0">0%</progress>
                            <p class="form-hint mb-0" data-progress-message>No cierres esta ventana durante la carga.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/video-choice.js') ?>?v=<?= (int) filemtime(FCPATH . 'assets/js/video-choice.js') ?>"></script>
<?= $this->endSection() ?>
