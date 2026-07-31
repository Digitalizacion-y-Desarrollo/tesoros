<?php

namespace App\Services;

use Config\Services;
use Throwable;

final class SmtpParticipantRegistrationMailer implements ParticipantRegistrationMailerInterface
{
    public function send(string $recipient, string $folio, string $categoryName): bool
    {
        $config = config('Email');
        $email = Services::email($config, false);

        try {
            $email->setFrom($config->fromEmail, $config->fromName);
            $email->setTo($recipient);
            $email->setSubject('Registro exitoso · Tesoros Gastronómicos');
            $email->setMessage(view('emails/participant_registration_success', [
                'folio' => $folio,
                'categoryName' => $categoryName,
                'eyebrow' => $categoryName,
                'closeAtLabel' => $this->closeAtLabel(),
            ]));
            $email->setAltMessage(
                "Tu registro fue creado correctamente. Folio: {$folio}. "
                . "Categoría: {$categoryName}. Conserva el folio para consultar tu participación.",
            );

            return $email->send();
        } catch (Throwable $exception) {
            log_message('error', 'Falló el correo de registro exitoso: {type}', [
                'type' => $exception::class,
            ]);

            return false;
        }
    }

    /**
     * Fecha de cierre para el correo. Se omite si no está configurada o es inválida:
     * un dato de calendario no debe impedir el envío de la confirmación.
     */
    private function closeAtLabel(): string
    {
        try {
            $closeAt = (new ConvocationSchedule())->closeAt();
        } catch (Throwable $exception) {
            log_message('error', 'La fecha de cierre configurada no es válida: {type}', [
                'type' => $exception::class,
            ]);

            return '';
        }

        return $closeAt === null ? '' : $closeAt->format('d/m/Y') . ' · ' . $closeAt->format('H:i') . ' h';
    }
}
