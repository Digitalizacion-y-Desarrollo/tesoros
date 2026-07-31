<?php

namespace App\Services;

use DomainException;

final class EmailNormalizer
{
    public function normalize(string $email): string
    {
        $normalized = mb_strtolower(trim($email), 'UTF-8');

        if (strlen($normalized) > 254 || filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('El correo electrónico no es válido.');
        }

        return $normalized;
    }
}
