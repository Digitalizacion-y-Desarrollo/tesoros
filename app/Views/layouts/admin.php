<?php
$isAuthenticated = (bool) session('admin_authenticated');
$adminNav        = $isAuthenticated ? [
    ['route' => 'admin.dashboard',    'label' => 'Tablero',    'match' => 'administracion'],
    ['route' => 'admin.applications', 'label' => 'Solicitudes', 'match' => 'administracion/solicitudes*'],
    ['route' => 'admin.audit',        'label' => 'Bitácora',   'match' => 'administracion/bitacora*'],
] : [];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($title ?? 'Administración') ?> · Tesoros Gastronómicos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..800;1,400..600&family=Source+Sans+3:ital,wght@0,300..700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="admin-body">
    <a class="skip-link" href="#contenido">Saltar al contenido principal</a>

    <div class="admin-shell">
        <div class="institutional-bar">
            <span class="flag flag-mx" aria-hidden="true"></span>
            <span>México</span>
            <span class="institutional-divider" aria-hidden="true">·</span>
            <span>Francia</span>
            <span class="flag flag-fr" aria-hidden="true"></span>
        </div>

        <header class="admin-topbar">
            <a class="brand-lockup text-decoration-none" href="<?= $isAuthenticated ? url_to('admin.dashboard') : url_to('home') ?>">
                <img class="brand-mark" src="<?= base_url('assets/images/brand-tesoros.png') ?>"
                    alt="Tesoros Gastronómicos del Estado de México">
                <span>
                    <span class="brand-title font-display d-block">Tesoros Gastronómicos</span>
                    <span class="brand-subtitle d-block">Panel administrativo</span>
                </span>
            </a>
            <div class="admin-topbar-actions">
                <a class="admin-topbar-link" href="<?= url_to('home') ?>">Ver sitio público</a>
                <?php if ($isAuthenticated): ?>
                    <span class="admin-session">
                        <span class="admin-session-dot" aria-hidden="true"></span>
                        Sesión activa
                    </span>
                    <form method="post" action="<?= url_to('admin.logout') ?>" class="m-0">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-wine" type="submit">Cerrar sesión</button>
                    </form>
                <?php endif ?>
            </div>
        </header>

        <?php if ($adminNav !== []): ?>
            <nav class="admin-nav" aria-label="Secciones de administración">
                <ul>
                    <?php foreach ($adminNav as $item): ?>
                        <?php $isCurrent = url_is($item['match']); ?>
                        <li>
                            <a class="admin-nav-link<?= $isCurrent ? ' is-active' : '' ?>"
                                href="<?= url_to($item['route']) ?>"
                                <?= $isCurrent ? 'aria-current="page"' : '' ?>><?= esc($item['label']) ?></a>
                        </li>
                    <?php endforeach ?>
                </ul>
            </nav>
        <?php endif ?>

        <main id="contenido" class="admin-main">
            <div class="admin-container">
                <?php if (session()->has('message')): ?>
                    <div role="status" class="admin-alert admin-alert-success">
                        <?= esc((string) session('message')) ?>
                    </div>
                <?php endif ?>
                <?php if (session()->has('error')): ?>
                    <div role="alert" class="admin-alert admin-alert-danger">
                        <?= esc((string) session('error')) ?>
                    </div>
                <?php endif ?>

                <?= $this->renderSection('content') ?>
            </div>
        </main>

        <footer class="admin-footer">
            <div class="admin-container admin-footer-inner">
                <span>© 2026 Gobierno del Estado de México</span>
                <span class="admin-footer-note">México–Francia · 200 años de historia y amistad</span>
            </div>
        </footer>
    </div>

    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
