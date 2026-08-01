<?php

use App\Database\Seeds\CategorySeeder;
use App\Exceptions\ApplicationValidationException;
use App\Services\ApplicationDocumentService;
use App\Services\ApplicationWorkflowService;
use App\Services\DraftApplicationService;
use App\Services\PrivateVideoStorage;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class ApplicationWorkflowServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $seed = CategorySeeder::class;

    public function testSavesAndReloadsAnEditableDraft(): void
    {
        $draft = $this->createDraft(
            'cocineras-cocineros-tradicionales',
            'inicio@example.test',
            [$this->participant('GODE561231HDFBCD09')],
        );
        $service = new ApplicationWorkflowService($this->db);
        $form = $this->validForm('cocineras-cocineros-tradicionales');
        $form['signature_dish'] = 'Mole de prueba';
        $form['years_experience'] = '18';

        $service->saveDraft($draft['id'], [
            'email' => 'actualizado@example.test',
            'participants' => [
                $this->participant('GODE561231HDFBCD09', 'María actualizada'),
            ],
            'form' => $form,
        ]);

        $context = $service->get($draft['id']);
        $this->assertSame('actualizado@example.test', $context['application']['email']);
        $this->assertSame(2, $context['application']['version']);
        $this->assertSame('María actualizada', $context['participants'][0]['first_name']);
        $this->assertSame('Mole de prueba', $context['form']['signature_dish']);
        $this->seeInDatabase('cook_profiles', [
            'application_id' => $draft['id'],
            'years_experience' => 18,
            'signature_dish' => 'Mole de prueba',
        ]);
        $this->seeInDatabase('application_histories', [
            'application_id' => $draft['id'],
            'action' => 'draft_saved',
        ]);
        $this->seeInDatabase('application_videos', [
            'application_id' => $draft['id'],
            'source_type' => 'url',
            'external_url' => 'https://example.test/video',
        ]);
    }

    public function testPartialDraftCanBeSavedButCannotReachSummary(): void
    {
        $draft = $this->createDraft(
            'restaurantes',
            'restaurante@example.test',
            [$this->participant('MARA850101MMCBCR08')],
        );
        $service = new ApplicationWorkflowService($this->db);

        $service->saveDraft($draft['id'], [
            'email' => 'restaurante@example.test',
            'participants' => [$this->participant('MARA850101MMCBCR08')],
            'form' => ['business_name' => 'Restaurante provisional'],
        ]);

        $this->expectException(ApplicationValidationException::class);
        $service->assertReadyForSubmission($draft['id']);
    }

    public function testEveryCategoryCanSaveACompleteValidForm(): void
    {
        $fixtures = [
            ['cocineras-cocineros-tradicionales', 'cct@example.test', [$this->participant('GODE561231HDFBCD09')]],
            ['restaurantes', 'res@example.test', [$this->participant('MARA850101MMCBCR08')]],
            [
                'joven-talento-gastronomia',
                'jtg@example.test',
                [$this->participant('LOPE900202HMCDFR07', 'Carlos')],
            ],
            ['bebidas-tradicionales-ancestrales', 'bta@example.test', [$this->participant('BETA910404MMCNRL05')]],
        ];
        $service = new ApplicationWorkflowService($this->db);

        foreach ($fixtures as [$category, $email, $participants]) {
            $draft = $this->createDraft($category, $email, $participants);
            $service->saveDraft($draft['id'], [
                'email' => $email,
                'participants' => $participants,
                'form' => $this->validForm($category),
            ], null, $this->requiredDocumentFiles($category));
            $service->assertReadyForSubmission($draft['id']);
            $this->assertSame('borrador', $service->get($draft['id'])['application']['status']);
        }
    }

    public function testRestaurantRequiresInstitutionalVideoBeforeSubmission(): void
    {
        $category = 'restaurantes';
        $email = 'restaurante-video@example.test';
        $participants = [$this->participant('MARA850101MMCBCR08')];
        $draft = $this->createDraft($category, $email, $participants);
        $form = $this->validForm($category);
        $form['video_url'] = '';
        $service = new ApplicationWorkflowService($this->db);

        $service->saveDraft($draft['id'], [
            'email' => $email,
            'participants' => $participants,
            'form' => $form,
        ], null, $this->requiredDocumentFiles($category));

        try {
            $service->assertReadyForSubmission($draft['id']);
            $this->fail('La solicitud debía requerir un video institucional.');
        } catch (ApplicationValidationException $exception) {
            $this->assertArrayHasKey('form.video_file', $exception->errors());
        }
    }

    public function testRestaurantFormUsesTextNationalityAndTextareaProfile(): void
    {
        $definition = config('ApplicationForms')->categories['restaurantes'];
        $fields = array_column($definition['fields'], null, 'name');
        $documentTypes = array_column($definition['documents'], 'type');

        $this->assertSame('text', $fields['chef_nationality']['type']);
        $this->assertSame('textarea', $fields['restaurant_profile']['type']);
        $this->assertNotContains('restaurant_profile', $documentTypes);
    }

    public function testStudentVideoDurationCannotExceedThreeMinutes(): void
    {
        $category = 'joven-talento-gastronomia';
        $email = 'estudiante-video@example.test';
        $participants = [$this->participant('LOPE900202HMCDFR07', 'Carlos')];
        $draft = $this->createDraft($category, $email, $participants);
        $form = $this->validForm($category);
        $form['video_duration_seconds'] = '181';

        try {
            (new ApplicationWorkflowService($this->db))->saveDraft($draft['id'], [
                'email' => $email,
                'participants' => $participants,
                'form' => $form,
            ]);
            $this->fail('La solicitud debía rechazar videos mayores de tres minutos.');
        } catch (ApplicationValidationException $exception) {
            $this->assertArrayHasKey('form.video_duration_seconds', $exception->errors());
        }
    }

    public function testSubmissionIsTransactionalAndLocksFurtherChanges(): void
    {
        $draft = $this->createDraft(
            'bebidas-tradicionales-ancestrales',
            'bebida@example.test',
            [$this->participant('BETA910404MMCNRL05')],
            true,
        );
        $service = new ApplicationWorkflowService($this->db);
        $payload = [
            'email' => 'bebida@example.test',
            'participants' => [$this->participant('BETA910404MMCNRL05')],
            'form' => $this->validForm('bebidas-tradicionales-ancestrales'),
        ];
        $service->saveDraft(
            $draft['id'],
            $payload,
            null,
            $this->requiredDocumentFiles('bebidas-tradicionales-ancestrales'),
        );
        $service->submit($draft['id'], [
            'confirm_submit' => '1',
            'accept_declarations' => '1',
        ], '127.0.0.1', 'PHPUnit');

        $context = $service->get($draft['id']);
        $this->assertSame('enviada', $context['application']['status']);
        $this->assertNotNull($context['application']['submitted_at']);
        $this->seeInDatabase('legal_acceptances', [
            'application_id' => $draft['id'],
            'document_type' => 'privacy_notice',
        ]);
        $this->seeInDatabase('legal_acceptances', [
            'application_id' => $draft['id'],
            'document_type' => 'submission_declarations',
        ]);
        $this->seeInDatabase('legal_acceptances', [
            'application_id' => $draft['id'],
            'document_type' => 'terms',
        ]);
        $this->seeInDatabase('application_histories', [
            'application_id' => $draft['id'],
            'action' => 'application_submitted',
            'from_status' => 'borrador',
            'to_status' => 'enviada',
        ]);
        $this->seeInDatabase('email_queue', [
            'application_id' => $draft['id'],
            'event' => 'application_submitted',
            'status' => 'pending',
        ]);
        $this->dontSeeInDatabase('documents', [
            'application_id' => $draft['id'],
            'is_locked' => 0,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('no admite modificaciones');
        $service->saveDraft($draft['id'], $payload);
    }

    public function testVideoLinkMustUseHttps(): void
    {
        $draft = $this->createDraft(
            'cocineras-cocineros-tradicionales',
            'video@example.test',
            [$this->participant('GODE561231HDFBCD09')],
        );
        $form = $this->validForm('cocineras-cocineros-tradicionales');
        $form['video_url'] = 'http://example.test/video';

        $this->expectException(ApplicationValidationException::class);
        (new ApplicationWorkflowService($this->db))->saveDraft($draft['id'], [
            'email' => 'video@example.test',
            'participants' => [$this->participant('GODE561231HDFBCD09')],
            'form' => $form,
        ]);
    }

    public function testMp4FileIsStoredPrivatelyAndCanBeReplacedByAUrl(): void
    {
        $draft = $this->createDraft(
            'cocineras-cocineros-tradicionales',
            'archivo-video@example.test',
            [$this->participant('GODE561231HDFBCD09')],
        );
        $temporary = tempnam(sys_get_temp_dir(), 'tg-video-');
        file_put_contents($temporary, base64_decode('AAAAHGZ0eXBtcDQyAAAAAG1wNDJpc29tAAAAKGZyZWU=', true));
        $upload = new TestUploadedVideo(
            $temporary,
            'evidencia.mp4',
            'video/mp4',
            filesize($temporary),
            UPLOAD_ERR_OK,
        );
        $form = $this->validForm('cocineras-cocineros-tradicionales');
        $form['video_url'] = '';
        $service = new ApplicationWorkflowService($this->db);
        $service->saveDraft($draft['id'], [
            'email' => 'archivo-video@example.test',
            'participants' => [$this->participant('GODE561231HDFBCD09')],
            'form' => $form,
        ], $upload);

        $video = $service->get($draft['id'])['video'];
        $this->assertSame('file', $video['source_type']);
        $this->assertSame('evidencia.mp4', $video['original_name']);
        $this->assertFileExists((new PrivateVideoStorage())->absolutePath($video['private_path']));

        $form['video_url'] = 'https://example.test/reemplazo';
        $service->saveDraft($draft['id'], [
            'email' => 'archivo-video@example.test',
            'participants' => [$this->participant('GODE561231HDFBCD09')],
            'form' => $form,
        ]);

        $replacement = $service->get($draft['id'])['video'];
        $this->assertSame('url', $replacement['source_type']);
        $this->assertSame('https://example.test/reemplazo', $replacement['external_url']);
    }

    public function testMunicipalityMustBelongToTheOfficialCatalog(): void
    {
        $draft = $this->createDraft(
            'restaurantes',
            'municipio@example.test',
            [$this->participant('MARA850101MMCBCR08')],
        );
        $form = $this->validForm('restaurantes');
        $form['municipality'] = 'Municipio inventado';

        $this->expectException(ApplicationValidationException::class);
        (new ApplicationWorkflowService($this->db))->saveDraft($draft['id'], [
            'email' => 'municipio@example.test',
            'participants' => [$this->participant('MARA850101MMCBCR08')],
            'form' => $form,
        ]);
    }

    public function testReplacingAndRemovingADocumentPreservesItsVersions(): void
    {
        $category = 'cocineras-cocineros-tradicionales';
        $draft = $this->createDraft(
            $category,
            'versiones@example.test',
            [$this->participant('GODE561231HDFBCD09')],
        );
        $service = new ApplicationWorkflowService($this->db);
        $payload = [
            'email' => 'versiones@example.test',
            'participants' => [$this->participant('GODE561231HDFBCD09')],
            'form' => $this->validForm($category),
        ];

        $service->saveDraft($draft['id'], $payload, null, [
            'official_id' => $this->testPdf('identificacion.pdf'),
        ]);
        $service->saveDraft($draft['id'], $payload, null, [
            'official_id' => $this->testPdf('identificacion-nueva.pdf'),
        ]);

        $document = $this->db->table('documents')
            ->where('application_id', $draft['id'])
            ->where('document_type', 'official_id')
            ->get()->getRowArray();
        $this->assertSame(2, (int) $document['active_version_number']);
        $this->assertSame(2, $this->db->table('document_versions')
            ->where('document_id', $document['id'])->countAllResults());

        $service->saveDraft($draft['id'], $payload, null, [], ['official_id']);
        $document = $this->db->table('documents')->where('id', $document['id'])->get()->getRowArray();
        $this->assertNull($document['active_version_number']);
        $this->assertSame(2, $this->db->table('document_versions')
            ->where('document_id', $document['id'])->countAllResults());
    }

    public function testSeveralUnlockedCorrectionsKeepIncompleteUntilTheLastReplacement(): void
    {
        $category = 'cocineras-cocineros-tradicionales';
        $draft = $this->createDraft(
            $category,
            'correccion@example.test',
            [$this->participant('GODE561231HDFBCD09')],
        );
        $workflow = new ApplicationWorkflowService($this->db);
        $payload = [
            'email' => 'correccion@example.test',
            'participants' => [$this->participant('GODE561231HDFBCD09')],
            'form' => $this->validForm($category),
        ];
        $workflow->saveDraft($draft['id'], $payload, null, [
            'official_id' => $this->testPdf('identificacion.pdf'),
            'proof_of_address' => $this->testPdf('domicilio.pdf'),
        ]);
        $documents = $this->db->table('documents')
            ->where('application_id', $draft['id'])
            ->whereIn('document_type', ['official_id', 'proof_of_address'])
            ->get()->getResultArray();
        $byType = [];
        foreach ($documents as $document) {
            $byType[$document['document_type']] = $document;
        }
        $this->db->table('applications')->where('id', $draft['id'])->update(['status' => 'incompleta']);
        $this->db->table('documents')->whereIn('id', array_column($documents, 'id'))->update([
            'is_locked' => 1,
            'correction_unlocked' => 1,
        ]);
        $definitions = config('ApplicationForms')->categories[$category]['documents'];

        $service = new ApplicationDocumentService($this->db);
        $completed = $service->replaceUnlocked(
            $draft['id'],
            'official_id',
            $definitions[0],
            $this->testPdf('identificacion-corregida.pdf'),
        );

        $this->assertFalse($completed);
        $this->seeInDatabase('applications', ['id' => $draft['id'], 'status' => 'incompleta']);
        $this->seeInDatabase('documents', [
            'id' => $byType['proof_of_address']['id'],
            'correction_unlocked' => 1,
        ]);

        $completed = $service->replaceUnlocked(
            $draft['id'],
            'proof_of_address',
            $definitions[1],
            $this->testPdf('domicilio-corregido.pdf'),
        );

        $this->assertTrue($completed);
        $this->seeInDatabase('applications', ['id' => $draft['id'], 'status' => 'enviada']);
        $this->seeInDatabase('documents', [
            'id' => $byType['official_id']['id'],
            'active_version_number' => 2,
            'is_locked' => 1,
            'correction_unlocked' => 0,
        ]);
        $this->seeInDatabase('documents', [
            'id' => $byType['proof_of_address']['id'],
            'active_version_number' => 2,
            'is_locked' => 1,
            'correction_unlocked' => 0,
        ]);
        $this->assertSame(4, $this->db->table('document_versions')->countAllResults());
        $this->seeInDatabase('application_histories', [
            'application_id' => $draft['id'],
            'action' => 'document_correction_submitted',
            'from_status' => 'incompleta',
            'to_status' => 'enviada',
        ]);
        $this->assertSame(2, $this->db->table('email_queue')
            ->where('application_id', $draft['id'])
            ->where('event', 'correction_received')->countAllResults());
    }

    public function testDocumentWithDoubleExtensionIsRejected(): void
    {
        $category = 'cocineras-cocineros-tradicionales';
        $draft = $this->createDraft(
            $category,
            'doble-extension@example.test',
            [$this->participant('GODE561231HDFBCD09')],
        );

        $this->expectException(ApplicationValidationException::class);
        (new ApplicationWorkflowService($this->db))->saveDraft($draft['id'], [
            'email' => 'doble-extension@example.test',
            'participants' => [$this->participant('GODE561231HDFBCD09')],
            'form' => $this->validForm($category),
        ], null, [
            'official_id' => $this->testPdf('identificacion.php.pdf'),
        ]);
    }

    public function testEditingToARegisteredCurpReturnsTheSpecificMessage(): void
    {
        $first = $this->createDraft(
            'cocineras-cocineros-tradicionales',
            'primera-edicion@example.test',
            [$this->participant('GODE561231HDFBCD09')],
        );
        $this->createDraft(
            'restaurantes',
            'segunda-edicion@example.test',
            [$this->participant('MARA850101MMCBCR08')],
        );

        try {
            (new ApplicationWorkflowService($this->db))->saveDraft($first['id'], [
                'email' => 'primera-edicion@example.test',
                'participants' => [$this->participant(' mara850101mmcbcr08 ')],
                'form' => $this->validForm('cocineras-cocineros-tradicionales'),
            ]);
            $this->fail('La CURP registrada debió ser rechazada.');
        } catch (ApplicationValidationException $exception) {
            $this->assertSame(
                'La CURP ya tiene un registro en el sistema.',
                $exception->errors()['participants.0.curp'] ?? null,
            );
        }
    }

    /**
     * @param list<array<string, string>> $participants
     *
     * @return array{id: int, folio: string, status: string}
     */
    private function createDraft(
        string $category,
        string $email,
        array $participants,
        bool $withAcceptance = false,
    ): array {
        return (new DraftApplicationService($this->db))->create(
            $category,
            $email,
            $participants,
            $withAcceptance ? [
                'document_version' => 'PROVISIONAL-TEST',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
            ] : null,
        );
    }

    /**
     * @return array<string, string>
     */
    private function validForm(string $category): array
    {
        $form = [];
        foreach (config('ApplicationForms')->categories[$category]['fields'] as $field) {
            $form[$field['name']] = match (true) {
                $field['name'] === 'municipality' => 'Toluca',
                $field['type'] === 'number' => (string) ($field['min'] ?? 5),
                $field['type'] === 'email' => 'campo@example.test',
                $field['type'] === 'select' => (string) ($field['options'][0] ?? ''),
                in_array($field['type'], ['url', 'video'], true) => 'https://example.test/video',
                default => 'Dato válido',
            };
        }

        return $form;
    }

    /** @return array<string, UploadedFile> */
    private function requiredDocumentFiles(string $category): array
    {
        $files = [];
        foreach (config('ApplicationForms')->categories[$category]['documents'] ?? [] as $definition) {
            if (! empty($definition['required'])) {
                $files[$definition['type']] = ($definition['accept'] ?? '') === 'image'
                    ? $this->testJpeg($definition['type'] . '.jpg')
                    : $this->testPdf($definition['type'] . '.pdf');
            }
        }

        return $files;
    }

    private function testPdf(string $name): UploadedFile
    {
        $temporary = tempnam(sys_get_temp_dir(), 'tg-document-');
        file_put_contents($temporary, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF");

        return new TestUploadedVideo(
            $temporary,
            $name,
            'application/pdf',
            filesize($temporary),
            UPLOAD_ERR_OK,
        );
    }

    private function testJpeg(string $name): UploadedFile
    {
        $temporary = tempnam(sys_get_temp_dir(), 'tg-document-');
        file_put_contents($temporary, base64_decode(
            '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAEf/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABBQJ//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAwEBPwF//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAgEBPwF//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQAGPwJ//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPyF//9oADAMBAAIAAwAAABD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/EH//xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/EH//xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/EH//2Q==',
            true,
        ));

        return new TestUploadedVideo(
            $temporary,
            $name,
            'image/jpeg',
            filesize($temporary),
            UPLOAD_ERR_OK,
        );
    }

    /**
     * @return array{curp: string, first_name: string, last_name: string, second_last_name: string}
     */
    private function participant(string $curp, string $firstName = 'María'): array
    {
        return [
            'curp' => $curp,
            'first_name' => $firstName,
            'last_name' => 'González',
            'second_last_name' => 'Ejemplo',
        ];
    }
}

final class TestUploadedVideo extends UploadedFile
{
    public function isValid(): bool
    {
        return $this->getError() === UPLOAD_ERR_OK && is_file($this->getTempName());
    }

    public function move(string $targetPath, ?string $name = null, bool $overwrite = false): bool
    {
        if (! is_dir($targetPath)) {
            mkdir($targetPath, 0750, true);
        }
        $name ??= $this->getName();
        $destination = rtrim($targetPath, '/\\') . DIRECTORY_SEPARATOR . $name;
        if (! rename($this->getTempName(), $destination)) {
            return false;
        }
        $this->path = rtrim($targetPath, '/\\') . DIRECTORY_SEPARATOR;
        $this->name = $name;
        $this->hasMoved = true;

        return true;
    }
}
