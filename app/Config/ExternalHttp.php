<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

final class ExternalHttp extends BaseConfig
{
    public string $caBundle = '';

    public function __construct()
    {
        parent::__construct();
        $this->caBundle = trim((string) env('http.caBundle', ''));
    }

    public function tlsVerification(bool $enabled = true): bool|string
    {
        if (! $enabled) {
            return false;
        }

        return $this->caBundle !== '' ? $this->caBundle : true;
    }
}
