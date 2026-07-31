<?php

namespace App\Services;

use DomainException;

final class CurpNormalizer
{
    private const STRUCTURAL_PATTERN = '/^[A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d]\d$/';

    public function normalize(string $curp): string
    {
        $normalized = $this->canonicalize($curp);

        if (! preg_match(self::STRUCTURAL_PATTERN, $normalized)) {
            throw new DomainException('La CURP no tiene una estructura válida.');
        }

        return $normalized;
    }

    /**
     * Normaliza para comparar y consultar unicidad sin asumir todavía que la
     * estructura sea válida.
     */
    public function canonicalize(string $curp): string
    {
        return strtoupper((string) preg_replace('/\s+/u', '', trim($curp)));
    }
}
