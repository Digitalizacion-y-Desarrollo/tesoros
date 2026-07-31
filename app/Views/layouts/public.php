<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Convocatoria Tesoros Gastronómicos del Estado de México rumbo a París 2026.">
    <title><?= esc($title ?? 'Tesoros Gastronómicos') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400&family=Playfair+Display:ital,wght@0,400..800;1,400..600&family=Source+Sans+3:ital,wght@0,300..700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100 bg-light text-dark">
    <a class="skip-link" href="#contenido">Saltar al contenido principal</a>
    <?= $this->include('partials/public_header') ?>
    <?= $this->include('partials/flash_messages') ?>
    <main id="contenido">
        <?= $this->renderSection('content') ?>
    </main>
    <?= $this->include('partials/public_footer') ?>
    <?= $this->include('partials/accessibility') ?>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/accessibility.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
