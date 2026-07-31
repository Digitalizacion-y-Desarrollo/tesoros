<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use Throwable;

final class AuditLogService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= Database::connect();
    }

    public function record(
        string $actorType,
        string $action,
        ?int $applicationId = null,
        ?string $actorReference = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $metadata = [],
    ): void {
        $safeMetadata = $this->sanitizeMetadata($metadata);
        try {
            $this->db->table('audit_log')->insert([
                'application_id' => $applicationId,
                'actor_type' => mb_substr($actorType, 0, 30),
                'actor_reference' => $actorReference !== null ? mb_substr($actorReference, 0, 120) : null,
                'action' => mb_substr($action, 0, 80),
                'ip_address' => $ipAddress !== null ? mb_substr($ipAddress, 0, 45) : null,
                'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 500) : null,
                'metadata' => $safeMetadata !== [] ? json_encode($safeMetadata, JSON_THROW_ON_ERROR) : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'No fue posible registrar un evento de auditoría: {type}', [
                'type' => $exception::class,
            ]);
        }
    }

    public function listing(int $page = 1, int $perPage = 50): array
    {
        $page = max(1, $page);
        $perPage = max(10, min(100, $perPage));
        $builder = $this->db->table('audit_log l')
            ->select('l.*, a.folio')
            ->join('applications a', 'a.id = l.application_id', 'left');
        $total = (clone $builder)->countAllResults();

        return [
            'rows' => $builder->orderBy('l.created_at', 'DESC')
                ->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray(),
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'total' => $total,
        ];
    }

    private function sanitizeMetadata(array $metadata): array
    {
        $blocked = ['password', 'token', 'access_token', 'code', 'code_hash', 'secret', 'document_content'];
        $safe = [];
        foreach ($metadata as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            if (in_array($normalizedKey, $blocked, true)) {
                continue;
            }
            if (is_scalar($value) || $value === null) {
                $safe[$key] = is_string($value) ? mb_substr($value, 0, 1000) : $value;
            }
        }

        return $safe;
    }
}
