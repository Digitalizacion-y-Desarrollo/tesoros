<?php

namespace App\Services;

use Config\Convocation;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;

final class ConvocationSchedule
{
    public function __construct(private readonly ?Convocation $config = null)
    {
    }

    public function isClosed(?DateTimeImmutable $now = null): bool
    {
        $closeAt = $this->closeAt();
        if ($closeAt === null) {
            return false;
        }

        return ($now ?? new DateTimeImmutable('now', $closeAt->getTimezone())) >= $closeAt;
    }

    public function assertRegistrationOpen(): void
    {
        if ($this->isClosed()) {
            throw new DomainException('La convocatoria está cerrada y ya no admite nuevos registros.');
        }
    }

    public function assertDraftEditingOpen(): void
    {
        if ($this->isClosed()) {
            throw new DomainException('La convocatoria está cerrada. Ya no es posible guardar o enviar borradores.');
        }
    }

    public function closeAt(): ?DateTimeImmutable
    {
        $value = ($this->config ?? config('Convocation'))->closeAt;
        if ($value === '') {
            return null;
        }

        $timezone = new DateTimeZone('America/Mexico_City');
        $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value, $timezone)
            ?: DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value, $timezone);
        if ($date === false) {
            throw new DomainException('La fecha de cierre configurada no es válida.');
        }

        return $date;
    }
}
