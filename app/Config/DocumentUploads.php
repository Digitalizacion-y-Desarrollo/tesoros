<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

final class DocumentUploads extends BaseConfig
{
    public int $maxBytes = 524_288_000;
    public string $relativeDirectory = 'private/uploads/documents';
    public string $antivirusCommand = '';
    public bool $allowDevelopmentAntivirusBypass = true;

    public function __construct()
    {
        parent::__construct();
        $this->antivirusCommand = trim((string) env('uploads.antivirusCommand', ''));
        $this->allowDevelopmentAntivirusBypass = filter_var(
            env('uploads.allowDevelopmentAntivirusBypass', true),
            FILTER_VALIDATE_BOOL,
        );
    }
}
