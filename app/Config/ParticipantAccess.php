<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ParticipantAccess extends BaseConfig
{
    public int $codeTtlSeconds = 600;
    public int $maxAttempts = 5;
    public int $resendCooldownSeconds = 60;
    public int $sessionTtlSeconds = 7200;
    public int $maxRequestsPerIdentity = 5;
    public int $maxRequestsPerIp = 20;
    public int $maxRequestsPerSession = 10;
    public int $rateWindowSeconds = 900;
}
