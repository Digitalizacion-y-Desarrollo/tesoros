<?php

use App\Database\Seeds\CategorySeeder;
use App\Services\ApplicationLifecycleService;
use App\Services\ConvocationSchedule;
use App\Services\DraftApplicationService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Convocation;

/**
 * @internal
 */
final class ApplicationLifecycleServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $seed = CategorySeeder::class;

    public function testAdminTransitionsFollowTheOfficialGraph(): void
    {
        $application = $this->draft('estado@example.test', 'GODE561231HDFBCD09');
        $this->db->table('applications')->where('id', $application['id'])->update(['status' => 'enviada']);
        $service = new ApplicationLifecycleService($this->db);

        $service->changeStatus($application['id'], 'en_revision', 'Inicio de revisión', 'admin-test');
        $service->changeStatus($application['id'], 'seleccionada', null, 'admin-test');

        $this->seeInDatabase('applications', ['id' => $application['id'], 'status' => 'seleccionada']);
        $this->seeInDatabase('application_histories', [
            'application_id' => $application['id'],
            'action' => 'admin_status_changed',
            'from_status' => 'en_revision',
            'to_status' => 'seleccionada',
        ]);
        $this->seeInDatabase('email_queue', [
            'application_id' => $application['id'],
            'event' => 'application_selected',
            'status' => 'pending',
        ]);

        $this->expectException(DomainException::class);
        $service->changeStatus($application['id'], 'en_revision', null, 'admin-test');
    }

    public function testCorrectionRequestUnlocksSeveralCurrentDocumentsTogether(): void
    {
        $application = $this->draft('incompleta@example.test', 'GODE561231HDFBCD09');
        $this->db->table('applications')->where('id', $application['id'])->update(['status' => 'en_revision']);
        $now = date('Y-m-d H:i:s');
        foreach (['official_id', 'proof_of_address'] as $type) {
            $this->db->table('documents')->insert([
                'application_id' => $application['id'],
                'document_type' => $type,
                'label' => $type,
                'is_required' => 1,
                'is_locked' => 1,
                'correction_unlocked' => 0,
                'active_version_number' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        $targets = $this->db->table('documents')->where('application_id', $application['id'])
            ->orderBy('id', 'ASC')->get()->getResultArray();

        (new ApplicationLifecycleService($this->db))->requestCorrections(
            $application['id'],
            array_map(static fn (array $document): int => (int) $document['id'], $targets),
            'Sustituye ambos documentos por copias legibles.',
            'admin-test',
        );

        $this->seeInDatabase('applications', ['id' => $application['id'], 'status' => 'incompleta']);
        foreach ($targets as $target) {
            $this->seeInDatabase('documents', ['id' => $target['id'], 'correction_unlocked' => 1]);
        }
        $this->assertSame(2, $this->db->table('documents')
            ->where('application_id', $application['id'])->where('correction_unlocked', 1)->countAllResults());
        $this->seeInDatabase('admin_comments', [
            'application_id' => $application['id'],
            'document_id' => null,
            'is_visible_to_participant' => 1,
        ]);
        $history = $this->db->table('application_histories')
            ->where('application_id', $application['id'])
            ->where('action', 'document_correction_requested')->get()->getRowArray();
        $metadata = json_decode((string) $history['metadata'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(2, $metadata['document_count']);
        $this->seeInDatabase('email_queue', [
            'application_id' => $application['id'],
            'event' => 'correction_requested',
            'status' => 'pending',
        ]);
    }

    public function testParticipantCancellationIsIrreversibleAndAudited(): void
    {
        $application = $this->draft('cancelacion@example.test', 'GODE561231HDFBCD09');
        $service = new ApplicationLifecycleService($this->db);
        $service->cancelByParticipant($application['id'], true, '127.0.0.1', 'PHPUnit');

        $this->seeInDatabase('applications', ['id' => $application['id'], 'status' => 'cancelada']);
        $this->seeInDatabase('application_histories', [
            'application_id' => $application['id'],
            'action' => 'application_cancelled',
            'to_status' => 'cancelada',
        ]);
        $this->seeInDatabase('email_queue', [
            'application_id' => $application['id'],
            'event' => 'application_cancelled',
            'status' => 'pending',
        ]);

        $this->expectException(DomainException::class);
        $service->cancelByParticipant($application['id'], true, '127.0.0.1', 'PHPUnit');
    }

    public function testConfiguredClosingDateBlocksRegistrationAndDraftEditing(): void
    {
        $config = new Convocation();
        $config->closeAt = '2026-07-29 23:59:59';
        $schedule = new ConvocationSchedule($config);

        $this->assertTrue($schedule->isClosed(new DateTimeImmutable('2026-07-30 00:00:00', new DateTimeZone('America/Mexico_City'))));
        $this->expectException(DomainException::class);
        $schedule->assertRegistrationOpen();
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
