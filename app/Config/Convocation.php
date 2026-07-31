<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

final class Convocation extends BaseConfig
{
    public string $closeAt = '';

    public function __construct()
    {
        parent::__construct();
        $this->closeAt = trim((string) env('convocation.closeAt', ''));
    }
}
