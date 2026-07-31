<?php

namespace App\Commands;

use App\Services\SmtpParticipantRegistrationMailer;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class TestRegistrationEmail extends BaseCommand
{
    protected $group = 'Tesoros';
    protected $name = 'email:test-registration';
    protected $description = 'Envía una confirmación ficticia de registro para verificar SMTP.';
    protected $usage = 'email:test-registration <destinatario>';
    protected $arguments = [
        'destinatario' => 'Correo que recibirá el mensaje en el buzón SMTP configurado.',
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

        $sent = (new SmtpParticipantRegistrationMailer())->send(
            $recipient,
            'TG-2026-JTG-000000',
            'Joven Talento Universitario en Gastronomía',
        );

        if (! $sent) {
            CLI::error('El servidor SMTP no confirmó el envío. Revisa writable/logs.');
            return EXIT_ERROR;
        }

        CLI::write('El servidor SMTP confirmó el correo de registro exitoso.', 'green');
        return EXIT_SUCCESS;
    }
}
