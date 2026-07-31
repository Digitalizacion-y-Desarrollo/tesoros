<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Database;
use DateTimeImmutable;
use DomainException;
use Throwable;

final class EmailNotificationService
{
    private const DEFINITIONS = [
        'registration_created' => [
            'template' => 'participant_registration_success',
            'subject' => 'Registro exitoso · Tesoros Gastronómicos',
        ],
        'application_submitted' => [
            'template' => 'application_event',
            'subject' => 'Solicitud enviada · Tesoros Gastronómicos',
        ],
        'correction_requested' => [
            'template' => 'application_event',
            'subject' => 'Corrección requerida · Tesoros Gastronómicos',
        ],
        'correction_received' => [
            'template' => 'application_event',
            'subject' => 'Corrección recibida · Tesoros Gastronómicos',
        ],
        'application_selected' => [
            'template' => 'application_event',
            'subject' => 'Resultado de tu participación · Tesoros Gastronómicos',
        ],
        'application_rejected' => [
            'template' => 'application_event',
            'subject' => 'Resultado de tu participación · Tesoros Gastronómicos',
        ],
        'application_cancelled' => [
            'template' => 'application_event',
            'subject' => 'Solicitud cancelada · Tesoros Gastronómicos',
        ],
    ];

    private BaseConnection $db;
    private EmailSenderInterface $sender;

    public function __construct(?BaseConnection $db = null, ?EmailSenderInterface $sender = null)
    {
        $this->db = $db ?? Database::connect();
        $this->sender = $sender ?? new SmtpEmailSender();
    }

