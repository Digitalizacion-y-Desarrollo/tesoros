<?php

namespace App\Services;

use App\Exceptions\ApplicationValidationException;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\ApplicationForms;
use Config\Database;
use DomainException;
use Throwable;

final class ApplicationWorkflowService
{
    private BaseConnection $db;
    private ApplicationForms $forms;
    private CurpNormalizer $curpNormalizer;
    private EmailNormalizer $emailNormalizer;
    private MunicipalityCatalog $municipalities;
    private PrivateVideoStorage $videoStorage;
    private ApplicationDocumentService $documentService;

    public function __construct(
        ?BaseConnection $db = null,
        ?ApplicationForms $forms = null,
        ?PrivateVideoStorage $videoStorage = null,
        ?ApplicationDocumentService $documentService = null,
    ) {
        $this->db = $db ?? Database::connect();
        $this->forms = $forms ?? config('ApplicationForms');
        $this->curpNormalizer = new CurpNormalizer();
        $this->emailNormalizer = new EmailNormalizer();
        $this->municipalities = new MunicipalityCatalog();
        $this->videoStorage = $videoStorage ?? new PrivateVideoStorage();
        $this->documentService = $documentService ?? new ApplicationDocumentService($this->db);
    }

    /**
     * @return array{
     *     application: array<string, mixed>,
     *     category: array<string, mixed>,
     *     participants: list<array<string, mixed>>,
     *     form: array<string, mixed>,
     *     definition: array<string, mixed>
     * }
     */
    public function get(int $applicationId): array
    {
        $row = $this->applicationRow($applicationId, false);

        if ($row === null) {
            throw new DomainException('No fue posible localizar la solicitud.');
        }

        return $this->buildContext($row);
    }

