<?php

namespace App\Controllers\Participant;

use App\Controllers\ParticipantController;
use App\Services\PrivateVideoStorage;
use App\Services\AuditLogService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;
use DomainException;

final class VideoController extends ParticipantController
{
    public function show(int $videoId)
    {
        $applicationId = (int) $this->session->get('participant_application_id');
        $db = Database::connect();
        $video = $db->table('application_videos')
            ->where('id', $videoId)
            ->where('application_id', $applicationId)
            ->where('source_type', 'file')
            ->get()
            ->getRowArray();
        if ($video === null || $video['private_path'] === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        try {
            $path = (new PrivateVideoStorage())->absolutePath((string) $video['private_path']);
        } catch (DomainException) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db->table('application_histories')->insert([
            'application_id' => $applicationId,
            'action' => 'participant_video_viewed',
            'from_status' => null,
            'to_status' => null,
            'actor_type' => 'participant',
            'metadata' => json_encode(['video_id' => $videoId], JSON_THROW_ON_ERROR),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        (new AuditLogService($db))->record(
            'participant',
            'participant_video_viewed',
            $applicationId,
            null,
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString(),
            ['video_id' => $videoId],
        );

        return $this->response
            ->download($path, null)
            ->setFileName((string) $video['original_name'])
            ->inline()
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store');
    }
}
