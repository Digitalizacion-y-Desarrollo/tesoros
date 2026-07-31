<?php

namespace App\Services;

use App\Exceptions\AccessRateLimitException;
use Closure;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use Config\ParticipantAccess;
use DateTimeImmutable;
use DomainException;
use Throwable;

final class ParticipantAccessService
{
    private BaseConnection $db;
    private ParticipantAccess $config;
    private ParticipantCodeMailerInterface $mailer;
    private Closure $clock;

    public function __construct(
        ?BaseConnection $db = null,
        ?ParticipantCodeMailerInterface $mailer = null,
        ?ParticipantAccess $config = null,
        ?Closure $clock = null,
    ) {
        $this->db = $db ?? Database::connect();
        $this->mailer = $mailer ?? new SmtpParticipantCodeMailer();
        $this->config = $config ?? config('ParticipantAccess');
        $this->clock = $clock ?? static fn (): DateTimeImmutable => new DateTimeImmutable('now');
    }

    /**
     * @return array{
     *     code_id: int|null,
     *     application_id: int|null,
     *     fake_hash: string|null,
     *     expires_at: string,
     *     mail_sent: bool
     * }
     */
    public function requestCode(
        string $email,
        string $folio,
        ?string $ipAddress,
        string $browserSessionId,
        ?string $userAgent,
    ): array {
        $normalizedEmail = $this->normalizeEmailForLookup($email);
        $normalizedFolio = strtoupper(trim($folio));
        $emailHash = hash('sha256', $normalizedEmail);
        $folioHash = hash('sha256', $normalizedFolio);
        $sessionHash = hash('sha256', $browserSessionId);
        $now = ($this->clock)();

        $this->enforceRequestLimits($emailHash, $folioHash, $ipAddress, $sessionHash, $now);

        $application = $this->db->table('applications')
            ->select('id, email, folio')
            ->where('email_hash', $emailHash)
            ->where('folio', $normalizedFolio)
            ->get()
            ->getRowArray();
        $expiresAt = $now->modify("+{$this->config->codeTtlSeconds} seconds");

        if ($application === null) {
            $this->recordEvent(null, 'code_request_unknown', $emailHash, $folioHash, $sessionHash, $ipAddress, $userAgent);

            return [
                'code_id' => null,
                'application_id' => null,
                'fake_hash' => password_hash($this->generateCode(), PASSWORD_DEFAULT),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'mail_sent' => false,
            ];
        }

        $code = $this->generateCode();
        $applicationId = (int) $application['id'];
        $nowSql = $now->format('Y-m-d H:i:s');

        $this->db->resetTransStatus();
        $this->db->transBegin();
        try {
            $lockedApplication = $this->db->query(
                'SELECT id FROM applications WHERE id = ? FOR UPDATE',
                [$applicationId],
            )->getRowArray();
            if ($lockedApplication === null) {
                throw new DomainException('No fue posible generar el código temporal.');
            }

            $this->db->table('access_codes')
                ->where('application_id', $applicationId)
                ->where('used_at', null)
                ->where('invalidated_at', null)
                ->update(['invalidated_at' => $nowSql]);

            $inserted = $this->db->table('access_codes')->insert([
                'application_id' => $applicationId,
                'code_hash' => password_hash($code, PASSWORD_DEFAULT),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'attempts' => 0,
                'max_attempts' => $this->config->maxAttempts,
                'sent_at' => $nowSql,
                'created_at' => $nowSql,
            ]);
            if (! $inserted) {
                throw new DomainException('No fue posible generar el código temporal.');
            }
            $codeId = (int) $this->db->insertID();

            if ($this->db->transStatus() === false) {
                throw new DomainException('No fue posible generar el código temporal.');
            }
            $this->db->transCommit();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }

        $this->recordEvent($applicationId, 'code_requested', $emailHash, $folioHash, $sessionHash, $ipAddress, $userAgent);
        $mailSent = $this->mailer->send(
            (string) $application['email'],
            (string) $application['folio'],
            $code,
            $expiresAt->format('Y-m-d H:i:s'),
        );

        if ($mailSent) {
            $this->recordEvent($applicationId, 'code_email_sent', $emailHash, $folioHash, $sessionHash, $ipAddress, $userAgent);
        } else {
            $this->db->table('access_codes')->where('id', $codeId)->update(['invalidated_at' => $nowSql]);
            $this->recordEvent($applicationId, 'code_email_failed', $emailHash, $folioHash, $sessionHash, $ipAddress, $userAgent);
        }
        try {
            (new EmailNotificationService($this->db))->recordAccessCodeResult(
                $applicationId,
                $codeId,
                $mailSent,
                $expiresAt->format('Y-m-d H:i:s'),
            );
        } catch (Throwable $exception) {
            log_message('error', 'No fue posible registrar el resultado del correo temporal: {type}', [
                'type' => $exception::class,
            ]);
        }

        return [
            'code_id' => $codeId,
            'application_id' => $applicationId,
            'fake_hash' => null,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'mail_sent' => $mailSent,
        ];
    }

