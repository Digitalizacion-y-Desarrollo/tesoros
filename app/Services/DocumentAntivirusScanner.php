<?php

namespace App\Services;

use Config\DocumentUploads;
use DomainException;

final class DocumentAntivirusScanner
{
    public function __construct(private readonly ?DocumentUploads $config = null)
    {
    }

    public function assertSafe(string $filePath): void
    {
        $config = $this->config ?? config('DocumentUploads');
        if ($config->antivirusCommand === '') {
            if (ENVIRONMENT !== 'production' && $config->allowDevelopmentAntivirusBypass) {
                log_message('warning', 'Análisis antivirus omitido mediante bypass de desarrollo.');
                return;
            }
            throw new DomainException('El análisis antivirus no está configurado. No es posible recibir el archivo.');
        }
        if (! str_contains($config->antivirusCommand, '{file}')) {
            throw new DomainException('El comando antivirus debe incluir el marcador {file}.');
        }
        if (! is_callable('exec')) {
            throw new DomainException('El análisis antivirus no está disponible en el servidor.');
        }

        $output = [];
        $exitCode = 1;
        exec(str_replace('{file}', escapeshellarg($filePath), $config->antivirusCommand), $output, $exitCode);
        if ($exitCode !== 0) {
            throw new DomainException('El archivo no superó el análisis de seguridad.');
        }
    }
}
