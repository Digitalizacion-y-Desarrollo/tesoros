<?php

namespace App\Services;

use Config\Services;
use Throwable;

final class SmtpParticipantCodeMailer implements ParticipantCodeMailerInterface
{
    public function send(string $recipient, string $folio, string $code, string $expiresAt): bool
    {
        $config = config('Email');
        $email = Services::email($config, false);

        try {
            $email->setFrom($config->fromEmail, $config->fromName);
            $email->setTo($recipient);
            $email->setSubject('Código temporal de acceso · Tesoros Gastronómicos');
            $email->setMessage(view('emails/participant_access_code', [
                'folio' => $folio,
                'code' => $code,
                'expiresAt' => $expiresAt,
            ]));
            $email->setAltMessage(
                "Tu código temporal es {$code}. Vence en 10 minutos. "
                . 'Si no solicitaste este acceso, ignora el mensaje.',
            );

            return $email->send();
        } catch (Throwable $exception) {
            log_message('error', 'Falló el envío del código temporal por correo: {type}', [
                'type' => $exception::class,
            ]);

            return false;
        }
    }
}
