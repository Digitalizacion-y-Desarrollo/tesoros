<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$categoryLabels = [
    'cocineras-cocineros-tradicionales' => 'Cocineras y Cocineros Tradicionales',
    'restaurantes'                      => 'Restaurantes',
    'joven-talento-gastronomia'         => 'Joven Talento Universitario',
    'bebidas-tradicionales-ancestrales' => 'Bebidas Tradicionales y Ancestrales',
];
$queryBase = $listing['filters'];
?>
<div class="page-head">
    <div>
        <p class="eyebrow mb-0">Administración</p>
        <h1 class="font-display">Solicitudes</h1>
    </div>
    <div class="page-head-actions">
        <span class="admin-result-count"><?= number_format($listing['total']) ?> resultados</span>
        <a class="btn btn-outline-wine" href="<?= url_to('admin.applications.export') ?>?<?= esc(http_build_query($listing['filters']), 'attr') ?>">Exportar CSV</a>
    </div>
</div>

<form method="get" action="<?= url_to('admin.applications') ?>" class="admin-panel admin-filters">
    <div class="row g-3 align-items-end">
        <div class="col-lg-4">
            <label class="form-label" for="q">Buscar</label>
            <input class="form-control" id="q" name="q" value="<?= esc($listing['filters']['q']) ?>" placeholder="Folio, correo, nombre o CURP">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label" for="category">Categoría</label>
            <select class="form-select" id="category" name="category">
                <option value="">Todas</option>
                <?php foreach ($categories as $code => $definition): ?>
                    <option value="<?= esc($code) ?>" <?= $listing['filters']['category'] === $code ? 'selected' : '' ?>><?= esc($categoryLabels[$code] ?? $code) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label" for="status">Estado</label>
            <select class="form-select" id="status" name="status">
                <option value="">Todos</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= esc($status) ?>" <?= $listing['filters']['status'] === $status ? 'selected' : '' ?>><?= esc(admin_status_label($status)) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="form-label" for="municipality">Municipio</label>
            <input class="form-control" id="municipality" name="municipality" list="admin-municipalities" value="<?= esc($listing['filters']['municipality']) ?>">
        </div>
        <div class="col-sm-6 col-lg-2 d-grid">
            <button class="btn btn-wine" type="submit">Aplicar filtros</button>
        </div>
    </div>
    <datalist id="admin-municipalities">
        <?php foreach ($municipalities as $municipality): ?>
            <option value="<?= esc($municipality) ?>"></option>
        <?php endforeach ?>
    </datalist>
</form>

<div class="admin-panel">
    <div class="table-responsive">
        <table class="table table-hover admin-table">
            <thead>
                <tr>
                    <th scope="col">Folio</th>
                    <th scope="col">Responsable</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Municipio</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Actualización</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listing['rows'] as $row): ?>
                    <tr>
                        <td><a class="fw-semibold" href="<?= url_to('admin.applications.show', $row['id']) ?>"><?= esc($row['folio']) ?></a></td>
                        <td>
                            <?= esc($row['first_name'] . ' ' . $row['last_name']) ?>
                            <small><?= esc($row['email_masked']) ?> · <?= esc($row['curp_masked']) ?></small>
                        </td>
                        <td><?= esc($row['category_name']) ?></td>
                        <td><?= esc($row['municipality'] ?? 'Pendiente') ?></td>
                        <td><?= admin_status_badge($row['status']) ?></td>
                        <td><?= esc($row['updated_at']) ?></td>
                    </tr>
                <?php endforeach ?>
                <?php if ($listing['rows'] === []): ?>
                    <tr><td colspan="6" class="admin-empty">No hay resultados para los filtros seleccionados.</td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($listing['pages'] > 1): ?>
    <nav aria-label="Paginación de solicitudes">
        <ul class="admin-pagination">
            <?php for ($page = 1; $page <= $listing['pages']; $page++): ?>
                <li class="<?= $page === $listing['page'] ? 'is-current' : '' ?>">
                    <a href="?<?= esc(http_build_query($queryBase + ['page' => $page]), 'attr') ?>"
                        <?= $page === $listing['page'] ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                </li>
            <?php endfor ?>
        </ul>
    </nav>
<?php endif ?>
<?= $this->endSection() ?>
