<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\DocumentUploads;
use DomainException;
use Throwable;

final class PrivateDocumentStorage
{
    private DocumentUploads $config;
    private DocumentAntivirusScanner $scanner;

    public function __construct(?DocumentUploads $config = null, ?DocumentAntivirusScanner $scanner = null)
    {
        $this->config = $config ?? config('DocumentUploads');
        $this->scanner = $scanner ?? new DocumentAntivirusScanner($this->config);
    }

    public function hasUpload(?UploadedFile $file): bool
    {
        return $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * @param array<string, mixed> $definition
     * @return array{private_path:string, original_name:string, mime_type:string, size_bytes:int, sha256:string}
     */
    public function store(int $applicationId, string $documentType, array $definition, UploadedFile $file): array
    {
        [$mime, $extension] = $this->assertValid($definition, $file);
        $temporaryPath = $file->getTempName();
        $this->scanner->assertSafe($temporaryPath);
        $relativeDirectory = trim($this->config->relativeDirectory, '/\\') . '/' . $applicationId . '/' . $documentType;
        $absoluteDirectory = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);
        if (! is_dir($absoluteDirectory) && ! mkdir($absoluteDirectory, 0750, true) && ! is_dir($absoluteDirectory)) {
            throw new DomainException('No fue posible preparar el almacenamiento privado del archivo.');
        }

        $storedName = bin2hex(random_bytes(32)) . '.' . $extension;
        try {
            $file->move($absoluteDirectory, $storedName, false);
        } catch (Throwable $exception) {
            log_message('error', 'No fue posible mover un documento privado: {type}', ['type' => $exception::class]);
            throw new DomainException('No fue posible guardar el archivo. Inténtalo nuevamente.');
        }
        $absolutePath = $absoluteDirectory . DIRECTORY_SEPARATOR . $storedName;
        if ($mime === 'image/jpeg') {
            $this->stripJpegMetadataWhenViable($absolutePath);
        }
        $hash = hash_file('sha256', $absolutePath);
        $size = filesize($absolutePath);
        if ($hash === false || $size === false) {
            @unlink($absolutePath);
            throw new DomainException('No fue posible verificar la integridad del archivo.');
        }
        $safeName = preg_replace('/[^\pL\pN._ -]/u', '_', basename($file->getClientName()));

        return [
            'private_path' => $relativeDirectory . '/' . $storedName,
            'original_name' => mb_substr($safeName ?: 'documento.' . $extension, 0, 255),
            'mime_type' => $mime,
            'size_bytes' => $size,
            'sha256' => $hash,
        ];
    }

    public function absolutePath(string $relativePath): string
    {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($relativePath, '/\\'));
        $base = realpath(WRITEPATH . trim($this->config->relativeDirectory, '/\\'));
        $candidate = realpath(WRITEPATH . $normalized);
        if ($base === false || $candidate === false || ! str_starts_with($candidate, $base . DIRECTORY_SEPARATOR)) {
            throw new DomainException('No fue posible localizar el archivo privado.');
        }

        return $candidate;
    }

    public function delete(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }
        try {
            $path = $this->absolutePath($relativePath);
        } catch (DomainException) {
            return;
        }
        if (is_file($path) && ! unlink($path)) {
            log_message('warning', 'No fue posible limpiar un archivo privado después de una operación fallida.');
        }
    }

    /**
     * @param array<string, mixed> $definition
     * @return array{string,string}
     */
    private function assertValid(array $definition, UploadedFile $file): array
    {
        if (! $file->isValid()) {
            $message = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'El archivo excede el límite permitido por el servidor.'
                : 'La carga del archivo no se completó correctamente.';
            throw new DomainException($message);
        }
        $name = basename($file->getClientName());
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (! in_array($extension, ['pdf', 'jpg', 'jpeg'], true) || str_contains(pathinfo($name, PATHINFO_FILENAME), '.')) {
            throw new DomainException('Solo se permiten PDF, JPG o JPEG sin extensiones dobles.');
        }
        if ($file->getSize() <= 0 || $file->getSize() > $this->config->maxBytes) {
            throw new DomainException('El archivo no puede exceder 500 MB.');
        }
        $mime = $file->getMimeType();
        $mimeExtension = match ($mime) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            default => null,
        };
        if ($mimeExtension === null || ($mimeExtension === 'pdf' && $extension !== 'pdf')
            || ($mimeExtension === 'jpg' && ! in_array($extension, ['jpg', 'jpeg'], true))) {
            throw new DomainException('El contenido real del archivo no coincide con su extensión.');
        }
        $accept = (string) ($definition['accept'] ?? 'pdf,image');
        if (($accept === 'pdf' && $mime !== 'application/pdf') || ($accept === 'image' && $mime !== 'image/jpeg')) {
            throw new DomainException($accept === 'pdf'
                ? 'Este documento debe cargarse en formato PDF.'
                : 'Este documento debe cargarse en formato JPG o JPEG.');
        }

        return [$mime, $mimeExtension];
    }

    private function stripJpegMetadataWhenViable(string $path): void
    {
        if (! function_exists('imagecreatefromjpeg') || ! function_exists('imagejpeg')) {
            log_message('warning', 'No se retiraron metadatos JPEG porque GD no está disponible.');
            return;
        }
        $dimensions = @getimagesize($path);
        if ($dimensions === false || ((int) $dimensions[0] * (int) $dimensions[1]) > 40_000_000) {
            log_message('warning', 'No se retiraron metadatos JPEG por dimensiones inválidas o excesivas.');
            return;
        }
        $image = @imagecreatefromjpeg($path);
        if ($image === false) {
            return;
        }
        $temporary = $path . '.clean';
        $written = @imagejpeg($image, $temporary, 92);
        imagedestroy($image);
        if ($written && @rename($temporary, $path)) {
            return;
        }
        @unlink($temporary);
        log_message('warning', 'No fue posible retirar los metadatos del archivo JPEG.');
    }
}