    public function enqueueApplication(
        int $applicationId,
        string $event,
        array $context = [],
        ?string $idempotencyReference = null,
    ): int {
        $definition = self::DEFINITIONS[$event] ?? null;
        if ($definition === null) {
            throw new DomainException('El evento de correo indicado no está registrado.');
        }

        $application = $this->db->table('applications a')
            ->select('a.id, a.email, a.folio, a.status, a.version, c.name AS category_name')
            ->join('categories c', 'c.id = a.category_id')
            ->where('a.id', $applicationId)
            ->get()
            ->getRowArray();
        if ($application === null) {
            throw new DomainException('No fue posible localizar la solicitud para notificarla.');
        }

        $reference = $idempotencyReference
            ?? $event . ':' . $applicationId . ':' . (int) ($application['version'] ?? 0);
        $idempotencyKey = hash('sha256', $reference);
        $existing = $this->db->table('email_queue')
            ->select('id')
            ->where('idempotency_key', $idempotencyKey)
            ->get()
            ->getRowArray();
        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $payload = array_merge([
            'folio' => (string) $application['folio'],
            'category_name' => (string) $application['category_name'],
            'status' => (string) $application['status'],
        ], $this->sanitizeContext($context));
        $now = date('Y-m-d H:i:s');
        try {
            $inserted = $this->db->table('email_queue')->insert([
                'application_id' => $applicationId,
                'event' => $event,
                'template' => $definition['template'],
                'recipient_email' => (string) $application['email'],
                'subject' => $definition['subject'],
                'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                'idempotency_key' => $idempotencyKey,
                'status' => 'pending',
                'attempts' => 0,
                'max_attempts' => 5,
                'available_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (DatabaseException $exception) {
            $duplicate = $this->db->table('email_queue')
                ->select('id')
                ->where('idempotency_key', $idempotencyKey)
                ->get()
                ->getRowArray();
            if ($duplicate !== null) {
                return (int) $duplicate['id'];
            }
            throw $exception;
        }
        if (! $inserted) {
            $duplicate = $this->db->table('email_queue')
                ->select('id')
                ->where('idempotency_key', $idempotencyKey)
                ->get()
                ->getRowArray();
            if ($duplicate !== null) {
                return (int) $duplicate['id'];
            }
            throw new DatabaseException('No fue posible encolar la notificación.');
        }

        return (int) $this->db->insertID();
    }

    public function enqueueAndAttempt(
        int $applicationId,
        string $event,
        array $context = [],
        ?string $idempotencyReference = null,
    ): bool {
        $queueId = $this->enqueueApplication(
            $applicationId,
            $event,
            $context,
            $idempotencyReference,
        );

        return ENVIRONMENT === 'testing' || $this->attempt($queueId);
    }

    public function attempt(int $queueId): bool
    {
        $this->db->resetTransStatus();
        $this->db->transBegin();

        try {
            $sql = 'SELECT * FROM email_queue WHERE id = ?';
            if ($this->db->DBDriver === 'MySQLi') {
                $sql .= ' FOR UPDATE';
            }
            $item = $this->db->query($sql, [$queueId])->getRowArray();
            if ($item === null || $item['status'] === 'sent') {
                $this->db->transCommit();
                return $item !== null;
            }
            $now = new DateTimeImmutable('now');
            if ((int) $item['attempts'] >= (int) $item['max_attempts']
                || new DateTimeImmutable((string) $item['available_at']) > $now) {
                $this->db->transCommit();
                return false;
            }

            $attempt = (int) $item['attempts'] + 1;
            $this->db->table('email_queue')->where('id', $queueId)->update([
                'status' => 'processing',
                'attempts' => $attempt,
                'locked_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ]);
            if ($this->db->transStatus() === false) {
                throw new DatabaseException('No fue posible reservar la notificación.');
            }
            $this->db->transCommit();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }

        $payload = json_decode((string) $item['payload'], true);
        $payload = is_array($payload) ? $payload : [];
        $payload['categoryName'] = (string) ($payload['category_name'] ?? '');
        $content = $this->content((string) $item['event'], $payload);
        $sent = $this->sender->send(
            (string) $item['recipient_email'],
            (string) $item['subject'],
            view('emails/' . $item['template'], array_merge($payload, $content)),
            $content['plain_text'],
        );
        $finishedAt = new DateTimeImmutable('now');
        $attempt = (int) $item['attempts'] + 1;
        $exhausted = $attempt >= (int) $item['max_attempts'];
        $this->db->table('email_queue')->where('id', $queueId)->update($sent ? [
            'status' => 'sent',
            'sent_at' => $finishedAt->format('Y-m-d H:i:s'),
            'locked_at' => null,
            'last_error' => null,
            'updated_at' => $finishedAt->format('Y-m-d H:i:s'),
        ] : [
            'status' => $exhausted ? 'failed' : 'pending',
            'available_at' => $finishedAt->modify('+' . $this->backoffSeconds($attempt) . ' seconds')
                ->format('Y-m-d H:i:s'),
            'locked_at' => null,
            'last_error' => 'El servidor SMTP no confirmó el envío.',
            'updated_at' => $finishedAt->format('Y-m-d H:i:s'),
        ]);

        return $sent;
    }

    public function processPending(int $limit = 25): array
    {
        $limit = max(1, min(100, $limit));
        $rows = $this->db->table('email_queue')
            ->select('id')
            ->where('attempts < max_attempts', null, false)
            ->groupStart()
                ->groupStart()
                    ->where('status', 'pending')
                    ->where('available_at <=', date('Y-m-d H:i:s'))
                ->groupEnd()
                ->orGroupStart()
                    ->where('status', 'processing')
                    ->where('locked_at <', date('Y-m-d H:i:s', time() - 900))
                ->groupEnd()
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $sent = 0;
        $failed = 0;
        foreach ($rows as $row) {
            try {
                $this->db->table('email_queue')->where('id', (int) $row['id'])
                    ->where('status', 'processing')
                    ->update(['status' => 'pending', 'locked_at' => null]);
                $this->attempt((int) $row['id']) ? $sent++ : $failed++;
            } catch (Throwable $exception) {
                $failed++;
                log_message('error', 'Falló el procesamiento de una notificación: {type}', [
                    'type' => $exception::class,
                ]);
            }
        }

        return ['processed' => count($rows), 'sent' => $sent, 'failed' => $failed];
    }

    public function recordAccessCodeResult(
        int $applicationId,
        int $codeId,
        bool $sent,
        string $expiresAt,
    ): void {
        $application = $this->db->table('applications')->select('email, folio')
            ->where('id', $applicationId)->get()->getRowArray();
        if ($application === null) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('email_queue')->ignore(true)->insert([
            'application_id' => $applicationId,
            'event' => 'access_code',
            'template' => 'participant_access_code',
            'recipient_email' => $application['email'],
            'subject' => 'Código temporal de acceso · Tesoros Gastronómicos',
            'payload' => json_encode([
                'folio' => $application['folio'],
                'expires_at' => $expiresAt,
                'sensitive_payload_stored' => false,
            ], JSON_THROW_ON_ERROR),
            'idempotency_key' => hash('sha256', 'access_code:' . $codeId),
            'status' => $sent ? 'sent' : 'failed',
            'attempts' => 1,
            'max_attempts' => 1,
            'available_at' => $now,
            'sent_at' => $sent ? $now : null,
            'last_error' => $sent ? null : 'El servidor SMTP no confirmó el envío.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function sanitizeContext(array $context): array
    {
        $allowed = ['comment', 'document_label'];
        $clean = [];
        foreach ($allowed as $key) {
            if (isset($context[$key]) && is_scalar($context[$key])) {
                $clean[$key] = mb_substr(trim((string) $context[$key]), 0, 4000);
            }
        }

        return $clean;
    }

    private function content(string $event, array $payload): array
    {
        $folio = (string) ($payload['folio'] ?? '');
        $category = (string) ($payload['category_name'] ?? '');
        $content = match ($event) {
            'application_submitted' => [
                'title' => 'Tu solicitud fue enviada',
                'message' => 'Recibimos tu solicitud definitiva. A partir de este momento permanecerá bloqueada mientras continúa el proceso de revisión.',
                'detail' => 'Estado actual: Enviada.',
            ],
            'correction_requested' => [
                'title' => 'Necesitamos una corrección',
                'message' => 'La administración solicitó reemplazar un documento de tu solicitud.',
                'detail' => 'Documento: ' . ($payload['document_label'] ?? 'documento indicado')
                    . '. Comentario: ' . ($payload['comment'] ?? 'Consulta el detalle de tu solicitud.'),
            ],
            'correction_received' => [
                'title' => 'Recibimos tu corrección',
                'message' => 'La nueva versión del documento quedó registrada y tu solicitud volvió al estado Enviada.',
                'detail' => 'La versión anterior se conserva de forma privada para auditoría.',
            ],
            'application_selected' => [
                'title' => 'Tu solicitud fue seleccionada',
                'message' => 'Tu participación cambió al estado Seleccionada.',
                'detail' => 'Consulta el portal privado para revisar el estado actualizado.',
            ],
            'application_rejected' => [
                'title' => 'Resultado de tu solicitud',
                'message' => 'Tu participación cambió al estado Rechazada.',
                'detail' => 'Consulta el portal privado para revisar el estado actualizado.',
            ],
            'application_cancelled' => [
                'title' => 'Tu solicitud fue cancelada',
                'message' => 'Confirmamos la cancelación irreversible de tu solicitud.',
                'detail' => 'El registro se conserva únicamente para trazabilidad y auditoría.',
            ],
            default => [
                'title' => 'Notificación de tu solicitud',
                'message' => 'Hay una actualización relacionada con tu participación.',
                'detail' => 'Consulta el portal privado para conocer el estado actual.',
            ],
        };
        $content['eyebrow'] = $category;
        $content['plain_text'] = $content['title'] . "\n\n"
            . $content['message'] . "\n" . $content['detail']
            . "\n\nFolio: {$folio}\nCategoría: {$category}";

        return $content;
    }

    private function backoffSeconds(int $attempt): int
    {
        return min(3600, 60 * (2 ** max(0, $attempt - 1)));
    }
}
