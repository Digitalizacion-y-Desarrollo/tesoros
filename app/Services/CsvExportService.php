<?php

namespace App\Services;

final class CsvExportService
{
    private const HEADERS = [
        'Folio',
        'Categoría',
        'Estado',
        'Municipio',
        'Correo',
        'CURP responsable',
        'Nombre',
        'Primer apellido',
        'Segundo apellido',
        'Creación',
        'Envío',
        'Actualización',
    ];

    public function generate(array $rows): string
    {
        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            throw new \RuntimeException('No fue posible preparar la exportación.');
        }
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, self::HEADERS);

        foreach ($rows as $row) {
            fputcsv($stream, array_map(
                fn (mixed $value): string => $this->safeCell($value),
                [
                    $row['folio'] ?? '',
                    $row['category_name'] ?? '',
                    $row['status'] ?? '',
                    $row['municipality'] ?? '',
                    $row['email'] ?? '',
                    $row['curp'] ?? '',
                    $row['first_name'] ?? '',
                    $row['last_name'] ?? '',
                    $row['second_last_name'] ?? '',
                    $row['created_at'] ?? '',
                    $row['submitted_at'] ?? '',
                    $row['updated_at'] ?? '',
                ],
            ));
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        if ($csv === false) {
            throw new \RuntimeException('No fue posible generar la exportación.');
        }

        return $csv;
    }

    public function safeCell(mixed $value): string
    {
        $cell = str_replace("\0", '', trim((string) $value));
        if (preg_match('/^[\x00-\x20]*[=+\-@]/u', $cell) === 1) {
            return "'" . $cell;
        }

        return $cell;
    }
}
