<?php

namespace App\Services;

class UnconfiguredAdminAuthProvider implements AdminAuthProviderInterface
{
    public function isConfigured(): bool
    {
        return false;
    }

    public function isAuthenticated(): bool
    {
        return false;
    }

    public function attempt(string $email, string $password): bool
    {
        return false;
    }

    public function user(): ?array
    {
        return null;
    }

    public function logout(): void
    {
    }

    public function forgotPassword(string $email, string $loginUrl): bool
    {
        return false;
    }

    public function lastError(): string
    {
        return 'La autenticación administrativa no está configurada.';
    }
}
