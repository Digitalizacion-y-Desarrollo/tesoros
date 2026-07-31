<?php

namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Domain\ApplicationStatus;
use App\Services\AdminApplicationService;
use App\Services\ApplicationLifecycleService;
use App\Services\AuditLogService;
use App\Services\CsvExportService;
use App\Services\MunicipalityCatalog;
use DomainException;

final class ApplicationController extends AdminController
{
    public function index(): string
    {
        $service = new AdminApplicationService();
        return view('admin/applications/index', [
            'title' => 'Solicitudes',
            'listing' => $service->listing($this->request->getGet(), (int) ($this->request->getGet('page') ?: 1)),
            'categories' => config('ApplicationForms')->categories,
            'statuses' => ApplicationStatus::values(),
            'municipalities' => (new MunicipalityCatalog())->all(),
        ]);
    }

    public function show(int $applicationId): string
    {
        $detail = (new AdminApplicationService())->detail($applicationId);
        return view('admin/applications/show', [
            'title' => 'Detalle de solicitud',
            'detail' => $detail,
            'allowedTransitions' => ApplicationStatus::allowedAdminTransitions(
                (string) $detail['application']['status'],
            ),
        ]);
    }

    public function exportCsv()
    {
        $export = (new AdminApplicationService())->exportRows($this->request->getGet());
        $csv = (new CsvExportService())->generate($export['rows']);
        $filters = $export['filters'];
        $this->auditExport(count($export['rows']), [
            'category' => $filters['category'],
            'status' => $filters['status'],
            'municipality' => $filters['municipality'],
            'search_applied' => $filters['q'] !== '',
        ]);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="solicitudes-' . date('Ymd-His') . '.csv"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody($csv);
    }

    public function updatePersonalData(int $applicationId)
    {
        $participants = $this->request->getPost('participants');
        try {
            (new AdminApplicationService())->updatePersonalData(
                $applicationId,
                (string) $this->request->getPost('email'),
                is_array($participants) ? array_values($participants) : [],
                $this->actor(),
            );
        } catch (DomainException $exception) {
            return redirect()->route('admin.applications.show', [$applicationId])
                ->withInput()->with('error', $exception->getMessage());
        }

        $this->audit('admin_personal_data_updated', $applicationId);
        return redirect()->route('admin.applications.show', [$applicationId])
            ->with('message', 'Los datos personales fueron actualizados.');
    }

    public function addComment(int $applicationId)
    {
        try {
            (new AdminApplicationService())->addComment(
                $applicationId,
                (string) $this->request->getPost('comment'),
                (string) $this->request->getPost('visible_to_participant') === '1',
                $this->actor(),
            );
        } catch (DomainException $exception) {
            return redirect()->route('admin.applications.show', [$applicationId])
                ->with('error', $exception->getMessage());
        }

        $this->audit('admin_comment_added', $applicationId, [
            'visible_to_participant' => (string) $this->request->getPost('visible_to_participant') === '1',
        ]);
        return redirect()->route('admin.applications.show', [$applicationId])
            ->with('message', 'Comentario agregado.');
    }

    public function changeStatus(int $applicationId)
    {
        try {
            (new ApplicationLifecycleService())->changeStatus(
                $applicationId,
                (string) $this->request->getPost('status'),
                $this->request->getPost('comment'),
                $this->actor(),
            );
        } catch (DomainException $exception) {
            return redirect()->route('admin.applications.show', [$applicationId])
                ->with('error', $exception->getMessage());
        }

        $this->audit('admin_status_changed', $applicationId, [
            'target_status' => (string) $this->request->getPost('status'),
        ]);
        return redirect()->route('admin.applications.show', [$applicationId])
            ->with('message', 'Estado actualizado.');
    }

    public function requestCorrection(int $applicationId)
    {
        $documentIds = $this->request->getPost('document_ids');
        $documentIds = is_array($documentIds) ? array_values($documentIds) : [];
        try {
            (new ApplicationLifecycleService())->requestCorrections(
                $applicationId,
                $documentIds,
                (string) $this->request->getPost('comment'),
                $this->actor(),
            );
        } catch (DomainException $exception) {
            return redirect()->route('admin.applications.show', [$applicationId])
                ->with('error', $exception->getMessage());
        }

        $this->audit('document_correction_requested', $applicationId, [
            'document_ids' => array_map('intval', $documentIds),
            'document_count' => count($documentIds),
        ]);
        return redirect()->route('admin.applications.show', [$applicationId])
            ->with('message', 'La solicitud fue marcada como incompleta y los documentos seleccionados quedaron habilitados.');
    }

    private function actor(): ?string
    {
        $actor = trim((string) $this->session->get('admin_actor_reference'));
        return $actor !== '' ? mb_substr($actor, 0, 120) : null;
    }

    private function audit(string $action, int $applicationId, array $metadata = []): void
    {
        (new AuditLogService())->record(
            'admin',
            $action,
            $applicationId,
            $this->actor(),
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString(),
            $metadata,
        );
    }

    private function auditExport(int $count, array $filters): void
    {
        (new AuditLogService())->record(
            'admin',
            'applications_exported',
            null,
            $this->actor(),
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString(),
            ['format' => 'csv', 'row_count' => $count] + $filters,
        );
    }
}
