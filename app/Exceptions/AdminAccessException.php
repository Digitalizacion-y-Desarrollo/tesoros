<?php

namespace App\Exceptions;

use RuntimeException;

final class AdminAccessException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $httpStatus = null,
    ) {
        parent::__construct($message);
    }
}
