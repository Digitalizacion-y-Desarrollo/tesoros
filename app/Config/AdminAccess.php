<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

final class AdminAccess extends BaseConfig
{
    public string $baseUrl = 'https://accesos.digitalneza.com/';
    public string $systemKey = '';
    public int $timeoutSeconds = 10;
    public int $connectTimeoutSeconds = 5;
    public bool $verifyTls = true;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = rtrim((string) env('adminAuth.baseUrl', $this->baseUrl), '/') . '/';
        $this->systemKey = trim((string) env('adminAuth.systemKey', ''));
        $this->timeoutSeconds = max(2, (int) env('adminAuth.timeoutSeconds', 10));
        $this->connectTimeoutSeconds = max(2, (int) env('adminAuth.connectTimeoutSeconds', 5));
        $this->verifyTls = filter_var(env('adminAuth.verifyTls', true), FILTER_VALIDATE_BOOL);
    }
}
