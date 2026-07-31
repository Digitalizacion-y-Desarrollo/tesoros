<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Database;
use DomainException;
use Throwable;

final class DraftApplicationService
{
    private BaseConnection $db;
    private CurpNormalizer $curpNormalizer;
    private EmailNormalizer $emailNormalizer;
    private FolioGenerator $folioGenerator;

    public function __construct(
        ?BaseConnection $db = null,
        ?CurpNormalizer $curpNormalizer = null,
        ?EmailNormalizer $emailNormalizer = null,
    ) {
        $this->db = $db ?? Database::connect();
        $this->curpNormalizer = $curpNormalizer ?? new CurpNormalizer();
        $this->emailNormalizer = $emailNormalizer ?? new EmailNormalizer();
        $this->folioGenerator = new FolioGenerator($this->db);
    }

    /**
     * @param list<array{
     *     curp: string,
     *     first_name: string,
     *     last_name: string,
     *     second_last_name?: string|null
     * }> $participants
     *
     * @param array{
     *     document_version: string,
     *     ip_address?: string|null,
     *     user_agent?: string|null
     * }|null $privacyAcceptance
     *
     * @return array{id: int, folio: string, status: string}
     */
    public function create(
        string $categoryCode,
        string $email,
        array $participants,
        ?array $privacyAcceptance = null,
    ): array
    {
        (new ConvocationSchedule())->assertRegistrationOpen();

        $category = $this->db->table('categories')
            ->where('code', trim($categoryCode))
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        if ($category === null) {
            throw new DomainException('La categoría indicada no está disponible.');
        }

        $normalizedEmail = $this->emailNormalizer->normalize($email);
        $normalizedParticipants = $this->normalizeParticipants($category['code'], $participants);
        $now = date('Y-m-d H:i:s');

        $this->db->resetTransStatus();
        $this->db->transBegin();

        try {
            $folio = $this->folioGenerator->next($category);

            $applicationInserted = $this->db->table('applications')->insert([
                'category_id' => $category['id'],
                'folio'       => $folio,
                'email'       => $normalizedEmail,
                'email_hash'  => hash('sha256', $normalizedEmail),
                'status'      => 'borrador',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $this->assertDatabaseWrite($applicationInserted);
            $applicationId = (int) $this->db->insertID();

            foreach ($normalizedParticipants as $index => $participant) {
                $participantInserted = $this->db->table('participants')->insert([
                    'application_id'  => $applicationId,
                    'member_number'   => $index + 1,
                    'role'            => $index === 0 ? 'responsable' : 'integrante',
                    'is_primary'      => $index === 0 ? 1 : 0,
                    'curp'            => $participant['curp'],
                    'first_name'      => $participant['first_name'],
                    'last_name'       => $participant['last_name'],
                    'second_last_name' => $participant['second_last_name'],
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
                $this->assertDatabaseWrite($participantInserted);
            }

            $profileTable = $this->profileTableFor($category['code']);
            $profileInserted = $this->db->table($profileTable)->insert([
                'application_id' => $applicationId,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
            $this->assertDatabaseWrite($profileInserted);

            $historyInserted = $this->db->table('application_histories')->insert([
                'application_id' => $applicationId,
                'action'         => 'draft_created',
                'from_status'    => null,
                'to_status'      => 'borrador',
                'actor_type'     => 'participant',
                'metadata'       => json_encode(['category' => $category['code']], JSON_THROW_ON_ERROR),
                'created_at'     => $now,
            ]);
            $this->assertDatabaseWrite($historyInserted);

            if ($privacyAcceptance !== null) {
                $acceptanceInserted = $this->db->table('legal_acceptances')->insert([
                    'application_id'   => $applicationId,
                    'document_type'    => 'privacy_notice',
                    'document_version' => $privacyAcceptance['document_version'],
                    'accepted_at'      => $now,
                    'ip_address'       => $privacyAcceptance['ip_address'] ?? null,
                    'user_agent'       => isset($privacyAcceptance['user_agent'])
                        ? mb_substr((string) $privacyAcceptance['user_agent'], 0, 500)
                        : null,
                    'created_at'       => $now,
                ]);
                $this->assertDatabaseWrite($acceptanceInserted);
            }

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('No fue posible crear la solicitud.');
            }

            $this->db->transCommit();

            return [
                'id'     => $applicationId,
                'folio'  => $folio,
                'status' => 'borrador',
            ];
        } catch (Throwable $exception) {
            $this->db->transRollback();

            if ($exception instanceof DatabaseException && (int) $exception->getCode() === 1062) {
                if (str_contains($exception->getMessage(), 'uq_participants_curp')) {
                    throw new DomainException('La CURP ya tiene un registro en el sistema.', 0, $exception);
                }

                throw new DomainException('Ya existe una solicitud con el correo proporcionado.', 0, $exception);
            }

            throw $exception;
        }
    }

    /**
     * @param list<array<string, mixed>> $participants
     *
     * @return list<array{curp: string, first_name: string, last_name: string, second_last_name: string|null}>
     */
    private function normalizeParticipants(string $categoryCode, array $participants): array
    {
        if (count($participants) !== 1) {
            throw new DomainException('La categoría requiere exactamente una persona participante.');
        }

        $normalized = [];
        $requestCurps = [];

        foreach ($participants as $participant) {
            $curpCandidate = $this->curpNormalizer->canonicalize((string) ($participant['curp'] ?? ''));
            if ($curpCandidate !== '' && $this->curpAlreadyExists($curpCandidate)) {
                throw new DomainException('La CURP ya tiene un registro en el sistema.');
            }

            $curp = $this->curpNormalizer->normalize($curpCandidate);
            $firstName = trim((string) ($participant['first_name'] ?? ''));
            $lastName = trim((string) ($participant['last_name'] ?? ''));

            if ($firstName === '' || $lastName === '') {
                throw new DomainException('El nombre y primer apellido son obligatorios.');
            }

            if (isset($requestCurps[$curp])) {
                throw new DomainException('Una CURP no puede repetirse dentro de la solicitud.');
            }

            $requestCurps[$curp] = true;
            $normalized[] = [
                'curp'             => $curp,
                'first_name'       => $firstName,
                'last_name'        => $lastName,
                'second_last_name' => ($secondLastName = trim((string) ($participant['second_last_name'] ?? ''))) !== ''
                    ? $secondLastName
                    : null,
            ];
        }

        return $normalized;
    }

    private function curpAlreadyExists(string $curp): bool
    {
        return $this->db->table('participants')
            ->where('curp', $curp)
            ->countAllResults() > 0;
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

    private function assertDatabaseWrite(bool $succeeded): void
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
}
