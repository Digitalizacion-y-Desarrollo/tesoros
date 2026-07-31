<?php

namespace App\Services;

use Config\VideoUploads;
use DomainException;

final class VideoAntivirusScanner
{
    public function __construct(private readonly ?VideoUploads $config = null)
    {
    }

    public function assertSafe(string $filePath): void
    {
        $config = $this->config ?? config('VideoUploads');
        if ($config->antivirusCommand === '') {
            if (ENVIRONMENT !== 'production' && $config->allowDevelopmentAntivirusBypass) {
                log_message('warning', 'Análisis antivirus de video omitido mediante bypass de desarrollo.');
                return;
            }

            throw new DomainException('El análisis antivirus no está configurado. No es posible recibir el video.');
        }

        $command = str_replace('{file}', escapeshellarg($filePath), $config->antivirusCommand);
        if (! str_contains($config->antivirusCommand, '{file}')) {
            throw new DomainException('El comando antivirus debe incluir el marcador {file}.');
        }
        if (! is_callable('exec')) {
            throw new DomainException('El análisis antivirus no está disponible en el servidor.');
        }

        $output = [];
        $exitCode = 1;
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new DomainException('El video no superó el análisis de seguridad.');
        }
    }
}
