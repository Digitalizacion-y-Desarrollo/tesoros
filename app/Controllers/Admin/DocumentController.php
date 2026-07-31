<?php

namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Services\PrivateDocumentStorage;
use App\Services\AuditLogService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;
use DomainException;

final class DocumentController extends AdminController
{
    public function show(int $versionId)
    {
        $db = Database::connect();
        $version = $db->query(
            'SELECT v.*, d.application_id, d.document_type
             FROM document_versions v
             INNER JOIN documents d ON d.id = v.document_id
             WHERE v.id = ?',
            [$versionId],
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
            'application_id' => $version['application_id'],
            'action' => 'admin_document_viewed',
            'from_status' => null,
            'to_status' => null,
            'actor_type' => 'admin',
            'metadata' => json_encode([
                'document_version_id' => $versionId,
                'document_type' => $version['document_type'],
            ], JSON_THROW_ON_ERROR),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        (new AuditLogService($db))->record(
            'admin',
            'admin_document_viewed',
            (int) $version['application_id'],
            (string) $this->session->get('admin_actor_reference'),
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString(),
            ['document_version_id' => $versionId, 'document_type' => $version['document_type']],
        );

        return $this->response->download($path, null)
            ->setFileName((string) $version['original_name'])
            ->inline()
            ->setHeader('Content-Type', (string) $version['mime_type'])
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store');
    }
}
