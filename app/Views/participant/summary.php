<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$application = $context['application'];
$category = $context['category'];
?>
<section class="application-shell py-5">
    <div class="container-xxl">
        <div class="row g-4">
            <aside class="col-lg-4">
                <div class="application-aside">
                    <p class="eyebrow mb-2">Folio</p>
                    <p class="application-folio"><?= esc($application['folio']) ?></p>
                    <div class="application-step-list mt-4" aria-label="Etapas del registro">
                        <span class="complete">1. Identificación</span>
                        <span class="complete">2. Formulario</span>
                        <span class="active">3. Resumen y envío</span>
                    </div>
                </div>
            </aside>
            <div class="col-lg-8">
                <div class="application-card">
                    <h1 class="h2 font-display text-wine-dark">Revisa antes de enviar</h1>
                    <p class="text-secondary">Después de confirmar, los datos y archivos quedarán bloqueados.</p>

                    <section class="summary-section">
                        <h2 class="h4">Solicitud</h2>
                        <dl class="summary-grid">
                            <div><dt>Categoría</dt><dd><?= esc($category['name']) ?></dd></div>
                            <div><dt>Correo</dt><dd><?= esc($application['email']) ?></dd></div>
                        </dl>
                    </section>

                    <section class="summary-section">
                        <h2 class="h4">Participantes</h2>
                        <?php foreach ($context['participants'] as $index => $person): ?>
                            <dl class="summary-grid">
                                <div><dt>Rol</dt><dd><?= $index === 0 ? 'Responsable' : 'Integrante' ?></dd></div>
                                <div><dt>Nombre</dt><dd><?= esc(trim($person['first_name'] . ' ' . $person['last_name'] . ' ' . ($person['second_last_name'] ?? ''))) ?></dd></div>
                                <div><dt>CURP</dt><dd><?= esc($person['curp']) ?></dd></div>
                            </dl>
                        <?php endforeach ?>
                    </section>

                    <section class="summary-section">
                        <h2 class="h4">Información de la categoría</h2>
                        <dl class="summary-grid">
                            <?php foreach ($context['definition']['fields'] as $field): ?>
                                <div class="<?= $field['type'] === 'textarea' ? 'summary-wide' : '' ?>">
                                    <dt><?= esc($field['label']) ?></dt>
                                    <?php if ($field['type'] === 'video'): ?>
                                        <dd>
                                            <?php if ($context['video'] === null): ?>
                                                No capturado
                                            <?php elseif ($context['video']['source_type'] === 'file'): ?>
                                                <a href="<?= url_to('participant.video', $context['video']['id']) ?>" target="_blank">
                                                    Consultar <?= esc($context['video']['original_name']) ?>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= esc($context['video']['external_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer">
                                                    Abrir enlace HTTPS
                                                </a>
                                            <?php endif ?>
                                        </dd>
                                    <?php else: ?>
                                        <dd><?= nl2br(esc((string) ($context['form'][$field['name']] ?? 'No capturado'))) ?></dd>
                                    <?php endif ?>
                                </div>
                            <?php endforeach ?>
                        </dl>
                    </section>

                    <?php if ($context['documents'] !== []): ?>
                        <section class="summary-section">
                            <h2 class="h4">Documentos</h2>
                            <dl class="summary-grid">
                                <?php foreach ($context['documents'] as $document): ?>
                                    <div>
                                        <dt><?= esc($document['label']) ?></dt>
                                        <dd>
                                            <?php if ($document['current'] === null || $document['current']['version_id'] === null): ?>
                                                No capturado
                                            <?php else: ?>
                                                <a href="<?= url_to('participant.document', $document['current']['version_id']) ?>" target="_blank">
                                                    Consultar <?= esc($document['current']['original_name']) ?>
                                                </a>
                                            <?php endif ?>
                                        </dd>
                                    </div>
                                <?php endforeach ?>
                            </dl>
                        </section>
                    <?php endif ?>

                    <form method="post" action="<?= url_to('participant.draft.submit') ?>" class="border-top pt-4 mt-4">
                        <?= csrf_field() ?>
                        <div class="alert provisional-notice">
                            Las declaraciones y textos legales son provisionales de desarrollo. La publicación en producción permanece bloqueada hasta recibir los documentos aprobados.
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input <?= isset($errors['accept_declarations']) ? 'is-invalid' : '' ?>" id="accept-declarations" name="accept_declarations" type="checkbox" value="1" required>
                            <label class="form-check-label" for="accept-declarations">Acepto los <a href="<?= url_to('legal.show', 'terminos-condiciones') ?>" target="_blank" rel="noopener">términos y declaraciones provisionales vigentes</a> y confirmo que la información es correcta.</label>
                            <?php if (isset($errors['accept_declarations'])): ?><div class="invalid-feedback"><?= esc($errors['accept_declarations']) ?></div><?php endif ?>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input <?= isset($errors['confirm_submit']) ? 'is-invalid' : '' ?>" id="confirm-submit" name="confirm_submit" type="checkbox" value="1" required>
                            <label class="form-check-label fw-semibold" for="confirm-submit">Entiendo que el envío es definitivo y bloqueará la solicitud.</label>
                            <?php if (isset($errors['confirm_submit'])): ?><div class="invalid-feedback"><?= esc($errors['confirm_submit']) ?></div><?php endif ?>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between gap-3 mt-4">
                            <a class="btn btn-outline-wine" href="<?= url_to('participant.draft') ?>">Volver a editar</a>
                            <button class="btn btn-wine" type="submit">Enviar solicitud definitivamente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
