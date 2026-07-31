<?php

use App\Services\AdminAccessApiClientInterface;
use App\Services\DigitalNezaAdminAuthProvider;
use CodeIgniter\Test\CIUnitTestCase;
use Config\AdminAccess;

/**
 * @internal
 */
final class DigitalNezaAdminAuthProviderTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        service('session')->remove([
            'admin_authenticated',
            'admin_access_token',
            'admin_user',
            'admin_actor_reference',
            'admin_login_failures',
            'admin_login_blocked_until',
        ]);
        parent::tearDown();
    }

    public function testLoginValidationAndLogoutUseTheInstitutionalContract(): void
    {
        $config = $this->config();
        $client = new FakeAdminAccessClient('system-test');
        $provider = new DigitalNezaAdminAuthProvider($config, $client, service('session'));

        $this->assertTrue($provider->isConfigured());
        $this->assertTrue($provider->attempt('ADMIN@EXAMPLE.TEST', 'password'));
        $this->assertTrue($provider->isAuthenticated());
        $this->assertSame('7', $provider->user()['id']);
        $this->assertSame('admin@example.test', $provider->user()['email']);
        $this->assertSame('access-api:7', service('session')->get('admin_actor_reference'));

        $provider->logout();
        $this->assertFalse((bool) service('session')->get('admin_authenticated'));
        $this->assertTrue($client->logoutCalled);
    }

    public function testResponseForAnotherSystemIsRejected(): void
    {
        $provider = new DigitalNezaAdminAuthProvider(
            $this->config(),
            new FakeAdminAccessClient('different-system'),
            service('session'),
        );

        $this->assertFalse($provider->attempt('admin@example.test', 'password'));
        $this->assertStringContainsString('sistema diferente', $provider->lastError());
        $this->assertFalse((bool) service('session')->get('admin_authenticated'));
    }

    public function testMissingSystemKeyFailsClosed(): void
    {
        $config = $this->config();
        $config->systemKey = '';
        $provider = new DigitalNezaAdminAuthProvider(
            $config,
            new FakeAdminAccessClient('system-test'),
            service('session'),
        );

        $this->assertFalse($provider->isConfigured());
        $this->assertFalse($provider->attempt('admin@example.test', 'password'));
    }

    private function config(): AdminAccess
    {
        $config = new AdminAccess();
        $config->baseUrl = 'https://access.example.test/';
        $config->systemKey = 'system-test';
        $config->verifyTls = true;
        return $config;
    }
}

final class FakeAdminAccessClient implements AdminAccessApiClientInterface
{
    public bool $logoutCalled = false;

    public function __construct(private readonly string $returnedSystemKey)
    {
    }

    public function login(string $email, string $password): array
    {
        return $this->response() + ['received_email' => $email];
    }

    public function me(string $token): array
    {
        return $this->response();
    }

    public function logout(string $token): void
    {
        $this->logoutCalled = true;
    }

    public function forgotPassword(string $email, string $loginUrl): void
    {
    }

    /** @return array<string, mixed> */
    private function response(): array
    {
        return [
            'message' => 'OK',
            'data' => [
                'access_token' => 'opaque-test-token',
                'token_type' => 'Bearer',
                'user' => [
                    'id' => 7,
                    'name' => 'Admin',
                    'apellido_paterno' => 'Prueba',
                    'apellido_materno' => '',
                    'email' => 'admin@example.test',
                    'departamento' => [],
                ],
                'system' => ['id' => 2, 'nombre' => 'Tesoros', 'clave' => $this->returnedSystemKey],
                'roles' => ['administrador'],
                'permissions' => ['solicitudes.ver'],
            ],
        ];
    }
}
