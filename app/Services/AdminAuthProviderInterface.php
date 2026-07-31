<?php

namespace App\Services;

interface AdminAuthProviderInterface
{
    public function isConfigured(): bool;

    public function isAuthenticated(): bool;

    public function attempt(string $email, string $password): bool;

    /** @return array<string, mixed>|null */
    public function user(): ?array;

    public function logout(): void;

    public function forgotPassword(string $email, string $loginUrl): bool;

    public function lastError(): string;
}
