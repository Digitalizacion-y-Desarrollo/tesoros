<?php

namespace App\Exceptions;

use DomainException;

final class ApplicationValidationException extends DomainException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('La solicitud contiene datos que requieren corrección.');
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
