<?php

namespace App\Services;

use App\Exceptions\ApplicationValidationException;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Database;
use DomainException;
use Throwable;

final class ApplicationDocumentService
{
    private BaseConnection $db;
    private PrivateDocumentStorage $storage;

    public function __construct(?BaseConnection $db = null, ?PrivateDocumentStorage $storage = null)
    {
        $this->db = $db ?? Database::connect();
        $this->storage = $storage ?? new PrivateDocumentStorage();
    }

    /**
     * @param list<array<string, mixed>> $definitions
     * @param array<string, UploadedFile|null> $files
     * @param list<string> $removals
     * @return list<string> newly stored private paths, for rollback cleanup
     */
    public function synchronizeDraft(int $applicationId, array $definitions, array $files, array $removals): array
    {
        $definitionMap = [];
        foreach ($definitions as $definition) {
            $definitionMap[(string) $definition['type']] = $definition;
        }
        $newPaths = [];
        $errors = [];

        foreach ($definitionMap as $type => $definition) {
            $file = $files[$type] ?? null;
            $hasUpload = $this->storage->hasUpload($file);
            $remove = in_array($type, $removals, true);
            if ($hasUpload && $remove) {
                $errors["documents.{$type}"] = 'Elige reemplazar o eliminar el documento, no ambas opciones.';
                continue;
            }

            $document = $this->db->table('documents')
                ->where('application_id', $applicationId)
                ->where('document_type', $type)
                ->get()
                ->getRowArray();

            if ($remove && $document !== null) {
                if ((int) $document['is_locked'] === 1) {
                    $errors["documents.{$type}"] = 'Este documento está bloqueado.';
                    continue;
                }
                $this->assertWrite($this->db->table('documents')->where('id', $document['id'])->update([
                    'active_version_number' => null,
                    'removed_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]));
                continue;
            }
            if (! $hasUpload || $file === null) {
                continue;
            }

            try {
                $stored = $this->storage->store($applicationId, $type, $definition, $file);
            } catch (DomainException $exception) {
                $errors["documents.{$type}"] = $exception->getMessage();
                continue;
            }
            $newPaths[] = $stored['private_path'];
            $now = date('Y-m-d H:i:s');

            if ($document === null) {
                $this->assertWrite($this->db->table('documents')->insert([
                    'application_id' => $applicationId,
                    'document_type' => $type,
                    'label' => $definition['label'],
                    'is_required' => ! empty($definition['required']) ? 1 : 0,
                    'is_locked' => 0,
                    'correction_unlocked' => 0,
                    'active_version_number' => null,
                    'removed_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
                $documentId = (int) $this->db->insertID();
                $nextVersion = 1;
            } else {
                if ((int) $document['is_locked'] === 1) {
                    $errors["documents.{$type}"] = 'Este documento está bloqueado.';
                    $this->storage->delete($stored['private_path']);
                    array_pop($newPaths);
                    continue;
                }
                $documentId = (int) $document['id'];
                $nextVersion = (int) ($this->db->table('document_versions')
                    ->selectMax('version_number', 'max_version')
                    ->where('document_id', $documentId)
                    ->get()->getRow('max_version') ?? 0) + 1;
            }

            $this->assertWrite($this->db->table('document_versions')->insert([
                'document_id' => $documentId,
                'version_number' => $nextVersion,
                'private_path' => $stored['private_path'],
                'original_name' => $stored['original_name'],
                'mime_type' => $stored['mime_type'],
                'size_bytes' => $stored['size_bytes'],
                'sha256' => $stored['sha256'],
                'uploaded_by_type' => 'participant',
                'uploaded_by_reference' => null,
                'created_at' => $now,
            ]));
            $this->assertWrite($this->db->table('documents')->where('id', $documentId)->update([
                'label' => $definition['label'],
                'is_required' => ! empty($definition['required']) ? 1 : 0,
                'active_version_number' => $nextVersion,
                'removed_at' => null,
                'updated_at' => $now,
            ]));
        }

        if ($errors !== []) {
            foreach ($newPaths as $path) {
                $this->storage->delete($path);
            }
            throw new ApplicationValidationException($errors);
        }

        return $newPaths;
    }

    /** @param list<array<string, mixed>> $definitions */
    public function assertRequiredPresent(int $applicationId, array $definitions): void
    {
        $active = [];
        foreach ($this->db->table('documents')->select('document_type, active_version_number')
            ->where('application_id', $applicationId)->get()->getResultArray() as $row) {
            if ($row['active_version_number'] !== null) {
                $active[(string) $row['document_type']] = true;
            }
        }
        $errors = [];
        foreach ($definitions as $definition) {
            $type = (string) $definition['type'];
            if (! empty($definition['required']) && ! isset($active[$type])) {
                $errors["documents.{$type}"] = 'Este documento es obligatorio para enviar.';
            }
        }
        if ($errors !== []) {
            throw new ApplicationValidationException($errors);
        }
    }

    public function lockAll(int $applicationId): void
    {
        $this->assertWrite($this->db->table('documents')->where('application_id', $applicationId)->update([
            'is_locked' => 1,
            'correction_unlocked' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    /**
     * Creates an immutable correction version. The application returns to enviada
     * only after every document requested in the same correction cycle is replaced.
     *
     * @param array<string, mixed> $definition
     */
    public function replaceUnlocked(
        int $applicationId,
        string $documentType,
        array $definition,
        UploadedFile $file,
    ): bool {
        $newPath = null;
        $correctionCycleCompleted = false;
        $this->db->resetTransStatus();
        $this->db->transBegin();

        try {
            $document = $this->db->query(
                'SELECT d.*, a.status
                 FROM documents d
                 INNER JOIN applications a ON a.id = d.application_id
                 WHERE d.application_id = ? AND d.document_type = ?
                 FOR UPDATE',
                [$applicationId, $documentType],
            )->getRowArray();
            if ($document === null || $document['status'] !== 'incompleta'
                || (int) $document['correction_unlocked'] !== 1) {
                throw new DomainException('Este documento no está habilitado para corrección.');
            }

            $stored = $this->storage->store($applicationId, $documentType, $definition, $file);
            $newPath = $stored['private_path'];
            $previousVersion = (int) $document['active_version_number'];
            $nextVersion = (int) ($this->db->table('document_versions')
                ->selectMax('version_number', 'max_version')
                ->where('document_id', $document['id'])
                ->get()->getRow('max_version') ?? 0) + 1;
            $now = date('Y-m-d H:i:s');

            $this->assertWrite($this->db->table('document_versions')->insert([
                'document_id' => $document['id'],
                'version_number' => $nextVersion,
                'private_path' => $stored['private_path'],
                'original_name' => $stored['original_name'],
                'mime_type' => $stored['mime_type'],
                'size_bytes' => $stored['size_bytes'],
                'sha256' => $stored['sha256'],
                'uploaded_by_type' => 'participant',
                'uploaded_by_reference' => null,
                'created_at' => $now,
            ]));
            $this->assertWrite($this->db->table('documents')->where('id', $document['id'])->update([
                'active_version_number' => $nextVersion,
                'is_locked' => 1,
                'correction_unlocked' => 0,
                'removed_at' => null,
                'updated_at' => $now,
            ]));
            $remainingCorrections = $this->db->table('documents')
                ->where('application_id', $applicationId)
                ->where('correction_unlocked', 1)
                ->countAllResults();
            $targetStatus = $remainingCorrections === 0 ? 'enviada' : 'incompleta';
            $correctionCycleCompleted = $remainingCorrections === 0;
            $applicationUpdate = $this->db->table('applications')
                ->set('version', 'version + 1', false)
                ->set('status', $targetStatus)
                ->set('updated_at', $now)
                ->where('id', $applicationId)
                ->where('status', 'incompleta')
                ->update();
            $this->assertWrite($applicationUpdate);
            $this->assertWrite($this->db->table('application_histories')->insert([
                'application_id' => $applicationId,
                'action' => 'document_correction_submitted',
                'from_status' => 'incompleta',
                'to_status' => $targetStatus,
                'actor_type' => 'participant',
                'metadata' => json_encode([
                    'document_type' => $documentType,
                    'previous_version' => $previousVersion,
                    'new_version' => $nextVersion,
                    'remaining_corrections' => $remainingCorrections,
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
            ]));

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('No fue posible registrar la corrección.');
            }
            $this->db->transCommit();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            $this->storage->delete($newPath);
            throw $exception;
        }

        try {
            (new EmailNotificationService($this->db))->enqueueAndAttempt(
                $applicationId,
                'correction_received',
                ['document_label' => $documentType],
            );
        } catch (Throwable $exception) {
            log_message('error', 'No fue posible encolar el correo de corrección recibida: {type}', [
                'type' => $exception::class,
            ]);
        }

        return $correctionCycleCompleted;
    }

    /** @return list<array<string, mixed>> */
    public function listing(int $applicationId, array $definitions): array
    {
        $rows = $this->db->query(
            'SELECT d.*, v.id AS version_id, v.original_name, v.mime_type, v.size_bytes, v.sha256, v.created_at AS uploaded_at
             FROM documents d
             LEFT JOIN document_versions v ON v.document_id = d.id AND v.version_number = d.active_version_number
             WHERE d.application_id = ?',
            [$applicationId],
        )->getResultArray();
        $byType = [];
        foreach ($rows as $row) {
            $byType[(string) $row['document_type']] = $row;
        }
        $result = [];
        foreach ($definitions as $definition) {
            $type = (string) $definition['type'];
            $result[] = $definition + ['current' => $byType[$type] ?? null];
        }

        return $result;
    }

    public function cleanup(array $paths): void
    {
        foreach ($paths as $path) {
            $this->storage->delete($path);
        }
    }

    private function assertWrite(bool $succeeded): void
    {
        if (! $succeeded) {
            $error = $this->db->error();
            throw new DatabaseException($error['message'] ?: 'No fue posible guardar el documento.', (int) $error['code']);
        }
    }
}
