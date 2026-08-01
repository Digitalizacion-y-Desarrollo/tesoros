<?php

namespace App\Services;

use DomainException;

final class UploadFilenameValidator
{
    /** @var list<string> */
    private const DANGEROUS_INTERMEDIATE_EXTENSIONS = [
        'asp', 'aspx', 'bat', 'cgi', 'cmd', 'com', 'exe', 'htm', 'html',
        'jar', 'js', 'jsp', 'mjs', 'phar', 'php', 'php3', 'php4', 'php5',
        'php7', 'php8', 'phtml', 'pl', 'ps1', 'py', 'rb', 'sh', 'shtml',
        'svg',
    ];

    /**
     * @param list<string> $allowedFinalExtensions
     */
    public function assertSafe(string $clientName, array $allowedFinalExtensions): string
    {
        $name = basename(str_replace('\\', '/', trim($clientName)));
        if ($name === '' || preg_match('/[\x00-\x1F\x7F]/', $name) === 1) {
            throw new DomainException('El nombre del archivo no es válido.');
        }

        $segments = explode('.', strtolower($name));
        $extension = count($segments) > 1 ? (string) array_pop($segments) : '';
        if (! in_array($extension, $allowedFinalExtensions, true)) {
            throw new DomainException('El archivo no tiene una extensión permitida.');
        }

        foreach ($segments as $segment) {
            if (in_array($segment, self::DANGEROUS_INTERMEDIATE_EXTENSIONS, true)) {
                throw new DomainException('El nombre del archivo contiene una extensión intermedia peligrosa.');
            }
        }

        return $extension;
    }
}
