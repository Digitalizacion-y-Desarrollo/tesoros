<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$application = $context['application'];
$statusLabels = [
    'borrador' => 'Borrador',
    'enviada' => 'Enviada',
    'en_revision' => 'En revisión',
    'incompleta' => 'Incompleta',
    'seleccionada' => 'Seleccionada',
    'rechazada' => 'Rechazada',
    'cancelada' => 'Cancelada',
];
?>
<section class="application-shell py-5">
    <div class="container">
        <div class="application-card col-xl-9 mx-auto">
            <p class="eyebrow mb-2">Detalle de participación</p>
            <h1 class="font-display display-6 text-wine-dark"><?= esc($application['folio']) ?></h1>
            <div class="status-banner mt-4">
                <span>Estado actual</span>
                <strong><?= esc($statusLabels[$application['status']] ?? $application['status']) ?></strong>
            </div>
            <dl class="summary-grid mt-4">
                <div><dt>Categoría</dt><dd><?= esc($context['category']['name']) ?></dd></div>
                <div><dt>Correo</dt><dd><?= esc($application['email']) ?></dd></div>
                <?php if ($application['submitted_at'] !== null): ?>
                    <div><dt>Fecha de envío</dt><dd><?= esc($application['submitted_at']) ?></dd></div>
                <?php endif ?>
            </dl>

            <?php if ($context['video'] !== null): ?>
                <div class="mt-4">
                    <h2 class="h5">Video registrado</h2>
                    <?php if ($context['video']['source_type'] === 'file'): ?>
                        <a class="btn btn-outline-wine" href="<?= url_to('participant.video', $context['video']['id']) ?>" target="_blank">
                            Consultar <?= esc($context['video']['original_name']) ?>
                        </a>
                    <?php else: ?>
                        <a class="btn btn-outline-wine" href="<?= esc($context['video']['external_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer">
                            Abrir enlace HTTPS
                        </a>
                    <?php endif ?>
                </div>
            <?php endif ?>

            <?php if ($context['documents'] !== []): ?>
                <div class="mt-4">
                    <h2 class="h5">Documentos registrados</h2>
                    <ul class="list-group">
                        <?php foreach ($context['documents'] as $document): ?>
                            <?php if ($document['current'] !== null && $document['current']['version_id'] !== null): ?>
                                <li class="list-group-item d-flex flex-wrap justify-content-between gap-2">
                                    <span><?= esc($document['label']) ?></span>
                                    <a href="<?= url_to('participant.document', $document['current']['version_id']) ?>" target="_blank">Consultar</a>
                                </li>
                            <?php endif ?>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <?php if ($application['status'] === 'borrador'): ?>
                <div class="alert alert-info mt-4">Tu solicitud todavía puede modificarse.</div>
                <a class="btn btn-wine" href="<?= url_to('participant.draft') ?>">Continuar formulario</a>
            <?php elseif ($application['status'] !== 'incompleta'): ?>
                <div class="alert alert-success mt-4">
                    La solicitud está bloqueada. No es posible modificar datos ni sustituir archivos después del envío.
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-4">
                    La solicitud permanece bloqueada, excepto por los documentos que administración solicitó corregir.
                </div>
            <?php endif ?>

            <?php if ($context['admin_comment'] !== null): ?>
                <div class="alert provisional-notice mt-4" role="status">
                    <h2 class="h5">Comentario de administración</h2>
                    <p class="mb-0"><?= nl2br(esc($context['admin_comment']['comment'])) ?></p>
                </div>
            <?php endif ?>

            <?php if ($application['status'] === 'incompleta'): ?>
                <?php foreach ($context['documents'] as $document): ?>
                    <?php if ($document['current'] !== null && (int) $document['current']['correction_unlocked'] === 1): ?>
                        <form method="post" action="<?= url_to('participant.document.correct', $document['type']) ?>"
                            enctype="multipart/form-data" class="border rounded-3 p-4 mt-4">
                            <?= csrf_field() ?>
                            <h2 class="h5">Corregir documento solicitado</h2>
                            <p><?= esc($document['label']) ?></p>
                            <label class="form-label" for="correction-file-<?= esc($document['type']) ?>">Nueva versión del archivo</label>
                            <input class="form-control" id="correction-file-<?= esc($document['type']) ?>" name="correction_file" type="file"
                                accept="application/pdf,image/jpeg,.pdf,.jpg,.jpeg" required>
                            <div class="form-check mt-3">
                                <input class="form-check-input" id="confirm-correction-<?= esc($document['type']) ?>" name="confirm_correction" type="checkbox" value="1" required>
                                <label class="form-check-label" for="confirm-correction-<?= esc($document['type']) ?>">Confirmo el reenvío de esta nueva versión.</label>
                            </div>
                            <button class="btn btn-wine mt-3" type="submit">Enviar corrección</button>
                        </form>
                    <?php endif ?>
                <?php endforeach ?>
            <?php endif ?>

            <?php if (in_array($application['status'], ['borrador', 'enviada', 'incompleta'], true)): ?>
                <details class="border border-danger-subtle rounded-3 p-4 mt-4">
                    <summary class="fw-semibold text-danger">Cancelar solicitud</summary>
                    <p class="mt-3">La cancelación es irreversible. Escribe tu folio para confirmar.</p>
                    <form method="post" action="<?= url_to('participant.application.cancel') ?>">
                        <?= csrf_field() ?>
                        <label class="form-label" for="folio-confirmation">Folio</label>
                        <input class="form-control" id="folio-confirmation" name="folio_confirmation" maxlength="18" required>
                        <div class="form-check mt-3">
                            <input class="form-check-input" id="confirm-cancel" name="confirm_cancel" type="checkbox" value="1" required>
                            <label class="form-check-label" for="confirm-cancel">Confirmo que deseo cancelar definitivamente.</label>
                        </div>
                        <button class="btn btn-danger mt-3" type="submit">Cancelar solicitud</button>
                    </form>
                </details>
            <?php endif ?>

            <form method="post" action="<?= url_to('participant.logout') ?>" class="border-top mt-4 pt-4">
                <?= csrf_field() ?>
                <button class="btn btn-outline-wine" type="submit">Cerrar sesión</button>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
