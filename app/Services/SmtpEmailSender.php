<?php

namespace App\Services;

use Config\Services;
use Throwable;

final class SmtpEmailSender implements EmailSenderInterface
{
    public function send(string $recipient, string $subject, string $html, string $plainText): bool
    {
        $config = config('Email');
        $email = Services::email($config, false);

        try {
            $email->setFrom($config->fromEmail, $config->fromName);
            $email->setTo($recipient);
            $email->setSubject($subject);
            $email->setMessage($html);
            $email->setAltMessage($plainText);

            return $email->send();
        } catch (Throwable $exception) {
            log_message('error', 'Falló un envío SMTP institucional: {type}', [
                'type' => $exception::class,
            ]);

            return false;
        }
    }
}
