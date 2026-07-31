<?php

use App\Database\Seeds\CategorySeeder;
use App\Services\AdminApplicationService;
use App\Services\DraftApplicationService;
use App\Services\MunicipalityCatalog;
use App\Domain\ApplicationStatus;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class AdminApplicationServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $seed = CategorySeeder::class;

    public function testDashboardListingAndDetailExposeManageableData(): void
    {
        $application = $this->draft('panel@example.test', 'GODE561231HDFBCD09');
        $service = new AdminApplicationService($this->db);

        $dashboard = $service->dashboard();
        $listing = $service->listing(['q' => $application['folio']]);
        $detail = $service->detail($application['id']);

        $this->assertSame(1, $dashboard['total']);
        $this->assertCount(1, $listing['rows']);
        $this->assertStringContainsString('***@', $listing['rows'][0]['email_masked']);
        $this->assertArrayNotHasKey('email', $listing['rows'][0]);
        $this->assertSame($application['folio'], $detail['application']['folio']);
        $this->assertCount(1, $detail['participants']);
    }

    public function testAdministratorCanEditPersonalDataWithoutChangingFolioOrCategory(): void
    {
        $application = $this->draft('antes@example.test', 'GODE561231HDFBCD09');
        $before = $this->db->table('applications')->where('id', $application['id'])->get()->getRowArray();

        (new AdminApplicationService($this->db))->updatePersonalData(
            $application['id'],
            'despues@example.test',
            [[
                'curp' => 'MARA850101MMCBCR08',
                'first_name' => 'Nombre actualizado',
                'last_name' => 'Apellido',
                'second_last_name' => '',
            ]],
            'admin-test',
        );

        $after = $this->db->table('applications')->where('id', $application['id'])->get()->getRowArray();
        $this->assertSame($before['folio'], $after['folio']);
        $this->assertSame($before['category_id'], $after['category_id']);
        $this->assertSame('despues@example.test', $after['email']);
        $this->seeInDatabase('participants', [
            'application_id' => $application['id'],
            'curp' => 'MARA850101MMCBCR08',
            'first_name' => 'Nombre actualizado',
        ]);
        $this->seeInDatabase('application_histories', [
            'application_id' => $application['id'],
            'action' => 'admin_personal_data_updated',
        ]);
    }

    public function testCommentsAndAuditAreRecorded(): void
    {
        $application = $this->draft('comentario@example.test', 'GODE561231HDFBCD09');
        $service = new AdminApplicationService($this->db);
        $service->addComment($application['id'], 'Comentario visible de prueba.', true, 'admin-test');
        $audit = $service->audit();

        $this->seeInDatabase('admin_comments', [
            'application_id' => $application['id'],
            'is_visible_to_participant' => 1,
        ]);
        $this->assertGreaterThanOrEqual(2, $audit['total']);
        $this->assertSame($application['folio'], $audit['rows'][0]['folio']);
    }

    public function testAdministrativeViewsRenderWithRealServiceData(): void
    {
        helper('admin_ui');
        $application = $this->draft('vistas@example.test', 'GODE561231HDFBCD09');
        $service = new AdminApplicationService($this->db);
        $detail = $service->detail($application['id']);

        $dashboardHtml = view('admin/dashboard', [
            'title' => 'Panel',
            'dashboard' => $service->dashboard(),
        ]);
        $listingHtml = view('admin/applications/index', [
            'title' => 'Solicitudes',
            'listing' => $service->listing([]),
            'categories' => config('ApplicationForms')->categories,
            'statuses' => ApplicationStatus::values(),
            'municipalities' => (new MunicipalityCatalog())->all(),
        ]);
        $detailHtml = view('admin/applications/show', [
            'title' => 'Detalle',
            'detail' => $detail,
            'allowedTransitions' => ApplicationStatus::allowedAdminTransitions($detail['application']['status']),
        ]);

        $this->assertStringContainsString('Panel administrativo', $dashboardHtml);
        $this->assertStringContainsString($application['folio'], $listingHtml);
        $this->assertStringContainsString('Datos personales', $detailHtml);
        $this->assertStringContainsString('Documentos y versiones', $detailHtml);
        $this->assertStringContainsString('document-preview-modal', $detailHtml);
        $this->assertStringContainsString('admin-document-preview.js', $detailHtml);
    }

    public function testExportRowsRespectCombinedFiltersAndContainNoPhysicalPaths(): void
    {
        $application = $this->draft('exportacion@example.test', 'GODE561231HDFBCD09');
        $this->db->table('applications')->where('id', $application['id'])->update(['status' => 'enviada']);
        $this->db->table('cook_profiles')->where('application_id', $application['id'])
            ->update(['municipality' => 'Nezahualcóyotl']);

        $export = (new AdminApplicationService($this->db))->exportRows([
            'category' => 'cocineras-cocineros-tradicionales',
            'status' => 'enviada',
            'municipality' => 'Nezahualcóyotl',
        ]);

        $this->assertCount(1, $export['rows']);
        $this->assertSame($application['folio'], $export['rows'][0]['folio']);
        $this->assertSame('exportacion@example.test', $export['rows'][0]['email']);
        $this->assertArrayNotHasKey('private_path', $export['rows'][0]);
        $this->assertArrayNotHasKey('sha256', $export['rows'][0]);
    }

    /** @return array{id:int,folio:string,status:string} */
    private function draft(string $email, string $curp): array
    {
        return (new DraftApplicationService($this->db))->create(
            'cocineras-cocineros-tradicionales',
            $email,
            [[
                'curp' => $curp,
                'first_name' => 'Persona',
                'last_name' => 'Prueba',
                'second_last_name' => '',
            ]],
        );
    }
}
