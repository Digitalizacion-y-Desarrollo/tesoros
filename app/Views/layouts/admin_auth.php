<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($title ?? 'Acceso administrativo') ?> · Tesoros Gastronómicos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..800;1,400..600&family=Source+Sans+3:ital,wght@0,300..700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="auth-body">
    <a class="skip-link" href="#contenido">Saltar al contenido principal</a>

    <div class="auth-shell">
        <div class="institutional-bar">
            <span class="flag flag-mx" aria-hidden="true"></span>
            <span>México</span>
            <span class="institutional-divider" aria-hidden="true">·</span>
            <span>Francia</span>
            <span class="flag flag-fr" aria-hidden="true"></span>
        </div>

        <header class="auth-header">
            <a class="brand-lockup text-decoration-none" href="<?= url_to('home') ?>">
                <img class="brand-mark" src="<?= base_url('assets/images/brand-tesoros.png') ?>"
                    alt="Tesoros Gastronómicos del Estado de México">
                <span>
                    <span class="brand-title font-display d-block">Tesoros Gastronómicos</span>
                    <span class="brand-subtitle d-block">Panel administrativo</span>
                </span>
            </a>
            <span class="auth-badge">
                <span class="auth-badge-dot" aria-hidden="true"></span>
                Acceso restringido
            </span>
        </header>

        <main id="contenido" class="auth-grid">
            <aside class="auth-aside">
                <img class="auth-aside-media" src="<?= base_url('assets/images/hero-portada.png') ?>"
                    alt="" aria-hidden="true">
                <span class="auth-aside-veil" aria-hidden="true"></span>
                <span class="ornament-pattern" aria-hidden="true"></span>

                <p class="auth-pill">
                    <span class="auth-pill-dot" aria-hidden="true"></span>
                    Panel de administración
                </p>

                <div class="auth-aside-body">
                    <h2 class="auth-aside-title font-display">Administración de convocatorias</h2>
                    <p class="auth-aside-lead">
                        Revisión de expedientes, validación documental, evaluación por comité y publicación de
                        resultados de las cuatro convocatorias del programa.
                    </p>
                    <dl class="auth-stats">
                        <div class="auth-stat">
                            <dt class="font-display">4</dt>
                            <dd>Convocatorias</dd>
                        </div>
                        <div class="auth-stat">
                            <dt class="font-display">125</dt>
                            <dd>Municipios</dd>
                        </div>
                        <div class="auth-stat">
                            <dt class="font-display">París 2026</dt>
                            <dd>Destino</dd>
                        </div>
                    </dl>
                </div>

                <p class="auth-aside-note">México–Francia · 200 años de historia y amistad</p>
            </aside>

            <div class="auth-panel">
                <div class="auth-panel-inner">
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
            </div>
        </main>

        <footer class="auth-footer">
            <span>© 2026 Gobierno del Estado de México</span>
            <a href="<?= url_to('home') ?>">Ver sitio público</a>
        </footer>
    </div>

    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/password-toggle.js') ?>" defer></script>
</body>
</html>
