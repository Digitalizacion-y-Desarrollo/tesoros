<?php

namespace App\Services;

use App\Exceptions\AdminAccessException;
use CodeIgniter\Session\Session;
use Config\AdminAccess;
use DomainException;
use Throwable;

final class DigitalNezaAdminAuthProvider implements AdminAuthProviderInterface
{
    private AdminAccess $config;
    private AdminAccessApiClientInterface $client;
    private Session $session;
    private string $error = '';

    public function __construct(
        ?AdminAccess $config = null,
        ?AdminAccessApiClientInterface $client = null,
        ?Session $session = null,
    ) {
        $this->config = $config ?? config('AdminAccess');
        $this->client = $client ?? new DigitalNezaAdminAccessApiClient($this->config);
        $this->session = $session ?? service('session');
    }

    public function isConfigured(): bool
    {
        return $this->config->systemKey !== ''
            && filter_var($this->config->baseUrl, FILTER_VALIDATE_URL) !== false
            && str_starts_with(strtolower($this->config->baseUrl), 'https://')
            && $this->config->verifyTls;
    }

    public function isAuthenticated(): bool
    {
        $token = (string) $this->session->get('admin_access_token');
        if (! $this->isConfigured() || ! $this->session->get('admin_authenticated') || $token === '') {
            return false;
        }

        try {
            $response = $this->client->me($token);
            $data = $this->validatedData($response, false);
            $this->storeUser($data);
            return true;
        } catch (Throwable $exception) {
            $this->error = $exception instanceof AdminAccessException || $exception instanceof DomainException
                ? $exception->getMessage()
                : 'No fue posible validar la sesión administrativa.';
            $this->clearLocalAuthentication();
            return false;
        }
    }

    public function attempt(string $email, string $password): bool
    {
        $this->error = '';
        $email = strtolower(trim($email));
        if (! $this->isConfigured()) {
            $this->error = 'La autenticación administrativa no está configurada.';
            return false;
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || $password === '') {
            $this->error = 'Captura un correo y contraseña válidos.';
            return false;
        }
        if ((int) $this->session->get('admin_login_blocked_until') > time()) {
            $this->error = 'Demasiados intentos. Espera un minuto antes de volver a intentar.';
            return false;
        }

        try {
            $response = $this->client->login($email, $password);
            $data = $this->validatedData($response, true);
            $token = (string) $data['access_token'];
            $this->session->regenerate(true);
            $this->session->set([
                'admin_authenticated' => true,
                'admin_access_token' => $token,
                'admin_login_failures' => 0,
                'admin_login_blocked_until' => null,
            ]);
            $this->storeUser($data);
            return true;
        } catch (Throwable $exception) {
            $failures = ((int) $this->session->get('admin_login_failures')) + 1;
            $this->session->set('admin_login_failures', $failures);
            if ($failures >= 5) {
                $this->session->set('admin_login_blocked_until', time() + 60);
            }
            $this->error = $exception instanceof AdminAccessException || $exception instanceof DomainException
                ? $exception->getMessage()
                : 'No fue posible iniciar sesión con el proveedor institucional.';
            return false;
        }
    }

    public function user(): ?array
    {
        $user = $this->session->get('admin_user');
        return is_array($user) ? $user : null;
    }

    public function logout(): void
    {
        $token = (string) $this->session->get('admin_access_token');
        try {
            if ($token !== '') {
                $this->client->logout($token);
            }
        } catch (Throwable $exception) {
            log_message('warning', 'El cierre de sesión central falló: {type}', ['type' => $exception::class]);
        } finally {
            $this->clearLocalAuthentication();
            $this->session->regenerate(true);
        }
    }

    public function forgotPassword(string $email, string $loginUrl): bool
    {
        $this->error = '';
        $email = strtolower(trim($email));
        if (! $this->isConfigured() || filter_var($email, FILTER_VALIDATE_EMAIL) === false
            || filter_var($loginUrl, FILTER_VALIDATE_URL) === false
            || (! str_starts_with(strtolower($loginUrl), 'https://')
                && ! (ENVIRONMENT !== 'production' && str_starts_with(strtolower($loginUrl), 'http://')))) {
            $this->error = 'No fue posible solicitar la recuperación de acceso.';
            return false;
        }
        try {
            $this->client->forgotPassword($email, $loginUrl);
            return true;
        } catch (Throwable) {
            $this->error = 'No fue posible solicitar la recuperación de acceso.';
            return false;
        }
    }

    public function lastError(): string
    {
        return $this->error;
    }

    /** @return array<string, mixed> */
    private function validatedData(array $response, bool $requiresToken): array
    {
        $data = $response['data'] ?? null;
        if (! is_array($data) || ! is_array($data['user'] ?? null) || ! is_array($data['system'] ?? null)) {
            throw new DomainException('El proveedor devolvió datos de usuario incompletos.');
        }
        $returnedKey = (string) ($data['system']['clave'] ?? '');
        if ($returnedKey === '' || ! hash_equals($this->config->systemKey, $returnedKey)) {
            throw new DomainException('El proveedor respondió para un sistema diferente.');
        }
        if ($requiresToken && (! is_string($data['access_token'] ?? null) || $data['access_token'] === '')) {
            throw new DomainException('El proveedor no devolvió un token de acceso.');
        }

        return $data;
    }

    private function storeUser(array $data): void
    {
        $user = $data['user'];
        $safe = [
            'id' => (string) ($user['id'] ?? ''),
            'name' => mb_substr(trim((string) ($user['name'] ?? '')), 0, 120),
            'apellido_paterno' => mb_substr(trim((string) ($user['apellido_paterno'] ?? '')), 0, 120),
            'apellido_materno' => mb_substr(trim((string) ($user['apellido_materno'] ?? '')), 0, 120),
            'email' => mb_substr(strtolower(trim((string) ($user['email'] ?? ''))), 0, 254),
            'departamento' => is_array($user['departamento'] ?? null) ? $user['departamento'] : [],
            'roles' => array_values(array_filter($data['roles'] ?? [], 'is_string')),
            'permissions' => array_values(array_filter($data['permissions'] ?? [], 'is_string')),
        ];
        if ($safe['id'] === '' || filter_var($safe['email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('El proveedor devolvió una identidad administrativa inválida.');
        }
        $this->session->set([
            'admin_user' => $safe,
            'admin_actor_reference' => 'access-api:' . $safe['id'],
        ]);
    }

    private function clearLocalAuthentication(): void
    {
        $this->session->remove([
            'admin_authenticated',
            'admin_access_token',
            'admin_user',
            'admin_actor_reference',
        ]);
    }
}
