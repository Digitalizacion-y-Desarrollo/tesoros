<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sourceRoot = $root . '/node_modules/bootstrap/dist';
$targetRoot = $root . '/public/assets/vendor/bootstrap';

$assets = [
    'css/bootstrap.min.css',
    'css/bootstrap.min.css.map',
    'js/bootstrap.bundle.min.js',
    'js/bootstrap.bundle.min.js.map',
];

foreach ($assets as $asset) {
    $source = $sourceRoot . '/' . $asset;
    $target = $targetRoot . '/' . $asset;

    if (! is_file($source)) {
        fwrite(STDERR, "No se encontró {$source}. Ejecuta npm install primero." . PHP_EOL);
        exit(1);
    }

    $targetDirectory = dirname($target);

    if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0755, true) && ! is_dir($targetDirectory)) {
        fwrite(STDERR, "No se pudo crear {$targetDirectory}." . PHP_EOL);
        exit(1);
    }

    if (! copy($source, $target)) {
        fwrite(STDERR, "No se pudo publicar {$asset}." . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, 'Bootstrap publicado localmente en public/assets/vendor/bootstrap.' . PHP_EOL);
