<?php

namespace App\Services;

use App\Domain\ApplicationStatus;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\ApplicationForms;
use Config\Database;
use DomainException;
use Throwable;

final class ApplicationLifecycleService
{
    private BaseConnection $db;
    private ApplicationForms $forms;

    public function __construct(?BaseConnection $db = null, ?ApplicationForms $forms = null)
    {
        $this->db = $db ?? Database::connect();
        $this->forms = $forms ?? config('ApplicationForms');
    }

    public function changeStatus(int $applicationId, string $target, ?string $comment, ?string $actor): void
    {
        if ($target === ApplicationStatus::Incomplete->value) {
            throw new DomainException('Para solicitar una corrección debes seleccionar un documento.');
        }

        $this->transactional(function (array $application) use ($applicationId, $target, $comment, $actor): void {
            $from = (string) $application['status'];
            if (! in_array($target, ApplicationStatus::allowedAdminTransitions($from), true)) {
                throw new DomainException('La transición de estado solicitada no está permitida.');
            }
            $now = date('Y-m-d H:i:s');
            $this->assertWrite($this->db->table('applications')->where('id', $applicationId)
                ->where('status', $from)->update([
                    'status' => $target,
                    'version' => ((int) $application['version']) + 1,
                    'updated_at' => $now,
                ]));
            if (trim((string) $comment) !== '') {
                $this->addComment($applicationId, trim((string) $comment), false, $actor, null, $now);
            }
            $this->history($applicationId, 'admin_status_changed', $from, $target, $actor, [
                'comment_provided' => trim((string) $comment) !== '',
            ], $now);
        }, $applicationId);

        $event = match ($target) {
            ApplicationStatus::Selected->value => 'application_selected',
            ApplicationStatus::Rejected->value => 'application_rejected',
            default => null,
        };
        if ($event !== null) {
            $this->notifySafely($applicationId, $event);
        }
    }

    public function requestCorrection(
        int $applicationId,
        int $documentId,
        string $comment,
        ?string $actor,
    ): void {
        $this->requestCorrections($applicationId, [$documentId], $comment, $actor);
    }

    /** @param list<int> $documentIds */
    public function requestCorrections(
        int $applicationId,
        array $documentIds,
        string $comment,
        ?string $actor,
    ): void {
        $comment = trim($comment);
        if ($comment === '' || mb_strlen($comment) > 4000) {
            throw new DomainException('Escribe un comentario de corrección de hasta 4000 caracteres.');
        }
        $documentIds = array_values(array_unique(array_filter(
            array_map(static fn ($id): int => (int) $id, $documentIds),
            static fn (int $id): bool => $id > 0,
        )));
        if ($documentIds === []) {
            throw new DomainException('Selecciona al menos un documento vigente de esta solicitud.');
        }

        $this->transactional(function (array $application) use (
            $applicationId,
            $documentIds,
            $comment,
            $actor,
        ): void {
            $from = (string) $application['status'];
            if (! in_array(ApplicationStatus::Incomplete->value, ApplicationStatus::allowedAdminTransitions($from), true)) {
                throw new DomainException('La solicitud no puede marcarse como incompleta desde su estado actual.');
            }
            $documents = $this->db->table('documents')
                ->select('id, document_type, label')
                ->where('application_id', $applicationId)
                ->where('active_version_number IS NOT NULL', null, false)
                ->whereIn('id', $documentIds)
                ->get()->getResultArray();
            if (count($documents) !== count($documentIds)) {
                throw new DomainException('Uno o más documentos seleccionados no pertenecen a esta solicitud o no tienen versión vigente.');
            }
            $now = date('Y-m-d H:i:s');
            $this->assertWrite($this->db->table('documents')->where('application_id', $applicationId)->update([
                'is_locked' => 1,
                'correction_unlocked' => 0,
                'updated_at' => $now,
            ]));
            $this->assertWrite($this->db->table('documents')->whereIn('id', $documentIds)->update([
                'is_locked' => 1,
                'correction_unlocked' => 1,
                'updated_at' => $now,
            ]));
            $this->assertWrite($this->db->table('applications')->where('id', $applicationId)
                ->where('status', $from)->update([
                    'status' => ApplicationStatus::Incomplete->value,
                    'version' => ((int) $application['version']) + 1,
                    'updated_at' => $now,
                ]));
            $this->addComment($applicationId, $comment, true, $actor, null, $now);
            $this->history($applicationId, 'document_correction_requested', $from, 'incompleta', $actor, [
                'document_ids' => $documentIds,
                'document_types' => array_column($documents, 'document_type'),
                'document_count' => count($documents),
            ], $now);
        }, $applicationId);

        $documents = $this->db->table('documents')->select('label')
            ->whereIn('id', $documentIds)->orderBy('id', 'ASC')->get()->getResultArray();
        $this->notifySafely($applicationId, 'correction_requested', [
            'comment' => $comment,
            'document_label' => implode(', ', array_column($documents, 'label')),
        ]);
    }

