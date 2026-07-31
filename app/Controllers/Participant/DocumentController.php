<?php

namespace App\Controllers\Participant;

use App\Controllers\ParticipantController;
use App\Services\PrivateDocumentStorage;
use App\Services\AuditLogService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;
use DomainException;

final class DocumentController extends ParticipantController
{
    public function show(int $versionId)
    {
        $applicationId = (int) $this->session->get('participant_application_id');
        $db = Database::connect();
        $version = $db->query(
            'SELECT v.*, d.application_id
             FROM document_versions v
             INNER JOIN documents d ON d.id = v.document_id
                AND d.active_version_number = v.version_number
             WHERE v.id = ? AND d.application_id = ?',
            [$versionId, $applicationId],
        )->getRowArray();
        if ($version === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        try {
            $path = (new PrivateDocumentStorage())->absolutePath((string) $version['private_path']);
        } catch (DomainException) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db->table('application_histories')->insert([
            'application_id' => $applicationId,
            'action' => 'participant_document_viewed',
            'from_status' => null,
            'to_status' => null,
            'actor_type' => 'participant',
            'metadata' => json_encode(['document_version_id' => $versionId], JSON_THROW_ON_ERROR),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        (new AuditLogService($db))->record(
            'participant',
            'participant_document_viewed',
            $applicationId,
            null,
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString(),
            ['document_version_id' => $versionId],
        );

        return $this->response->download($path, null)
            ->setFileName((string) $version['original_name'])
            ->inline()
            ->setHeader('Content-Type', (string) $version['mime_type'])
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store');
    }
}
