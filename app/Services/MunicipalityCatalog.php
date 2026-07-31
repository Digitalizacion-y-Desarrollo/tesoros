<?php

namespace App\Services;

use Config\Municipalities;

final class MunicipalityCatalog
{
    private Municipalities $config;

    public function __construct(?Municipalities $config = null)
    {
        $this->config = $config ?? config('Municipalities');
    }

    /**
     * @return list<string>
     */
    public function all(): array
    {
        return array_values($this->config->items);
    }

    public function count(): int
    {
        return count($this->config->items);
    }

    public function canonicalize(string $municipality): ?string
    {
        $candidate = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $municipality) ?? ''));

        foreach ($this->config->items as $officialName) {
            if (mb_strtolower($officialName) === $candidate) {
                return $officialName;
            }
        }

        return null;
    }
}
