<?php

use App\Controllers\Participant\ApplicationController;
use App\Database\Seeds\CategorySeeder;
use App\Services\DraftApplicationService;
use App\Services\PrivateDocumentStorage;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class ParticipantDocumentUploadControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $seed = CategorySeeder::class;

    public function testAjaxUploadPersistsDocumentAndReturnsParticipantRedirect(): void
    {
        $category = 'cocineras-cocineros-tradicionales';
        $participant = [
            'curp' => 'GODE561231HDFBCD09',
            'first_name' => 'Persona',
            'last_name' => 'Prueba',
            'second_last_name' => '',
        ];
        $draft = (new DraftApplicationService($this->db))->create(
            $category,
            'carga-controlador@example.test',
            [$participant],
        );
        $pdf = $this->testPdf('identificacion.pdf');
        $post = [
            'email' => 'carga-controlador@example.test',
            'participants' => [$participant],
            'form' => $this->validForm($category),
            'remove_video' => null,
            'remove_documents' => [],
            'next' => null,
        ];

        $request = $this->createMock(IncomingRequest::class);
        $request->method('getPost')->willReturnCallback(
            static fn (?string $key = null) => $key === null ? $post : ($post[$key] ?? null),
        );
        $request->method('getFile')->willReturnCallback(
            static fn (string $key) => $key === 'documents.official_id' ? $pdf : null,
        );
        $request->method('isAJAX')->willReturn(true);

        $session = service('session');
        $session->set([
            'participant_authenticated' => true,
            'participant_application_id' => $draft['id'],
            'participant_access_scope' => 'draft_owner',
        ]);

        $controller = new ApplicationController();
        $controller->initController($request, service('response'), service('logger'));
        $response = $controller->save();

        $this->assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($payload['ok']);
        $this->assertStringContainsString('/participante/borrador', $payload['redirect']);
        $this->assertSame(csrf_token(), $payload['csrf']['name']);
        $this->assertNotSame('', $payload['csrf']['hash']);
        $this->seeInDatabase('documents', [
            'application_id' => $draft['id'],
            'document_type' => 'official_id',
            'active_version_number' => 1,
        ]);
        $this->assertSame(1, $this->db->table('document_versions')->countAllResults());
        $version = $this->db->table('document_versions')->get()->getRowArray();
        (new PrivateDocumentStorage())->delete((string) $version['private_path']);
    }

    /** @return array<string, string> */
    private function validForm(string $category): array
    {
        $values = [];
        foreach (config('ApplicationForms')->categories[$category]['fields'] as $field) {
            $values[$field['name']] = match ($field['name']) {
                'municipality' => 'Toluca',
                'years_experience' => '5',
                'video_url' => '',
                default => str_contains($field['name'], 'phone') ? '7221234567' : 'Dato de prueba suficiente',
            };
        }

        return $values;
    }

    private function testPdf(string $name): ControllerTestUploadedFile
    {
        $temporary = tempnam(sys_get_temp_dir(), 'tg-controller-document-');
        file_put_contents($temporary, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF");

        return new ControllerTestUploadedFile(
            $temporary,
            $name,
            'application/pdf',
            filesize($temporary),
            UPLOAD_ERR_OK,
        );
    }
}

final class ControllerTestUploadedFile extends UploadedFile
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
