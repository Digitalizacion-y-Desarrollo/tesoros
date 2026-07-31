<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php $application = $detail['application']; ?>
<a class="admin-back" href="<?= url_to('admin.applications') ?>">
    <span aria-hidden="true">←</span> Volver a solicitudes
</a>

<div class="page-head">
    <div>
        <p class="eyebrow mb-0"><?= esc($application['category_name']) ?></p>
        <h1 class="font-display"><?= esc($application['folio']) ?></h1>
    </div>
    <div class="page-head-actions">
        <?= admin_status_badge($application['status'], true) ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <section class="admin-panel">
            <div class="admin-panel-head"><h2>Datos personales</h2></div>
            <form method="post" action="<?= url_to('admin.applications.personal', $application['id']) ?>">
                <?= csrf_field() ?>
                <label class="form-label" for="admin-email">Correo de la solicitud</label>
                <input class="form-control" id="admin-email" name="email" type="email" maxlength="254" value="<?= esc($application['email']) ?>" required>

                <?php foreach ($detail['participants'] as $index => $person): ?>
                    <fieldset class="mt-4">
                        <legend><?= $index === 0 ? 'Persona responsable' : 'Integrante ' . ($index + 1) ?></legend>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="first-<?= $index ?>">Nombre(s)</label>
                                <input class="form-control" id="first-<?= $index ?>" name="participants[<?= $index ?>][first_name]" maxlength="100" value="<?= esc($person['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="curp-<?= $index ?>">CURP</label>
                                <input class="form-control text-uppercase" id="curp-<?= $index ?>" name="participants[<?= $index ?>][curp]" maxlength="18" value="<?= esc($person['curp']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="last-<?= $index ?>">Primer apellido</label>
                                <input class="form-control" id="last-<?= $index ?>" name="participants[<?= $index ?>][last_name]" maxlength="150" value="<?= esc($person['last_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="second-<?= $index ?>">Segundo apellido</label>
                                <input class="form-control" id="second-<?= $index ?>" name="participants[<?= $index ?>][second_last_name]" maxlength="150" value="<?= esc($person['second_last_name'] ?? '') ?>">
                            </div>
                        </div>
                    </fieldset>
                <?php endforeach ?>

                <button class="btn btn-wine mt-4" type="submit">Guardar datos personales</button>
            </form>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-head"><h2>Información de la categoría</h2></div>
            <dl class="summary-grid mt-0">
                <?php foreach ($detail['form'] as $name => $value): ?>
                    <div>
                        <dt><?= esc(admin_field_label((string) $name)) ?></dt>
                        <dd><?= nl2br(esc((string) ($value !== '' ? $value : 'No capturado'))) ?></dd>
                    </div>
                <?php endforeach ?>
            </dl>
            <?php if ($detail['form'] === []): ?>
                <p class="admin-empty mb-0">Sin información capturada.</p>
            <?php endif ?>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-head"><h2>Documentos y versiones</h2></div>
            <div class="admin-doc-list">
                <?php foreach ($detail['documents'] as $document): ?>
                    <article class="admin-doc">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <h3><?= esc($document['label']) ?></h3>
                                <p class="admin-doc-meta">Versión activa: <?= esc($document['active_version_number'] ?? 'ninguna') ?></p>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <?php if ((int) $document['correction_unlocked'] === 1): ?>
                                    <span class="status-badge status-badge-warning">Habilitado para corrección</span>
                                <?php endif ?>
                                <?php if ($document['version_id'] !== null): ?>
                                    <button
                                        class="btn btn-sm btn-outline-wine"
                                        type="button"
                                        data-document-preview
                                        data-preview-url="<?= esc(url_to('admin.document', $document['version_id']), 'attr') ?>"
                                        data-preview-mime="<?= esc($document['mime_type'], 'attr') ?>"
                                        data-preview-name="<?= esc($document['original_name'], 'attr') ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#document-preview-modal"
                                    >Vista previa</button>
                                <?php endif ?>
                            </div>
                        </div>
                        <details class="mt-3">
                            <summary>Historial de versiones (<?= count($document['versions']) ?>)</summary>
                            <ul class="admin-doc-versions">
                                <?php foreach ($document['versions'] as $version): ?>
                                    <li>
                                        <span>v<?= (int) $version['version_number'] ?> · <?= esc($version['original_name']) ?> · <?= number_format(((int) $version['size_bytes']) / 1048576, 1) ?> MB</span>
                                        <button
                                            class="admin-doc-preview-link"
                                            type="button"
                                            data-document-preview
                                            data-preview-url="<?= esc(url_to('admin.document', $version['id']), 'attr') ?>"
                                            data-preview-mime="<?= esc($version['mime_type'], 'attr') ?>"
                                            data-preview-name="<?= esc($version['original_name'], 'attr') ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#document-preview-modal"
                                        >Consultar</button>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </details>
                    </article>
                <?php endforeach ?>

                <?php if ($detail['documents'] === []): ?>
                    <p class="admin-empty mb-0">No hay documentos registrados.</p>
                <?php endif ?>

                <?php if ($detail['video'] !== null): ?>
                    <article class="admin-doc admin-video-preview">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <h3>Video</h3>
                                <p class="admin-doc-meta mb-0">
                                    <?= $detail['video']['source_type'] === 'file'
                                        ? esc($detail['video']['original_name'])
                                        : esc($detail['video']['preview']['provider'] ?? 'Video externo') ?>
                                </p>
                            </div>
                            <?php if ($detail['video']['source_type'] === 'file'): ?>
                                <a class="btn btn-sm btn-outline-wine" href="<?= url_to('admin.video', $detail['video']['id']) ?>" target="_blank" rel="noopener">Abrir en otra pestaña</a>
                            <?php elseif (($detail['video']['preview']['url'] ?? '') !== ''): ?>
                                <a class="btn btn-sm btn-outline-wine" href="<?= esc($detail['video']['preview']['url'], 'attr') ?>" target="_blank" rel="noopener noreferrer">Abrir enlace original</a>
                            <?php endif ?>
                        </div>

                        <?php if ($detail['video']['source_type'] === 'file'): ?>
                            <video class="admin-video-player mt-3" controls preload="metadata" playsinline>
                                <source src="<?= url_to('admin.video', $detail['video']['id']) ?>" type="video/mp4">
                                Tu navegador no puede reproducir este video.
                            </video>
                        <?php elseif (($detail['video']['preview']['kind'] ?? '') === 'embed'): ?>
                            <div class="ratio ratio-16x9 mt-3">
                                <iframe
                                    class="admin-video-frame"
                                    src="<?= esc($detail['video']['preview']['embed_url'], 'attr') ?>"
                                    title="Video de <?= esc($detail['video']['preview']['provider'], 'attr') ?>"
                                    loading="lazy"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                    sandbox="allow-scripts allow-same-origin allow-presentation"
                                    allow="encrypted-media; picture-in-picture; fullscreen"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        <?php else: ?>
                            <div class="admin-external-video mt-3">
                                <p class="mb-1">El proveedor <strong><?= esc($detail['video']['preview']['host'] ?? '') ?></strong> no permite una vista previa segura dentro del panel.</p>
                                <p class="text-secondary mb-0">Usa “Abrir enlace original” para consultarlo en una pestaña separada.</p>
                            </div>
                        <?php endif ?>
                    </article>
                <?php endif ?>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-head"><h2>Historial</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Acción</th>
                            <th scope="col">Actor</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detail['history'] as $event): ?>
                            <tr>
                                <td><?= esc($event['created_at']) ?></td>
                                <td><?= esc(admin_action_label($event['action'])) ?></td>
                                <td><?= esc(admin_actor_label($event['actor_type'])) ?></td>
                                <td><?= esc(admin_status_label($event['from_status']) ?: '—') ?> → <?= esc(admin_status_label($event['to_status']) ?: '—') ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($detail['history'] === []): ?>
                            <tr><td colspan="4" class="admin-empty">Sin eventos registrados.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <aside class="col-xl-4">
        <section class="admin-panel">
            <div class="admin-panel-head"><h2>Cambiar estado</h2></div>
            <?php if ($allowedTransitions !== []): ?>
                <form method="post" action="<?= url_to('admin.applications.status', $application['id']) ?>">
                    <?= csrf_field() ?>
                    <label class="form-label" for="new-status">Nuevo estado</label>
                    <select class="form-select" id="new-status" name="status" required>
                        <option value="">Selecciona</option>
                        <?php foreach ($allowedTransitions as $status): ?>
                            <?php if ($status === 'incompleta') { continue; } ?>
                            <option value="<?= esc($status) ?>"><?= esc(admin_status_label($status)) ?></option>
                        <?php endforeach ?>
                    </select>
                    <label class="form-label mt-3" for="status-comment">Comentario interno opcional</label>
                    <textarea class="form-control" id="status-comment" name="comment" rows="3" maxlength="4000"></textarea>
                    <button class="btn btn-wine mt-3 w-100" type="submit">Actualizar estado</button>
                </form>
            <?php else: ?>
                <p class="text-secondary mb-0">Este estado no admite transiciones administrativas.</p>
            <?php endif ?>
        </section>

        <?php if (in_array('incompleta', $allowedTransitions, true)): ?>
            <section class="admin-panel">
                <div class="admin-panel-head"><h2>Solicitar corrección</h2></div>
                <form method="post" action="<?= url_to('admin.applications.correction', $application['id']) ?>">
                    <?= csrf_field() ?>
                    <fieldset>
                        <legend class="form-label">Documentos a desbloquear</legend>
                        <p class="form-hint">Selecciona uno o varios documentos para incluirlos en la misma solicitud.</p>
                        <div class="admin-correction-options">
                        <?php foreach ($detail['documents'] as $document): ?>
                            <?php if ($document['version_id'] === null) { continue; } ?>
                            <div class="form-check">
                                <input class="form-check-input" id="correction-document-<?= (int) $document['id'] ?>"
                                    name="document_ids[]" type="checkbox" value="<?= (int) $document['id'] ?>">
                                <label class="form-check-label" for="correction-document-<?= (int) $document['id'] ?>">
                                    <?= esc($document['label']) ?>
                                </label>
                            </div>
                        <?php endforeach ?>
                        </div>
                    </fieldset>
                    <label class="form-label mt-3" for="correction-comment">Indicaciones para la persona participante</label>
                    <textarea class="form-control" id="correction-comment" name="comment" rows="5" maxlength="4000" required></textarea>
                    <button class="btn btn-gold mt-3 w-100" type="submit">Marcar incompleta y desbloquear</button>
                </form>
            </section>
        <?php endif ?>

        <section class="admin-panel">
            <div class="admin-panel-head"><h2>Comentarios</h2></div>
            <form method="post" action="<?= url_to('admin.applications.comments', $application['id']) ?>">
                <?= csrf_field() ?>
                <label class="form-label" for="comment">Nuevo comentario</label>
                <textarea class="form-control" id="comment" name="comment" rows="4" maxlength="4000" required></textarea>
                <div class="form-check mt-3">
                    <input class="form-check-input" id="visible" name="visible_to_participant" type="checkbox" value="1">
                    <label class="form-check-label" for="visible">Visible para participante</label>
                </div>
                <button class="btn btn-outline-wine mt-3 w-100" type="submit">Agregar comentario</button>
            </form>

            <div class="mt-4">
                <?php foreach ($detail['comments'] as $comment): ?>
                    <div class="admin-note">
                        <p><?= nl2br(esc($comment['comment'])) ?></p>
                        <span class="admin-note-meta">
                            <?= esc($comment['created_at']) ?> · <?= $comment['is_visible_to_participant'] ? 'Visible' : 'Interno' ?>
                        </span>
                    </div>
                <?php endforeach ?>
                <?php if ($detail['comments'] === []): ?>
                    <p class="admin-empty mb-0">Sin comentarios registrados.</p>
                <?php endif ?>
            </div>
        </section>
    </aside>
</div>

<div class="modal fade" id="document-preview-modal" tabindex="-1" aria-labelledby="document-preview-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content admin-preview-modal">
            <div class="modal-header">
                <div>
                    <p class="eyebrow mb-1">Vista previa privada</p>
                    <h2 class="modal-title fs-5" id="document-preview-title">Documento</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar vista previa"></button>
            </div>
            <div class="modal-body admin-preview-body">
                <div class="admin-preview-loading" data-preview-loading role="status">Cargando vista previa…</div>
                <img class="admin-document-image d-none" data-preview-image src="" alt="">
                <iframe class="admin-document-frame d-none" data-preview-frame src="about:blank" title="Vista previa del documento"></iframe>
                <div class="admin-preview-unsupported d-none" data-preview-unsupported>
                    <p>Este tipo de archivo no tiene vista previa dentro del panel.</p>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-wine" data-preview-open href="#" target="_blank" rel="noopener">Abrir en otra pestaña</a>
                <button type="button" class="btn btn-wine" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/admin-document-preview.js') ?>"></script>
<?= $this->endSection() ?>
