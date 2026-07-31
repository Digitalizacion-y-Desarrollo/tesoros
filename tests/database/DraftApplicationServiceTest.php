<?php

use App\Database\Seeds\CategorySeeder;
use App\Services\DraftApplicationService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class DraftApplicationServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $seed = CategorySeeder::class;

    public function testCreatesDraftWithNormalizedIdentifiersAndProfile(): void
    {
        $result = (new DraftApplicationService($this->db))->create(
            'cocineras-cocineros-tradicionales',
            ' Persona@Example.COM ',
            [$this->participant(' gode561231hdfbcd09 ')],
        );

        $this->assertSame('TG-2026-CCT-000001', $result['folio']);
        $this->assertSame('borrador', $result['status']);
        $this->seeInDatabase('applications', [
            'id'         => $result['id'],
            'email'      => 'persona@example.com',
            'email_hash' => hash('sha256', 'persona@example.com'),
            'status'     => 'borrador',
        ]);
        $this->seeInDatabase('participants', [
            'application_id' => $result['id'],
            'curp'           => 'GODE561231HDFBCD09',
            'is_primary'     => 1,
        ]);
        $this->seeInDatabase('cook_profiles', ['application_id' => $result['id']]);
        $this->seeInDatabase('application_histories', [
            'application_id' => $result['id'],
            'action'         => 'draft_created',
            'to_status'      => 'borrador',
        ]);
    }

    public function testDuplicateEmailRollsBackApplicationAndFolioCounter(): void
    {
        $service = new DraftApplicationService($this->db);
        $service->create(
            'restaurantes',
            'unica@example.com',
            [$this->participant('GODE561231HDFBCD09')],
        );

        try {
            $service->create(
                'restaurantes',
                ' UNICA@example.com ',
                [$this->participant('MARA850101MMCBCR08')],
            );
            $this->fail('El correo duplicado debió ser rechazado.');
        } catch (\DomainException $exception) {
            $this->assertStringContainsString('Ya existe una solicitud', $exception->getMessage());
        }

        $next = $service->create(
            'restaurantes',
            'otra@example.com',
            [$this->participant('LOPE900202HMCDFR07')],
        );

        $this->assertSame('TG-2026-RES-000002', $next['folio']);
        $this->assertSame(2, $this->db->table('applications')->countAllResults());
    }

    public function testCurpIsUniqueAcrossEveryCategory(): void
    {
        $service = new DraftApplicationService($this->db);
        $service->create(
            'cocineras-cocineros-tradicionales',
            'primera@example.com',
            [$this->participant('GODE561231HDFBCD09')],
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('La CURP ya tiene un registro en el sistema.');

        $service->create(
            'bebidas-tradicionales-ancestrales',
            'segunda@example.com',
            [$this->participant(' gode561231hdfbcd09 ')],
        );
    }

    public function testExistingCurpMessageTakesPriorityOverStructuralValidation(): void
    {
        $service = new DraftApplicationService($this->db);
        $draft = $service->create(
            'cocineras-cocineros-tradicionales',
            'existente@example.com',
            [$this->participant('GODE561231HDFBCD09')],
        );
        $legacyCurp = 'CURPINVALIDA000000';
        $this->assertSame(18, strlen($legacyCurp));
        $this->db->table('participants')
            ->where('application_id', $draft['id'])
            ->update(['curp' => $legacyCurp]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('La CURP ya tiene un registro en el sistema.');

        $service->create(
            'bebidas-tradicionales-ancestrales',
            'nueva@example.com',
            [$this->participant(' curp invalida 000000 ')],
        );
    }

    public function testStudentApplicationCreatesOneParticipantUnderOneFolio(): void
    {
        $result = (new DraftApplicationService($this->db))->create(
            'joven-talento-gastronomia',
            'estudiante@example.com',
            [$this->participant('GODE561231HDFBCD09', 'Ana')],
        );

        $members = $this->db->table('participants')
            ->where('application_id', $result['id'])
            ->orderBy('member_number')
            ->get()
            ->getResultArray();

        $this->assertCount(1, $members);
        $this->assertSame('responsable', $members[0]['role']);
        $this->assertSame('TG-2026-JTG-000001', $result['folio']);
        $this->seeInDatabase('student_team_profiles', ['application_id' => $result['id']]);
    }

    public function testStudentApplicationRejectsMoreThanOneParticipant(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('exactamente una persona participante');

        (new DraftApplicationService($this->db))->create(
            'joven-talento-gastronomia',
            'estudiante@example.com',
            [
                $this->participant('GODE561231HDFBCD09'),
                $this->participant('MARA850101MMCBCR08'),
            ],
        );
    }

    public function testCategorySeederDoesNotResetAnExistingFolioSequence(): void
    {
        (new DraftApplicationService($this->db))->create(
            'cocineras-cocineros-tradicionales',
            'secuencia@example.test',
            [$this->participant('GODE561231HDFBCD09')],
        );

        $this->seeder->call(CategorySeeder::class);

        $counter = $this->db->table('folio_counters fc')
            ->select('fc.last_sequence')
            ->join('categories c', 'c.id = fc.category_id')
            ->where('c.code', 'cocineras-cocineros-tradicionales')
            ->get()
            ->getRowArray();
        $this->assertSame(1, (int) $counter['last_sequence']);
    }

    public function testConcurrentRequestsProduceUniqueSequentialFolios(): void
    {
        $workers = [
            ['concurrente1@example.com', 'GODE561231HDFBCD09'],
            ['concurrente2@example.com', 'MARA850101MMCBCR08'],
            ['concurrente3@example.com', 'LOPE900202HMCDFR07'],
            ['concurrente4@example.com', 'CARA880303MMCPLN06'],
        ];
        $processes = [];

        foreach ($workers as [$email, $curp]) {
            $command = implode(' ', [
                escapeshellarg(PHP_BINARY),
                escapeshellarg(HOMEPATH . 'tests/_support/concurrency_worker.php'),
                escapeshellarg('cocineras-cocineros-tradicionales'),
                escapeshellarg($email),
                escapeshellarg($curp),
            ]);
            $pipes = [];
            $process = proc_open(
                $command,
                [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes,
                HOMEPATH,
            );

            $this->assertIsResource($process);
            $processes[] = [$process, $pipes];
        }

        $folios = [];

        foreach ($processes as [$process, $pipes]) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);

            $this->assertSame(0, $exitCode, $error);
            $result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
            $this->assertArrayHasKey('folio', $result, $output);
            $folios[] = $result['folio'];
        }

        sort($folios);
        $this->assertSame([
            'TG-2026-CCT-000001',
            'TG-2026-CCT-000002',
            'TG-2026-CCT-000003',
            'TG-2026-CCT-000004',
        ], $folios);
        $this->assertSame(4, count(array_unique($folios)));
    }

    /**
     * @return array{curp: string, first_name: string, last_name: string, second_last_name: string}
     */
    private function participant(string $curp, string $firstName = 'María'): array
    {
        return [
            'curp'             => $curp,
            'first_name'       => $firstName,
            'last_name'        => 'González',
            'second_last_name' => 'Demo',
        ];
    }
}
