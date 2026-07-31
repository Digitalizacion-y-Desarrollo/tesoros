<?php

namespace App\Services;

interface AdminAccessApiClientInterface
{
    /** @return array<string, mixed> */
    public function login(string $email, string $password): array;

    /** @return array<string, mixed> */
    public function me(string $token): array;

    public function logout(string $token): void;

    public function forgotPassword(string $email, string $loginUrl): void;
}