    /**
     * @return array{id: int, application_id: int, token: string, expires_at: string}|null
     */
    public function verifyCode(
        ?int $codeId,
        ?int $applicationId,
        string $code,
        ?string $ipAddress,
        string $browserSessionId,
        ?string $userAgent,
    ): ?array {
        $sessionHash = hash('sha256', $browserSessionId);
        $now = ($this->clock)();
        $nowSql = $now->format('Y-m-d H:i:s');

        if ($codeId === null || $applicationId === null) {
            $this->recordEvent(null, 'code_verification_failed', null, null, $sessionHash, $ipAddress, $userAgent);
            return null;
        }

        $this->db->resetTransStatus();
        $this->db->transBegin();
        try {
            $accessCode = $this->db->query(
                'SELECT * FROM access_codes WHERE id = ? AND application_id = ? FOR UPDATE',
                [$codeId, $applicationId],
            )->getRowArray();

            if ($accessCode === null
                || $accessCode['used_at'] !== null
                || $accessCode['invalidated_at'] !== null
                || $accessCode['expires_at'] < $nowSql
                || (int) $accessCode['attempts'] >= (int) $accessCode['max_attempts']
            ) {
                $this->db->transRollback();
                $this->recordEvent($applicationId, 'code_verification_failed', null, null, $sessionHash, $ipAddress, $userAgent);
                return null;
            }

            if (! password_verify(trim($code), (string) $accessCode['code_hash'])) {
                $attempts = ((int) $accessCode['attempts']) + 1;
                $updates = [
                    'attempts' => $attempts,
                    'last_attempt_at' => $nowSql,
                ];
                if ($attempts >= (int) $accessCode['max_attempts']) {
                    $updates['invalidated_at'] = $nowSql;
                }
                $this->db->table('access_codes')->where('id', $codeId)->update($updates);
                $this->db->transCommit();
                $this->recordEvent($applicationId, 'code_verification_failed', null, null, $sessionHash, $ipAddress, $userAgent, [
                    'attempt_number' => $attempts,
                    'locked' => $attempts >= (int) $accessCode['max_attempts'],
                ]);
                return null;
            }

            $this->db->table('access_codes')->where('id', $codeId)->update([
                'used_at' => $nowSql,
                'last_attempt_at' => $nowSql,
            ]);
            $this->db->table('participant_sessions')
                ->where('application_id', $applicationId)
                ->where('revoked_at', null)
                ->update(['revoked_at' => $nowSql]);

            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            $sessionExpiresAt = $now->modify("+{$this->config->sessionTtlSeconds} seconds");
            $inserted = $this->db->table('participant_sessions')->insert([
                'application_id' => $applicationId,
                'token_hash' => hash('sha256', $token),
                'expires_at' => $sessionExpiresAt->format('Y-m-d H:i:s'),
                'last_activity_at' => $nowSql,
                'ip_hash' => $ipAddress !== null ? hash('sha256', $ipAddress) : null,
                'user_agent_hash' => $userAgent !== null ? hash('sha256', $userAgent) : null,
                'created_at' => $nowSql,
            ]);
            if (! $inserted) {
                throw new DomainException('No fue posible crear la sesión temporal.');
            }
            $participantSessionId = (int) $this->db->insertID();

            if ($this->db->transStatus() === false) {
                throw new DomainException('No fue posible crear la sesión temporal.');
            }
            $this->db->transCommit();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }

        $this->recordEvent($applicationId, 'participant_access_granted', null, null, $sessionHash, $ipAddress, $userAgent);

        return [
            'id' => $participantSessionId,
            'application_id' => $applicationId,
            'token' => $token,
            'expires_at' => $sessionExpiresAt->format('Y-m-d H:i:s'),
        ];
    }

