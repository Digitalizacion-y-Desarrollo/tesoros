<?php

namespace App\Commands;

use App\Services\EmailNotificationService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class ProcessEmailQueue extends BaseCommand
{
    protected $group = 'Tesoros';
    protected $name = 'email:work';
    protected $description = 'Procesa notificaciones pendientes y reintentos de la cola de correo.';
    protected $usage = 'email:work [límite]';

    public function run(array $params): int
    {
        $limit = isset($params[0]) ? (int) $params[0] : 25;
        $result = (new EmailNotificationService())->processPending($limit);
        CLI::write(sprintf(
            'Procesados: %d · Enviados: %d · Pendientes/fallidos: %d',
            $result['processed'],
            $result['sent'],
            $result['failed'],
        ));

        return $result['failed'] > 0 ? EXIT_ERROR : EXIT_SUCCESS;
    }
}
