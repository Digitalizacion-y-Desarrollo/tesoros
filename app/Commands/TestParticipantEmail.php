<?php

namespace App\Commands;

use App\Services\SmtpParticipantCodeMailer;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use DateTimeImmutable;

final class TestParticipantEmail extends BaseCommand
{
    protected $group = 'Tesoros';
    protected $name = 'email:test-participant';
    protected $description = 'Envía un código temporal ficticio para verificar la configuración SMTP.';
    protected $usage = 'email:test-participant <destinatario>';
    protected $arguments = [
        'destinatario' => 'Correo de prueba que recibirá el mensaje en el buzón SMTP configurado.',
    ];

    public function run(array $params): int
    {
        if (ENVIRONMENT === 'production') {
            CLI::error('Este comando de diagnóstico está deshabilitado en producción.');
            return EXIT_ERROR;
        }

        $recipient = trim((string) ($params[0] ?? ''));
        if (filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            CLI::error('Proporciona un destinatario válido.');
            return EXIT_ERROR;
        }

        $sent = (new SmtpParticipantCodeMailer())->send(
            $recipient,
            'TG-2026-PRUEBA-000000',
            '000000',
            (new DateTimeImmutable('+10 minutes'))->format('Y-m-d H:i:s'),
        );

        if (! $sent) {
            CLI::error('El servidor SMTP no confirmó el envío. Revisa writable/logs.');
            return EXIT_ERROR;
        }

        CLI::write('El servidor SMTP confirmó el envío de prueba.', 'green');
        return EXIT_SUCCESS;
    }
}
