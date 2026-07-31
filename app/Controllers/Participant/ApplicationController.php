<?php

namespace App\Controllers\Participant;

use App\Controllers\ParticipantController;
use App\Exceptions\ApplicationValidationException;
use App\Services\ApplicationWorkflowService;
use App\Services\MunicipalityCatalog;
use App\Services\ApplicationDocumentService;
use App\Services\ApplicationLifecycleService;
use App\Services\AuditLogService;
use CodeIgniter\HTTP\Files\UploadedFile;
use DomainException;

class ApplicationController extends ParticipantController
{
    public function show(): string
    {
        return view('participant/application', $this->viewData());
    }

    public function edit()
    {
        $data = $this->viewData();
        if ($data['context']['application']['status'] !== 'borrador') {
            return redirect()->route('participant.application');
        }

        return view('participant/draft', $data);
    }

    public function save()
    {
        $applicationId = $this->applicationId();
        $workflow = new ApplicationWorkflowService();
        $participants = $this->request->getPost('participants');
        $form = $this->request->getPost('form');
        $payload = [
            'email'        => (string) $this->request->getPost('email'),
            'participants' => is_array($participants) ? array_values($participants) : [],
            'form'         => is_array($form) ? $form : [],
            'remove_video' => $this->request->getPost('remove_video'),
        ];
        $documentFiles = [];
        $context = $workflow->get($applicationId);
        foreach ($context['definition']['documents'] ?? [] as $definition) {
            $type = (string) ($definition['type'] ?? '');
            if ($type === '') {
                continue;
            }
            $file = $this->request->getFile("documents.{$type}");
            if ($file instanceof UploadedFile) {
                $documentFiles[$type] = $file;
            }
        }
        $removeDocuments = $this->request->getPost('remove_documents');
        $removeDocuments = is_array($removeDocuments) ? array_values($removeDocuments) : [];

        try {
            $workflow->saveDraft(
                $applicationId,
                $payload,
                $this->request->getFile('video_file'),
                $documentFiles,
                $removeDocuments,
            );
        } catch (ApplicationValidationException $exception) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'ok' => false,
                    'message' => 'Revisa los archivos y campos señalados. El borrador no se modificó.',
                    'errors' => $exception->errors(),
                ]);
            }
            return redirect()->back()
                ->withInput()
                ->with('validation_errors', $exception->errors());
        } catch (DomainException $exception) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON([
                    'ok' => false,
                    'message' => $exception->getMessage(),
                    'errors' => [],
                ]);
            }
            return redirect()->route('participant.application')->with('error', $exception->getMessage());
        }

        $nextRoute = (string) $this->request->getPost('next') === 'summary'
            ? 'participant.draft.summary'
            : 'participant.draft';
        $message = 'El borrador se guardó correctamente.';

        if ($this->request->isAJAX()) {
            $this->session->setFlashdata('message', $message);

            return $this->response->setJSON([
                'ok' => true,
                'redirect' => url_to($nextRoute),
            ]);
        }

        return redirect()->route($nextRoute)->with('message', $message);
    }

    public function summary()
    {
        $service = new ApplicationWorkflowService();
        try {
            $service->assertReadyForSubmission($this->applicationId());
        } catch (ApplicationValidationException $exception) {
            return redirect()->route('participant.draft')
                ->with('validation_errors', $exception->errors())
                ->with('error', 'Completa los campos señalados antes de revisar el resumen.');
        } catch (DomainException $exception) {
            return redirect()->route('participant.application')->with('error', $exception->getMessage());
        }

        return view('participant/summary', $this->viewData($service));
    }

    public function submit()
    {
        try {
            (new ApplicationWorkflowService())->submit(
                $this->applicationId(),
                [
                    'confirm_submit'       => $this->request->getPost('confirm_submit'),
                    'accept_declarations' => $this->request->getPost('accept_declarations'),
                ],
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString(),
            );
        } catch (ApplicationValidationException $exception) {
            return redirect()->route('participant.draft.summary')
                ->withInput()
                ->with('validation_errors', $exception->errors());
        } catch (DomainException $exception) {
            return redirect()->route('participant.application')->with('error', $exception->getMessage());
        }

        $this->audit('application_submitted');
        return redirect()->route('participant.application')
            ->with('message', 'La solicitud fue enviada definitivamente y ahora se encuentra bloqueada.');
    }

    public function correctDocument(string $documentType)
    {
        $service = new ApplicationWorkflowService();
        $context = $service->get($this->applicationId());
        if ($context['application']['status'] !== 'incompleta') {
            return redirect()->route('participant.application')
                ->with('error', 'La solicitud no tiene una corrección pendiente.');
        }
        $definition = null;
        foreach ($context['definition']['documents'] ?? [] as $candidate) {
            if (($candidate['type'] ?? null) === $documentType) {
                $definition = $candidate;
                break;
            }
        }
        $document = null;
        foreach ($context['documents'] as $candidate) {
            if (($candidate['type'] ?? null) === $documentType) {
                $document = $candidate['current'];
                break;
            }
        }
        if ($definition === null || $document === null || (int) $document['correction_unlocked'] !== 1) {
            return redirect()->route('participant.application')
                ->with('error', 'Este documento no está habilitado para corrección.');
        }
        if ((string) $this->request->getPost('confirm_correction') !== '1') {
            return redirect()->route('participant.application')
                ->with('error', 'Confirma el reenvío de la corrección.');
        }

        try {
            $completed = (new ApplicationDocumentService())->replaceUnlocked(
                $this->applicationId(),
                $documentType,
                $definition,
                $this->request->getFile('correction_file'),
            );
        } catch (DomainException $exception) {
            return redirect()->route('participant.application')->with('error', $exception->getMessage());
        }

        $this->audit('document_correction_submitted', ['document_type' => $documentType]);
        return redirect()->route('participant.application')
            ->with('message', $completed
                ? 'La corrección fue recibida. Todos los documentos solicitados fueron reemplazados y la solicitud volvió al estado enviada.'
                : 'La corrección fue recibida. Aún quedan documentos solicitados por reemplazar.');
    }

    public function cancel()
    {
        $context = (new ApplicationWorkflowService())->get($this->applicationId());
        $confirmed = (string) $this->request->getPost('confirm_cancel') === '1'
            && strtoupper(trim((string) $this->request->getPost('folio_confirmation')))
                === strtoupper((string) $context['application']['folio']);
        try {
            (new ApplicationLifecycleService())->cancelByParticipant(
                $this->applicationId(),
                $confirmed,
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString(),
            );
        } catch (DomainException $exception) {
            return redirect()->route('participant.application')->with('error', $exception->getMessage());
        }

        $this->audit('application_cancelled');
        return redirect()->route('participant.application')
            ->with('message', 'La solicitud fue cancelada de forma irreversible.');
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(?ApplicationWorkflowService $service = null): array
    {
        $context = ($service ?? new ApplicationWorkflowService())->get($this->applicationId());

        return [
            'title'   => $context['application']['folio'] . ' · Solicitud',
            'context' => $context,
            'errors'  => session('validation_errors') ?? [],
            'municipalities' => (new MunicipalityCatalog())->all(),
        ];
    }

    private function applicationId(): int
    {
        $applicationId = (int) $this->session->get('participant_application_id');
        if ($applicationId <= 0) {
            throw new DomainException('La sesión no está asociada a una solicitud.');
        }

        return $applicationId;
    }

    private function audit(string $action, array $metadata = []): void
    {
        (new AuditLogService())->record(
            'participant',
            $action,
            $this->applicationId(),
            null,
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString(),
            $metadata,
        );
    }
}
