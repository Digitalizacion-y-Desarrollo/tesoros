<?php

require dirname(__DIR__) . '/system/Test/bootstrap.php';

$database = config('Database');
$defaultName = (string) ($database->default['database'] ?? '');
$testsName = (string) ($database->tests['database'] ?? '');
$defaultConnectionName = Config\Database::connect('default')->getDatabase();
$testsConnectionName = Config\Database::connect('tests')->getDatabase();

if ($testsName === ''
    || $testsName === $defaultName
    || $testsName === 'tesoros'
    || $testsConnectionName === $defaultConnectionName
    || $testsConnectionName === 'tesoros'
) {
    throw new RuntimeException(
        'La suite se detuvo porque la base de pruebas no está aislada de la base principal.',
    );
}
