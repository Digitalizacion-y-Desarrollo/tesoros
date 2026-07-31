<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div>
        <p class="eyebrow mb-0">Gestión de la convocatoria</p>
        <h1 class="font-display">Tablero</h1>
        <p class="page-head-lead">Resumen operativo de las cuatro convocatorias del programa.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn btn-wine" href="<?= url_to('admin.applications') ?>">Consultar solicitudes</a>
    </div>
</div>

<section class="row g-3" aria-label="Resumen de solicitudes">
    <div class="col-sm-6 col-xl-3">
        <div class="admin-metric admin-metric-primary">
            <span>Total de solicitudes</span>
            <strong><?= number_format($dashboard['total']) ?></strong>
        </div>
    </div>
    <?php foreach (array_slice($dashboard['by_status'], 0, 3) as $row): ?>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-metric">
                <span><?= esc(admin_status_label($row['status'])) ?></span>
                <strong><?= number_format($row['total']) ?></strong>
            </div>
        </div>
    <?php endforeach ?>
</section>

<div class="row g-4 mt-1">
    <section class="col-lg-6">
        <div class="admin-panel h-100">
            <div class="admin-panel-head"><h2>Por categoría</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr><th scope="col">Categoría</th><th scope="col" class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard['by_category'] as $row): ?>
                            <tr>
                                <td><?= esc($row['name']) ?></td>
                                <td class="admin-numeric"><?= number_format($row['total']) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($dashboard['by_category'] === []): ?>
                            <tr><td colspan="2" class="admin-empty">Sin datos capturados.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="col-lg-6">
        <div class="admin-panel h-100">
            <div class="admin-panel-head"><h2>Municipios con más registros</h2></div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr><th scope="col">Municipio</th><th scope="col" class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard['by_municipality'] as $row): ?>
                            <tr>
                                <td><?= esc($row['municipality']) ?></td>
                                <td class="admin-numeric"><?= number_format($row['total']) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($dashboard['by_municipality'] === []): ?>
                            <tr><td colspan="2" class="admin-empty">Sin datos capturados.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<section class="admin-panel mt-4">
    <div class="admin-panel-head">
        <h2>Solicitudes recientes</h2>
        <a href="<?= url_to('admin.audit') ?>">Ver bitácora</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover admin-table">
            <thead>
                <tr>
                    <th scope="col">Folio</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Responsable</th>
                    <th scope="col">Municipio</th>
                    <th scope="col">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dashboard['recent'] as $row): ?>
                    <tr>
                        <td><a class="fw-semibold" href="<?= url_to('admin.applications.show', $row['id']) ?>"><?= esc($row['folio']) ?></a></td>
                        <td><?= esc($row['category_name']) ?></td>
                        <td>
                            <?= esc($row['first_name'] . ' ' . $row['last_name']) ?>
                            <small><?= esc($row['email_masked']) ?></small>
                        </td>
                        <td><?= esc($row['municipality'] ?? 'Pendiente') ?></td>
                        <td><?= admin_status_badge($row['status']) ?></td>
                    </tr>
                <?php endforeach ?>
                <?php if ($dashboard['recent'] === []): ?>
                    <tr><td colspan="5" class="admin-empty">Aún no hay solicitudes.</td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</section>
<?= $this->endSection() ?>
