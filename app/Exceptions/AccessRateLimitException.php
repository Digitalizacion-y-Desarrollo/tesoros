<?php

namespace App\Exceptions;

use DomainException;

final class AccessRateLimitException extends DomainException
{
    public function __construct(private readonly int $retryAfterSeconds)
    {
        parent::__construct('Espera antes de solicitar otro código.');
    }

    public function retryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }
}
