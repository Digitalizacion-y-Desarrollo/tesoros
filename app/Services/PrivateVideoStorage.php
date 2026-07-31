<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\VideoUploads;
use DomainException;
use Throwable;

final class PrivateVideoStorage
{
    private VideoUploads $config;
    private VideoAntivirusScanner $scanner;

    public function __construct(?VideoUploads $config = null, ?VideoAntivirusScanner $scanner = null)
    {
        $this->config = $config ?? config('VideoUploads');
        $this->scanner = $scanner ?? new VideoAntivirusScanner($this->config);
    }

    public function hasUpload(?UploadedFile $file): bool
    {
        return $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * @return array{private_path: string, original_name: string, mime_type: string, size_bytes: int, sha256: string}
     */
    public function store(int $applicationId, UploadedFile $file): array
    {
        $this->assertValid($file);
        $temporaryPath = $file->getTempName();
        $this->scanner->assertSafe($temporaryPath);

        $relativeDirectory = trim($this->config->relativeDirectory, '/\\')
            . '/' . $applicationId;
        $absoluteDirectory = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);
        if (! is_dir($absoluteDirectory) && ! mkdir($absoluteDirectory, 0750, true) && ! is_dir($absoluteDirectory)) {
            throw new DomainException('No fue posible preparar el almacenamiento privado del video.');
        }

        $storedName = bin2hex(random_bytes(32)) . '.mp4';
        $size = $file->getSize();
        $hash = hash_file('sha256', $temporaryPath);
        if ($hash === false) {
            throw new DomainException('No fue posible verificar la integridad del video.');
        }

        try {
            $file->move($absoluteDirectory, $storedName, false);
        } catch (Throwable $exception) {
            log_message('error', 'No fue posible mover el video al almacenamiento privado: {type}', [
                'type' => $exception::class,
            ]);
            throw new DomainException('No fue posible guardar el video. Inténtalo nuevamente.');
        }
        $safeOriginalName = preg_replace('/[^\pL\pN._ -]/u', '_', basename($file->getClientName()));

        return [
            'private_path' => $relativeDirectory . '/' . $storedName,
            'original_name' => mb_substr($safeOriginalName ?: 'video.mp4', 0, 255),
            'mime_type' => 'video/mp4',
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
            throw new DomainException('No fue posible localizar el video privado.');
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
            log_message('warning', 'No fue posible eliminar una versión reemplazada de video privado.');
        }
    }

    private function assertValid(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            $message = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'El video excede el límite permitido por el servidor.'
                : 'La carga del video no se completó correctamente.';
            throw new DomainException($message);
        }

        $originalName = basename($file->getClientName());
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $stem = pathinfo($originalName, PATHINFO_FILENAME);
        if ($extension !== 'mp4' || str_contains($stem, '.')) {
            throw new DomainException('El video debe ser un archivo MP4 sin extensiones dobles.');
        }
        if ($file->getSize() <= 0 || $file->getSize() > $this->config->maxBytes) {
            throw new DomainException('El video no puede exceder 500 MB.');
        }
        if ($file->getMimeType() !== 'video/mp4') {
            throw new DomainException('El contenido del archivo no corresponde a un video MP4 válido.');
        }
    }
}
