<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

final class VideoUploads extends BaseConfig
{
    public int $maxBytes = 524_288_000;
    public string $relativeDirectory = 'private/uploads/videos';
    public string $antivirusCommand = '';
    public bool $allowDevelopmentAntivirusBypass = true;

    public function __construct()
    {
        parent::__construct();

        $this->antivirusCommand = trim((string) env('videoUploads.antivirusCommand', ''));
        $this->allowDevelopmentAntivirusBypass = filter_var(
            env('videoUploads.allowDevelopmentAntivirusBypass', true),
            FILTER_VALIDATE_BOOL,
        );
    }
}