    public function assertReadyForSubmission(int $applicationId): void
    {
        $context = $this->get($applicationId);
        $this->assertEditable($context['application']);
        $this->validatePayload((string) $context['category']['code'], [
            'email'        => $context['application']['email'],
            'participants' => $context['participants'],
            'form'         => $context['form'],
        ], true, $applicationId);
        if (($context['definition']['video_required'] ?? false) && $context['video'] === null) {
            throw new ApplicationValidationException([
                'form.video_file' => 'Debes cargar un video MP4 o registrar un enlace HTTPS antes de continuar.',
            ]);
        }
        $this->documentService->assertRequiredPresent(
            $applicationId,
            $context['definition']['documents'] ?? [],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function saveDraft(
        int $applicationId,
        array $payload,
        ?UploadedFile $videoFile = null,
        array $documentFiles = [],
        array $removeDocuments = [],
    ): void
    {
        (new ConvocationSchedule())->assertDraftEditingOpen();

        $newPrivatePath = null;
        $oldPrivatePathToDelete = null;
        $newDocumentPaths = [];
        $this->db->resetTransStatus();
        $this->db->transBegin();

        try {
            $row = $this->applicationRow($applicationId, true);
            $this->assertEditable($row);
            $existingVideo = $this->db->table('application_videos')
                ->where('application_id', $applicationId)
                ->get()
                ->getRowArray();

            $normalized = $this->validatePayload(
                (string) $row['category_code'],
                $payload,
                false,
                $applicationId,
            );
            $videoUrl = trim((string) ($normalized['form']['video_url'] ?? ''));
            $hasVideoFile = $this->videoStorage->hasUpload($videoFile);
            $removeVideo = ($payload['remove_video'] ?? null) === '1';
            if (($hasVideoFile && $videoUrl !== '') || ($removeVideo && $hasVideoFile)) {
                throw new ApplicationValidationException([
                    'form.video_file' => 'Elige una sola opción: archivo MP4 o enlace HTTPS.',
                ]);
            }
            if ($removeVideo) {
                $videoUrl = '';
                $normalized['form']['video_url'] = '';
            }

            $videoRecord = null;
            if ($hasVideoFile && $videoFile !== null) {
                try {
                    $stored = $this->videoStorage->store($applicationId, $videoFile);
                } catch (DomainException $exception) {
                    throw new ApplicationValidationException([
                        'form.video_file' => $exception->getMessage(),
                    ]);
                }
                $newPrivatePath = $stored['private_path'];
                $videoRecord = [
                    'source_type' => 'file',
                    'private_path' => $stored['private_path'],
                    'external_url' => null,
                    'original_name' => $stored['original_name'],
                    'mime_type' => $stored['mime_type'],
                    'size_bytes' => $stored['size_bytes'],
                    'sha256' => $stored['sha256'],
                ];
                $normalized['form']['video_url'] = '';
            } elseif ($videoUrl !== '') {
                $videoRecord = [
                    'source_type' => 'url',
                    'private_path' => null,
                    'external_url' => $videoUrl,
                    'original_name' => null,
                    'mime_type' => null,
                    'size_bytes' => null,
                    'sha256' => null,
                ];
            }
            $now = date('Y-m-d H:i:s');

            $this->assertWrite($this->db->table('applications')
                ->where('id', $applicationId)
                ->update([
                    'email'      => $normalized['email'],
                    'email_hash' => hash('sha256', $normalized['email']),
                    'version'    => ((int) $row['version']) + 1,
                    'updated_at' => $now,
                ]));

            $this->assertWrite($this->db->table('participants')
                ->where('application_id', $applicationId)
                ->delete());

            foreach ($normalized['participants'] as $index => $participant) {
                $this->assertWrite($this->db->table('participants')->insert([
                    'application_id'   => $applicationId,
                    'member_number'    => $index + 1,
                    'role'             => $index === 0 ? 'responsable' : 'integrante',
                    'is_primary'       => $index === 0 ? 1 : 0,
                    'curp'             => $participant['curp'],
                    'first_name'       => $participant['first_name'],
                    'last_name'        => $participant['last_name'],
                    'second_last_name' => $participant['second_last_name'],
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]));
            }

            $profileTable = $this->profileTableFor((string) $row['category_code']);
            $profileUpdate = $this->profileColumnsFor(
                (string) $row['category_code'],
                $normalized['form'],
            ) + [
                'form_data' => json_encode($normalized['form'], JSON_THROW_ON_ERROR),
                'updated_at' => $now,
            ];
            $this->assertWrite($this->db->table($profileTable)
                ->where('application_id', $applicationId)
                ->update($profileUpdate));

            $definition = $this->definitionFor((string) $row['category_code']);
            $newDocumentPaths = $this->documentService->synchronizeDraft(
                $applicationId,
                $definition['documents'] ?? [],
                $documentFiles,
                $removeDocuments,
            );

            if ($videoRecord !== null) {
                $videoRecord += [
                    'application_id' => $applicationId,
                    'updated_at' => $now,
                ];
                if ($existingVideo === null) {
                    $videoRecord['created_at'] = $now;
                    $this->assertWrite($this->db->table('application_videos')->insert($videoRecord));
                } else {
                    $this->assertWrite($this->db->table('application_videos')
                        ->where('application_id', $applicationId)
                        ->update($videoRecord));
                }
                if (($existingVideo['private_path'] ?? null) !== $newPrivatePath) {
                    $oldPrivatePathToDelete = $existingVideo['private_path'] ?? null;
                }
            } elseif ($removeVideo && $existingVideo !== null) {
                $oldPrivatePathToDelete = $existingVideo['private_path'] ?? null;
                $this->assertWrite($this->db->table('application_videos')
                    ->where('application_id', $applicationId)
                    ->delete());
                $normalized['form']['video_url'] = '';
                $this->assertWrite($this->db->table($profileTable)
                    ->where('application_id', $applicationId)
                    ->update([
                        'form_data' => json_encode($normalized['form'], JSON_THROW_ON_ERROR),
                        'updated_at' => $now,
                    ]));
            }

            $this->recordHistory($applicationId, 'draft_saved', 'borrador', 'borrador', [
                'version' => ((int) $row['version']) + 1,
                'video_source' => $videoRecord['source_type'] ?? ($removeVideo ? 'removed' : 'unchanged'),
            ]);

            $this->commitOrFail();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            $this->videoStorage->delete($newPrivatePath);
            $this->documentService->cleanup($newDocumentPaths);
            $this->translateDuplicate($exception);
            throw $exception;
        }

        $this->videoStorage->delete($oldPrivatePathToDelete);
    }

    /**
     * @param array<string, mixed> $confirmation
     */
    public function submit(
        int $applicationId,
        array $confirmation,
        ?string $ipAddress,
        ?string $userAgent,
    ): void {
        (new ConvocationSchedule())->assertDraftEditingOpen();

        $this->db->resetTransStatus();
        $this->db->transBegin();

        try {
            $row = $this->applicationRow($applicationId, true);
            $this->assertEditable($row);
            $context = $this->buildContext($row);

            $payload = [
                'email'        => $context['application']['email'],
                'participants' => $context['participants'],
                'form'         => $context['form'],
            ];
            $this->validatePayload(
                (string) $row['category_code'],
                $payload,
                true,
                $applicationId,
            );
            $this->documentService->assertRequiredPresent(
                $applicationId,
                $context['definition']['documents'] ?? [],
            );

            $errors = [];
            if (($confirmation['confirm_submit'] ?? null) !== '1') {
                $errors['confirm_submit'] = 'Confirma que deseas enviar definitivamente la solicitud.';
            }
            if (($confirmation['accept_declarations'] ?? null) !== '1') {
                $errors['accept_declarations'] = 'Debes aceptar las declaraciones provisionales vigentes.';
            }
            if ($errors !== []) {
                throw new ApplicationValidationException($errors);
            }

            $now = date('Y-m-d H:i:s');
            $this->assertWrite($this->db->table('applications')
                ->where('id', $applicationId)
                ->where('status', 'borrador')
                ->update([
                    'status'       => 'enviada',
                    'version'      => ((int) $row['version']) + 1,
                    'submitted_at' => $now,
                    'updated_at'   => $now,
                ]));

            $this->assertWrite($this->db->table('legal_acceptances')->insert([
                'application_id'   => $applicationId,
                'document_type'    => 'submission_declarations',
                'document_version' => $this->forms->declarationsVersion,
                'accepted_at'      => $now,
                'ip_address'       => $ipAddress,
                'user_agent'       => $userAgent !== null ? mb_substr($userAgent, 0, 500) : null,
                'created_at'       => $now,
            ]));
            $this->assertWrite($this->db->table('legal_acceptances')->insert([
                'application_id'   => $applicationId,
                'document_type'    => 'terms',
                'document_version' => $this->forms->declarationsVersion,
                'accepted_at'      => $now,
                'ip_address'       => $ipAddress,
                'user_agent'       => $userAgent !== null ? mb_substr($userAgent, 0, 500) : null,
                'created_at'       => $now,
            ]));
            $this->documentService->lockAll($applicationId);

            $this->recordHistory($applicationId, 'application_submitted', 'borrador', 'enviada', [
                'declarations_version' => $this->forms->declarationsVersion,
            ]);

            $this->commitOrFail();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }

        $this->notifySafely($applicationId, 'application_submitted', 'application_submitted:' . $applicationId);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{
     *     email: string,
     *     participants: list<array{curp: string, first_name: string, last_name: string, second_last_name: string|null}>,
     *     form: array<string, string>
     * }
     */
    private function validatePayload(
        string $categoryCode,
        array $payload,
        bool $forSubmit,
        ?int $applicationId = null,
    ): array
    {
        $errors = [];

        try {
            $email = $this->emailNormalizer->normalize((string) ($payload['email'] ?? ''));
        } catch (DomainException $exception) {
            $email = trim((string) ($payload['email'] ?? ''));
            $errors['email'] = $exception->getMessage();
        }

        $participantsInput = is_array($payload['participants'] ?? null)
            ? array_values($payload['participants'])
            : [];
        $participants = [];
        $seenCurps = [];

        if (count($participantsInput) !== 1) {
            $errors['participants'] = 'La solicitud requiere exactamente una persona participante.';
        }

        foreach ($participantsInput as $index => $participant) {
            if (! is_array($participant)) {
                $errors["participants.{$index}"] = 'Los datos de la persona no son válidos.';
                continue;
            }

            $curpCandidate = $this->curpNormalizer->canonicalize((string) ($participant['curp'] ?? ''));
            if ($curpCandidate !== '' && $this->curpAlreadyExists($curpCandidate, $applicationId)) {
                $curp = $curpCandidate;
                $errors["participants.{$index}.curp"] = 'La CURP ya tiene un registro en el sistema.';
            } else {
                try {
                    $curp = $this->curpNormalizer->normalize($curpCandidate);
                } catch (DomainException $exception) {
                    $curp = $curpCandidate;
                    $errors["participants.{$index}.curp"] = $exception->getMessage();
                }
            }

            $firstName = trim((string) ($participant['first_name'] ?? ''));
            $lastName = trim((string) ($participant['last_name'] ?? ''));
            $secondLastName = trim((string) ($participant['second_last_name'] ?? ''));

            if ($firstName === '' || mb_strlen($firstName) > 100) {
                $errors["participants.{$index}.first_name"] = 'Captura un nombre válido de hasta 100 caracteres.';
            }
            if ($lastName === '' || mb_strlen($lastName) > 150) {
                $errors["participants.{$index}.last_name"] = 'Captura un primer apellido válido de hasta 150 caracteres.';
            }
            if (mb_strlen($secondLastName) > 150) {
                $errors["participants.{$index}.second_last_name"] = 'El segundo apellido no puede exceder 150 caracteres.';
            }
            if ($curp !== '' && isset($seenCurps[$curp])) {
                $errors["participants.{$index}.curp"] = 'Una CURP no puede repetirse dentro de la solicitud.';
            }
            $seenCurps[$curp] = true;
            $participants[] = [
                'curp'             => $curp,
                'first_name'       => $firstName,
                'last_name'        => $lastName,
                'second_last_name' => $secondLastName !== '' ? $secondLastName : null,
            ];
        }

        $definition = $this->definitionFor($categoryCode);
        $formInput = is_array($payload['form'] ?? null) ? $payload['form'] : $payload;
        $form = [];

        foreach ($definition['fields'] as $field) {
            $name = (string) $field['name'];
            $value = trim((string) ($formInput[$name] ?? ''));

            if ($forSubmit && ($field['required'] ?? false) && $value === '') {
                $errors["form.{$name}"] = 'Este campo es obligatorio para enviar.';
                $form[$name] = $value;
                continue;
            }
            if ($value === '') {
                $form[$name] = $value;
                continue;
            }
            if ($name === 'municipality') {
                $canonicalMunicipality = $this->municipalities->canonicalize($value);
                if ($canonicalMunicipality === null) {
                    $errors["form.{$name}"] = 'Selecciona uno de los 125 municipios del Estado de México.';
                } else {
                    $value = $canonicalMunicipality;
                }
            }
            $form[$name] = $value;
            if (isset($field['max']) && mb_strlen($value) > (int) $field['max']) {
                $errors["form.{$name}"] = "No puede exceder {$field['max']} caracteres.";
            }
            if (($field['type'] ?? '') === 'number') {
                if (filter_var($value, FILTER_VALIDATE_INT) === false
                    || (int) $value < (int) ($field['min'] ?? PHP_INT_MIN)
                    || (int) $value > (int) ($field['maxNumber'] ?? PHP_INT_MAX)
                ) {
                    $errors["form.{$name}"] = 'Captura un número dentro del rango permitido.';
                }
            }
            if (($field['type'] ?? '') === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                $errors["form.{$name}"] = 'Captura un correo electrónico válido.';
            }
            if (isset($field['options']) && is_array($field['options']) && ! in_array($value, $field['options'], true)) {
                $errors["form.{$name}"] = 'Selecciona una opción válida.';
            }
            if (in_array(($field['type'] ?? ''), ['url', 'video'], true)) {
                $scheme = parse_url($value, PHP_URL_SCHEME);
                if (filter_var($value, FILTER_VALIDATE_URL) === false || strtolower((string) $scheme) !== 'https') {
                    $errors["form.{$name}"] = 'Captura una URL HTTPS válida.';
                }
            }
        }

        if ($errors !== []) {
            throw new ApplicationValidationException($errors);
        }

        return [
            'email'        => $email,
            'participants' => $participants,
            'form'         => $form,
        ];
    }

    /**
     * @param array<string, mixed>|null $row
     */
    private function assertEditable(?array $row): void
    {
        if ($row === null) {
            throw new DomainException('No fue posible localizar la solicitud.');
        }
        if (($row['status'] ?? null) !== 'borrador') {
            throw new DomainException('La solicitud ya fue enviada y no admite modificaciones.');
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function applicationRow(int $applicationId, bool $lock): ?array
    {
        $sql = <<<'SQL'
            SELECT a.*, c.code AS category_code, c.name AS category_name, c.folio_prefix
            FROM applications a
            INNER JOIN categories c ON c.id = a.category_id
            WHERE a.id = ?
            SQL;
        if ($lock) {
            $sql .= ' FOR UPDATE';
        }

        return $this->db->query($sql, [$applicationId])->getRowArray();
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function buildContext(array $row): array
    {
        $applicationId = (int) $row['id'];
        $categoryCode = (string) $row['category_code'];
        $profile = $this->db->table($this->profileTableFor($categoryCode))
            ->where('application_id', $applicationId)
            ->get()
            ->getRowArray() ?? [];
        $formData = $profile['form_data'] ?? null;
        if (is_string($formData) && $formData !== '') {
            $decoded = json_decode($formData, true);
            $formData = is_array($decoded) ? $decoded : [];
        }
        $formData = is_array($formData) ? $formData : [];
        $video = $this->db->table('application_videos')
            ->where('application_id', $applicationId)
            ->get()
            ->getRowArray();
        if ($video === null && trim((string) ($formData['video_url'] ?? '')) !== '') {
            $video = [
                'id' => null,
                'application_id' => $applicationId,
                'source_type' => 'url',
                'external_url' => $formData['video_url'],
                'private_path' => null,
                'original_name' => null,
                'mime_type' => null,
                'size_bytes' => null,
                'sha256' => null,
            ];
        }

        return [
            'application' => [
                'id'           => $applicationId,
                'folio'        => $row['folio'],
                'email'        => $row['email'],
                'status'       => $row['status'],
                'version'      => (int) $row['version'],
                'submitted_at' => $row['submitted_at'],
            ],
            'category' => [
                'code' => $categoryCode,
                'name' => $row['category_name'],
            ],
            'participants' => $this->db->table('participants')
                ->where('application_id', $applicationId)
                ->orderBy('member_number', 'ASC')
                ->get()
                ->getResultArray(),
            'form' => $formData,
            'definition' => $this->definitionFor($categoryCode),
            'video' => $video,
            'documents' => $this->documentService->listing(
                $applicationId,
                $this->definitionFor($categoryCode)['documents'] ?? [],
            ),
            'admin_comment' => $this->db->table('admin_comments')
                ->select('comment, created_at')
                ->where('application_id', $applicationId)
                ->where('is_visible_to_participant', 1)
                ->orderBy('created_at', 'DESC')
                ->get(1)
                ->getRowArray(),
            'convocation' => [
                'closed' => (new ConvocationSchedule())->isClosed(),
                'close_at' => (new ConvocationSchedule())->closeAt(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function definitionFor(string $categoryCode): array
    {
        if (! isset($this->forms->categories[$categoryCode])) {
            throw new DomainException('La categoría no tiene un formulario configurado.');
        }

        return $this->forms->categories[$categoryCode];
    }

    private function profileTableFor(string $categoryCode): string
    {
        return match ($categoryCode) {
            'cocineras-cocineros-tradicionales' => 'cook_profiles',
            'restaurantes' => 'restaurant_profiles',
            'joven-talento-gastronomia' => 'student_team_profiles',
            'bebidas-tradicionales-ancestrales' => 'beverage_profiles',
            default => throw new DomainException('La categoría no tiene un perfil configurado.'),
        };
    }

    /**
     * @param array<string, string> $form
     *
     * @return array<string, mixed>
     */
    private function profileColumnsFor(string $categoryCode, array $form): array
    {
        $columns = ['municipality' => $form['municipality'] !== '' ? $form['municipality'] : null];

        return $columns + match ($categoryCode) {
            'cocineras-cocineros-tradicionales' => [
                'years_experience' => $form['years_experience'] !== '' ? (int) $form['years_experience'] : null,
                'signature_dish'   => $form['signature_dish'] !== '' ? $form['signature_dish'] : null,
            ],
            'restaurantes' => [
                'business_name'    => $form['business_name'] !== '' ? $form['business_name'] : null,
                'legal_name'       => $form['legal_name'] !== '' ? $form['legal_name'] : null,
                'culinary_concept' => $form['culinary_concept'] !== '' ? $form['culinary_concept'] : null,
            ],
            'joven-talento-gastronomia' => [
                'institution_name' => $form['institution_name'] !== '' ? $form['institution_name'] : null,
                'campus'           => $form['campus'] !== '' ? $form['campus'] : null,
                'proposal_name'    => $form['proposal_name'] !== '' ? $form['proposal_name'] : null,
            ],
            'bebidas-tradicionales-ancestrales' => [
                'project_name'  => $form['project_name'] !== '' ? $form['project_name'] : null,
                'beverage_name' => $form['beverage_name'] !== '' ? $form['beverage_name'] : null,
                'beverage_type' => $form['beverage_type'] !== '' ? $form['beverage_type'] : null,
            ],
        };
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function recordHistory(
        int $applicationId,
        string $action,
        ?string $fromStatus,
        ?string $toStatus,
        array $metadata,
    ): void {
        $this->assertWrite($this->db->table('application_histories')->insert([
            'application_id' => $applicationId,
            'action'         => $action,
            'from_status'    => $fromStatus,
            'to_status'      => $toStatus,
            'actor_type'     => 'participant',
            'metadata'       => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at'     => date('Y-m-d H:i:s'),
        ]));
    }

    private function commitOrFail(): void
    {
        if ($this->db->transStatus() === false) {
            throw new DatabaseException('No fue posible guardar la solicitud.');
        }
        $this->db->transCommit();
    }

    private function assertWrite(bool $succeeded): void
    {
        if ($succeeded) {
            return;
        }
        $error = $this->db->error();
        throw new DatabaseException(
            $error['message'] !== '' ? $error['message'] : 'No fue posible guardar la solicitud.',
            (int) $error['code'],
        );
    }

    private function notifySafely(int $applicationId, string $event, string $idempotencyReference): void
    {
        try {
            (new EmailNotificationService($this->db))->enqueueAndAttempt(
                $applicationId,
                $event,
                [],
                $idempotencyReference,
            );
        } catch (Throwable $exception) {
            log_message('error', 'No fue posible encolar una notificación de solicitud: {type}', [
                'type' => $exception::class,
            ]);
        }
    }

    private function translateDuplicate(Throwable $exception): void
    {
        if ($exception instanceof DatabaseException && (int) $exception->getCode() === 1062) {
            if (str_contains($exception->getMessage(), 'uq_participants_curp')) {
                throw new DomainException(
                    'La CURP ya tiene un registro en el sistema.',
                    0,
                    $exception,
                );
            }

            throw new DomainException(
                'Ya existe una solicitud con el correo proporcionado.',
                0,
                $exception,
            );
        }
    }

    private function curpAlreadyExists(string $curp, ?int $exceptApplicationId = null): bool
    {
        $builder = $this->db->table('participants')->where('curp', $curp);

        if ($exceptApplicationId !== null) {
            $builder->where('application_id !=', $exceptApplicationId);
        }

        return $builder->countAllResults() > 0;
    }
}
