<?php

namespace App\Services;

use Config\Services;
use Throwable;

final class RecaptchaVerifier
{
    public function siteKey(): string
    {
        return trim((string) env('recaptcha.siteKey', ''));
    }

    public function isConfigured(): bool
    {
        return $this->siteKey() !== '' && trim((string) env('recaptcha.secretKey', '')) !== '';
    }

    public function verify(string $token, ?string $remoteIp = null): bool
    {
        $secret = trim((string) env('recaptcha.secretKey', ''));

        if ($secret === '') {
            return ENVIRONMENT !== 'production'
                && filter_var(env('recaptcha.allowDevelopmentBypass', true), FILTER_VALIDATE_BOOL);
        }

        if (trim($token) === '') {
            return false;
        }

        try {
            $externalHttp = config('ExternalHttp');
            $response = Services::curlrequest()->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'form_params' => array_filter([
                        'secret'   => $secret,
                        'response' => $token,
                        'remoteip' => $remoteIp,
                    ]),
                    'timeout' => 5,
                    'connect_timeout' => 3,
                    'verify' => $externalHttp->tlsVerification(),
                ],
            );
            $payload = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            return ($payload['success'] ?? false) === true;
        } catch (Throwable $exception) {
            log_message('warning', 'No fue posible validar reCAPTCHA: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
