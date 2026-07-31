<?php

namespace App\Filters;

use App\Services\ParticipantAccessService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ParticipantSessionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (! $session->get('participant_authenticated')) {
            return redirect()->route('participant.access')
                ->with('message', 'Solicita un código temporal para consultar tu participación.');
        }

        if ($session->get('participant_access_scope') !== 'temporary') {
            return null;
        }

        $valid = (new ParticipantAccessService())->validateSession(
            (int) $session->get('participant_session_id'),
            (int) $session->get('participant_application_id'),
            (string) $session->get('participant_session_token'),
        );
        if ($valid) {
            return null;
        }

        $session->destroy();

        return redirect()->route('participant.access')
            ->with('error', 'La sesión temporal expiró. Solicita un nuevo código.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
