<?php

namespace App\Services;

use App\Exceptions\AdminAccessException;
use CodeIgniter\HTTP\CURLRequest;
use Config\AdminAccess;
use Config\Services;
use Throwable;

final class DigitalNezaAdminAccessApiClient implements AdminAccessApiClientInterface
{
    private AdminAccess $config;
    private CURLRequest $client;

    public function __construct(?AdminAccess $config = null, ?CURLRequest $client = null)
    {
        $this->config = $config ?? config('AdminAccess');
        $externalHttp = config('ExternalHttp');
        $this->client = $client ?? Services::curlrequest([
            'baseURI' => $this->config->baseUrl,
            'headers' => ['Accept' => 'application/json'],
            'timeout' => $this->config->timeoutSeconds,
            'connect_timeout' => $this->config->connectTimeoutSeconds,
            'verify' => $externalHttp->tlsVerification($this->config->verifyTls),
            'http_errors' => false,
        ], null, null, false);
    }

    public function login(string $email, string $password): array
    {
        return $this->request('POST', 'api/auth/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
                'system_key' => $this->config->systemKey,
            ],
        ]);
    }

    public function me(string $token): array
    {
        return $this->request('GET', 'api/auth/me', [
            'query' => ['system_key' => $this->config->systemKey],
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
    }

    public function logout(string $token): void
    {
        $this->request('POST', 'api/auth/logout', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
    }

    public function forgotPassword(string $email, string $loginUrl): void
    {
        $this->request('POST', 'api/auth/forgot-password', [
            'json' => ['email' => $email, 'login_url' => $loginUrl],
        ]);
    }

    /** @return array<string, mixed> */
    private function request(string $method, string $path, array $options): array
    {
        $options['http_errors'] = false;
        try {
            $response = $this->client->request($method, $path, $options);
        } catch (Throwable $exception) {
            log_message('error', 'El proveedor administrativo no respondió: {type}', ['type' => $exception::class]);
            throw new AdminAccessException('El servicio institucional de acceso no está disponible.');
        }

        $status = $response->getStatusCode();
        $decoded = json_decode((string) $response->getBody(), true);
        if (! is_array($decoded)) {
            throw new AdminAccessException('El servicio institucional devolvió una respuesta inválida.', $status);
        }
        if ($status < 200 || $status >= 300) {
            $message = match ($status) {
                401 => 'El correo o la contraseña son incorrectos, o la sesión expiró.',
                403 => 'La cuenta no tiene acceso activo a este sistema.',
                404 => 'La clave del sistema no está registrada en el proveedor.',
                422 => 'Los datos de acceso no tienen el formato requerido.',
                default => 'El servicio institucional no pudo procesar la solicitud.',
            };
            throw new AdminAccessException($message, $status);
        }

        return $decoded;
    }
}
