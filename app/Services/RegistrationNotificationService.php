<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;

final class RegistrationNotificationService
{
    private BaseConnection $db;
    private ?ParticipantRegistrationMailerInterface $mailer;
    private EmailNotificationService $notifications;

    public function __construct(
        ?BaseConnection $db = null,
        ?ParticipantRegistrationMailerInterface $mailer = null,
        ?EmailNotificationService $notifications = null,
    ) {
        $this->db = $db ?? Database::connect();
        $this->mailer = $mailer;
        $this->notifications = $notifications ?? new EmailNotificationService($this->db);
    }

    public function send(int $applicationId): bool
    {
        $application = $this->db->table('applications a')
            ->select('a.email, a.folio, c.name AS category_name')
            ->join('categories c', 'c.id = a.category_id')
            ->where('a.id', $applicationId)
            ->get()
            ->getRowArray();
        if ($application === null) {
            throw new DomainException('No fue posible localizar el registro para notificarlo.');
        }

        $sent = $this->mailer !== null
            ? $this->mailer->send(
                (string) $application['email'],
                (string) $application['folio'],
                (string) $application['category_name'],
            )
            : $this->notifications->enqueueAndAttempt(
                $applicationId,
                'registration_created',
                [],
                'registration_created:' . $applicationId,
            );
        $this->db->table('application_histories')->insert([
            'application_id' => $applicationId,
            'action' => $sent ? 'registration_email_sent' : 'registration_email_failed',
            'from_status' => 'borrador',
            'to_status' => 'borrador',
            'actor_type' => 'system',
            'metadata' => json_encode(['template' => 'participant_registration_success'], JSON_THROW_ON_ERROR),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $sent;
    }
}
