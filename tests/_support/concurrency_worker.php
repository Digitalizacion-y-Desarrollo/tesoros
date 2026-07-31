<?php

declare(strict_types=1);

putenv('database.tests.hostname=127.0.0.1');
putenv('database.tests.database=tesoros_test');
putenv('database.tests.username=root');
putenv('database.tests.password=');
putenv('database.tests.DBDriver=MySQLi');
putenv('database.tests.DBPrefix=');

require dirname(__DIR__, 2) . '/system/Test/bootstrap.php';

$result = (new App\Services\DraftApplicationService())->create(
    $argv[1],
    $argv[2],
    [[
        'curp'             => $argv[3],
        'first_name'       => 'Prueba',
        'last_name'        => 'Concurrente',
        'second_last_name' => null,
    ]],
);

fwrite(STDOUT, json_encode($result, JSON_THROW_ON_ERROR));