    public function validateSession(int $sessionId, int $applicationId, string $token): bool
    {
        $nowSql = ($this->clock)()->format('Y-m-d H:i:s');
        $row = $this->db->table('participant_sessions')
            ->where('id', $sessionId)
            ->where('application_id', $applicationId)
            ->where('token_hash', hash('sha256', $token))
            ->where('revoked_at', null)
            ->where('expires_at >=', $nowSql)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return false;
        }

        $this->db->table('participant_sessions')->where('id', $sessionId)->update([
            'last_activity_at' => $nowSql,
        ]);

        return true;
    }

    public function revokeSession(?int $sessionId, ?int $applicationId): void
    {
        if ($sessionId === null || $applicationId === null) {
            return;
        }

        $nowSql = ($this->clock)()->format('Y-m-d H:i:s');
        $this->db->table('participant_sessions')
            ->where('id', $sessionId)
            ->where('application_id', $applicationId)
            ->where('revoked_at', null)
            ->update(['revoked_at' => $nowSql]);
        $this->recordEvent($applicationId, 'participant_logout', null, null, null, null, null);
    }

    private function enforceRequestLimits(
        string $emailHash,
        string $folioHash,
        ?string $ipAddress,
        string $sessionHash,
        DateTimeImmutable $now,
    ): void {
        $cooldownCutoff = $now->modify("-{$this->config->resendCooldownSeconds} seconds")->format('Y-m-d H:i:s');
        $recentIdentityRequest = $this->requestEvents()
            ->where('email_hash', $emailHash)
            ->where('folio_hash', $folioHash)
            ->where('created_at >=', $cooldownCutoff)
            ->countAllResults();
        if ($recentIdentityRequest > 0) {
            throw new AccessRateLimitException($this->config->resendCooldownSeconds);
        }

        $windowCutoff = $now->modify("-{$this->config->rateWindowSeconds} seconds")->format('Y-m-d H:i:s');
        $emailRequests = $this->requestEvents()
            ->where('email_hash', $emailHash)
            ->where('created_at >=', $windowCutoff)
            ->countAllResults();
        $folioRequests = $this->requestEvents()
            ->where('folio_hash', $folioHash)
            ->where('created_at >=', $windowCutoff)
            ->countAllResults();
        $ipRequests = $ipAddress !== null
            ? $this->requestEvents()->where('ip_address', $ipAddress)->where('created_at >=', $windowCutoff)->countAllResults()
            : 0;
        $sessionRequests = $this->requestEvents()
            ->where('session_hash', $sessionHash)
            ->where('created_at >=', $windowCutoff)
            ->countAllResults();

        if ($emailRequests >= $this->config->maxRequestsPerIdentity
            || $folioRequests >= $this->config->maxRequestsPerIdentity
            || $ipRequests >= $this->config->maxRequestsPerIp
            || $sessionRequests >= $this->config->maxRequestsPerSession
        ) {
            throw new AccessRateLimitException($this->config->rateWindowSeconds);
        }
    }

    private function requestEvents()
    {
        return $this->db->table('participant_access_events')
            ->whereIn('event', ['code_requested', 'code_request_unknown']);
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function recordEvent(
        ?int $applicationId,
        string $event,
        ?string $emailHash,
        ?string $folioHash,
        ?string $sessionHash,
        ?string $ipAddress,
        ?string $userAgent,
        array $metadata = [],
    ): void {
        $this->db->table('participant_access_events')->insert([
            'application_id' => $applicationId,
            'event' => $event,
            'email_hash' => $emailHash,
            'folio_hash' => $folioHash,
            'session_hash' => $sessionHash,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 500) : null,
            'metadata' => $metadata !== [] ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
            'created_at' => ($this->clock)()->format('Y-m-d H:i:s'),
        ]);
    }

    private function normalizeEmailForLookup(string $email): string
    {
        try {
            return (new EmailNormalizer())->normalize($email);
        } catch (DomainException) {
            return mb_strtolower(trim($email));
        }
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
