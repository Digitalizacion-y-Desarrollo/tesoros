<?php

namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Services\PrivateVideoStorage;
use App\Services\AuditLogService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;
use DomainException;

final class VideoController extends AdminController
{
    public function show(int $videoId)
    {
        $db = Database::connect();
        $video = $db->table('application_videos')
            ->where('id', $videoId)
            ->where('source_type', 'file')
            ->get()->getRowArray();
        if ($video === null || $video['private_path'] === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        try {
            $path = (new PrivateVideoStorage())->absolutePath((string) $video['private_path']);
        } catch (DomainException) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db->table('application_histories')->insert([
            'application_id' => $video['application_id'],
            'action' => 'admin_video_viewed',
            'from_status' => null,
            'to_status' => null,
            'actor_type' => 'admin',
            'metadata' => json_encode(['video_id' => $videoId], JSON_THROW_ON_ERROR),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        (new AuditLogService($db))->record(
            'admin',
            'admin_video_viewed',
            (int) $video['application_id'],
            (string) $this->session->get('admin_actor_reference'),
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString(),
            ['video_id' => $videoId],
        );

        return $this->response->download($path, null)
            ->setFileName((string) $video['original_name'])
            ->inline()
            ->setHeader('Content-Type', 'video/mp4')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store');
    }
}
