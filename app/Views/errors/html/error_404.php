<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Página no encontrada · Tesoros Gastronómicos</title>
    <style>
        body { margin: 0; background: #f7f1e7; color: #292929; font-family: system-ui, sans-serif; }
        main { max-width: 720px; margin: 10vh auto; padding: 48px 28px; text-align: center; }
        strong { display: block; color: #b68a38; font: 600 5rem/1 serif; }
        h1 { color: #4a0012; font: 600 2.5rem/1.15 Georgia, serif; }
        p { line-height: 1.7; }
        a { display: inline-block; margin-top: 20px; padding: 12px 20px; background: #75001c; color: white; text-decoration: none; }
    </style>
</head>
<body>
<main>
    <strong>404</strong>
    <h1>Página no encontrada</h1>
    <p>No encontramos la dirección solicitada. Verifica el enlace o vuelve a la página principal.</p>
    <?php if (ENVIRONMENT !== 'production' && ! empty($message)): ?>
        <p><small><?= esc($message) ?></small></p>
    <?php endif ?>
    <a href="<?= esc(base_url()) ?>">Volver al inicio</a>
</main>
</body>
</html>
