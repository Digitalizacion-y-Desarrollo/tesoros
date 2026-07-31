<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <p class="eyebrow mb-0">Trazabilidad</p>
        <h1 class="font-display">Bitácora de operaciones</h1>
        <p class="page-head-lead">No se muestran secretos ni contenido de documentos.</p>
    </div>
    <div class="page-head-actions">
        <span class="admin-result-count"><?= number_format($audit['total']) ?> eventos registrados</span>
    </div>
</div>

<div class="admin-panel">
    <div class="table-responsive">
        <table class="table table-hover admin-table">
            <thead>
                <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">Folio</th>
                    <th scope="col">Acción</th>
                    <th scope="col">Actor</th>
                    <th scope="col">Origen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($audit['rows'] as $row): ?>
                    <tr>
                        <td><?= esc($row['created_at']) ?></td>
                        <td>
                            <?php if ($row['application_id'] !== null): ?>
                                <a href="<?= url_to('admin.applications.show', $row['application_id']) ?>"><?= esc($row['folio'] ?? 'Solicitud') ?></a>
                            <?php else: ?>
                                —
                            <?php endif ?>
                        </td>
                        <td><?= esc(admin_action_label($row['action'])) ?></td>
                        <td>
                            <?= esc(admin_actor_label($row['actor_type'])) ?>
                            <?php if ($row['actor_reference']): ?>
                                <small><?= esc($row['actor_reference']) ?></small>
                            <?php endif ?>
                        </td>
                        <td><?= esc($row['ip_address'] ?? '—') ?></td>
                    </tr>
                <?php endforeach ?>
                <?php if ($audit['rows'] === []): ?>
                    <tr><td colspan="5" class="admin-empty">Sin eventos registrados.</td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($audit['pages'] > 1): ?>
    <nav aria-label="Paginación de la bitácora">
        <ul class="admin-pagination">
            <?php for ($page = 1; $page <= $audit['pages']; $page++): ?>
                <li class="<?= $page === $audit['page'] ? 'is-current' : '' ?>">
                    <a href="?page=<?= $page ?>" <?= $page === $audit['page'] ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                </li>
            <?php endfor ?>
        </ul>
    </nav>
<?php endif ?>
<?= $this->endSection() ?>