    public function cancelByParticipant(
        int $applicationId,
        bool $confirmed,
        ?string $ipAddress,
        ?string $userAgent,
    ): void {
        if (! $confirmed) {
            throw new DomainException('Debes confirmar expresamente la cancelación irreversible.');
        }

        $this->transactional(function (array $application) use ($applicationId, $ipAddress, $userAgent): void {
            $from = (string) $application['status'];
            if (! ApplicationStatus::canParticipantCancel($from)) {
                throw new DomainException('La solicitud ya no puede cancelarse desde su estado actual.');
            }
            $now = date('Y-m-d H:i:s');
            $this->assertWrite($this->db->table('applications')->where('id', $applicationId)
                ->where('status', $from)->update([
                    'status' => 'cancelada',
                    'version' => ((int) $application['version']) + 1,
                    'cancelled_at' => $now,
                    'updated_at' => $now,
                ]));
            $this->assertWrite($this->db->table('documents')->where('application_id', $applicationId)->update([
                'is_locked' => 1,
                'correction_unlocked' => 0,
                'updated_at' => $now,
            ]));
            $this->history($applicationId, 'application_cancelled', $from, 'cancelada', null, [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 250) : null,
            ], $now, 'participant');
        }, $applicationId);

        $this->notifySafely($applicationId, 'application_cancelled');
    }

    private function transactional(callable $operation, int $applicationId): void
    {
        $this->db->resetTransStatus();
        $this->db->transBegin();
        try {
            $application = $this->db->query(
                'SELECT * FROM applications WHERE id = ? FOR UPDATE',
                [$applicationId],
            )->getRowArray();
            if ($application === null) {
                throw new DomainException('No fue posible localizar la solicitud.');
            }
            $operation($application);
            if ($this->db->transStatus() === false) {
                throw new DatabaseException('No fue posible actualizar la solicitud.');
            }
            $this->db->transCommit();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    private function addComment(
        int $applicationId,
        string $comment,
        bool $visible,
        ?string $actor,
        ?int $documentId,
        string $now,
    ): void {
        $data = [
            'application_id' => $applicationId,
            'document_id' => $documentId,
            'comment' => $comment,
            'is_visible_to_participant' => $visible ? 1 : 0,
            'actor_reference' => $actor,
            'created_at' => $now,
        ];
        $this->assertWrite($this->db->table('admin_comments')->insert($data));
    }

    private function history(
        int $applicationId,
        string $action,
        ?string $from,
        ?string $to,
        ?string $actor,
        array $metadata,
        string $now,
        string $actorType = 'admin',
    ): void {
        $this->assertWrite($this->db->table('application_histories')->insert([
            'application_id' => $applicationId,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'actor_type' => $actorType,
            'actor_reference' => $actor,
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => $now,
        ]));
    }

    private function assertWrite(bool $success): void
    {
        if (! $success) {
            $error = $this->db->error();
            throw new DatabaseException($error['message'] ?: 'No fue posible actualizar la solicitud.', (int) $error['code']);
        }
    }

    private function notifySafely(int $applicationId, string $event, array $context = []): void
    {
        try {
            (new EmailNotificationService($this->db))->enqueueAndAttempt(
                $applicationId,
                $event,
                $context,
            );
        } catch (Throwable $exception) {
            log_message('error', 'No fue posible encolar una notificación de estado: {type}', [
                'type' => $exception::class,
            ]);
        }
    }
}
