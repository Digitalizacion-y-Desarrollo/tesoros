<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Database;
use DomainException;
use Throwable;

final class AdminApplicationService
{
    private BaseConnection $db;
    private EmailNormalizer $emailNormalizer;
    private CurpNormalizer $curpNormalizer;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
        $this->emailNormalizer = new EmailNormalizer();
        $this->curpNormalizer = new CurpNormalizer();
    }

    /** @return array<string, mixed> */
    public function dashboard(): array
    {
        $municipalityExpression = 'COALESCE(cp.municipality, rp.municipality, sp.municipality, bp.municipality)';
        $recent = $this->baseListBuilder()->orderBy('a.updated_at', 'DESC')->limit(8)
            ->get()->getResultArray();
        foreach ($recent as &$row) {
            $row['email_masked'] = $this->maskEmail((string) $row['email']);
            $row['curp_masked'] = $this->maskCurp((string) ($row['curp'] ?? ''));
            unset($row['email'], $row['curp']);
        }

        return [
            'total' => $this->db->table('applications')->countAllResults(),
            'by_status' => $this->db->table('applications')
                ->select('status, COUNT(*) AS total', false)->groupBy('status')
                ->orderBy('total', 'DESC')->get()->getResultArray(),
            'by_category' => $this->db->table('applications a')
                ->select('c.code, c.name, COUNT(*) AS total', false)
                ->join('categories c', 'c.id = a.category_id')
                ->groupBy(['c.id', 'c.code', 'c.name'])->orderBy('c.sort_order', 'ASC')
                ->get()->getResultArray(),
            'by_municipality' => $this->db->table('applications a')
                ->select($municipalityExpression . ' AS municipality, COUNT(*) AS total', false)
                ->join('cook_profiles cp', 'cp.application_id = a.id', 'left')
                ->join('restaurant_profiles rp', 'rp.application_id = a.id', 'left')
                ->join('student_team_profiles sp', 'sp.application_id = a.id', 'left')
                ->join('beverage_profiles bp', 'bp.application_id = a.id', 'left')
                ->where($municipalityExpression . ' IS NOT NULL', null, false)
                ->groupBy($municipalityExpression, false)->orderBy('total', 'DESC')
                ->limit(15)->get()->getResultArray(),
            'recent' => $recent,
        ];
    }

    /** @return array{rows:list<array<string,mixed>>,total:int,page:int,pages:int,filters:array<string,string>} */
    public function listing(array $filters, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(10, min(100, $perPage));
        $normalized = [
            'q' => trim((string) ($filters['q'] ?? '')),
            'category' => trim((string) ($filters['category'] ?? '')),
            'status' => trim((string) ($filters['status'] ?? '')),
            'municipality' => trim((string) ($filters['municipality'] ?? '')),
        ];
        $builder = $this->baseListBuilder();
        $this->applyFilters($builder, $normalized);
        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();
        $rows = $builder->orderBy('a.updated_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['email_masked'] = $this->maskEmail((string) $row['email']);
            $row['curp_masked'] = $this->maskCurp((string) ($row['curp'] ?? ''));
            unset($row['email'], $row['curp']);
        }

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'filters' => $normalized,
        ];
    }

    /** @return array{rows:list<array<string,mixed>>,filters:array<string,string>} */
    public function exportRows(array $filters, int $limit = 100000): array
    {
        $normalized = [
            'q' => trim((string) ($filters['q'] ?? '')),
            'category' => trim((string) ($filters['category'] ?? '')),
            'status' => trim((string) ($filters['status'] ?? '')),
            'municipality' => trim((string) ($filters['municipality'] ?? '')),
        ];
        $builder = $this->baseListBuilder()
            ->select('a.submitted_at, p.second_last_name');
        $this->applyFilters($builder, $normalized);

        return [
            'rows' => $builder->orderBy('a.updated_at', 'DESC')
                ->limit(max(1, min(100000, $limit)))
                ->get()->getResultArray(),
            'filters' => $normalized,
        ];
    }

    /** @return array<string, mixed> */
    public function detail(int $applicationId): array
    {
        $application = $this->db->table('applications a')
            ->select('a.*, c.code AS category_code, c.name AS category_name')
            ->join('categories c', 'c.id = a.category_id')
            ->where('a.id', $applicationId)->get()->getRowArray();
        if ($application === null) {
            throw new DomainException('No fue posible localizar la solicitud.');
        }
        $profileTable = match ($application['category_code']) {
            'cocineras-cocineros-tradicionales' => 'cook_profiles',
            'restaurantes' => 'restaurant_profiles',
            'joven-talento-gastronomia' => 'student_team_profiles',
            'bebidas-tradicionales-ancestrales' => 'beverage_profiles',
            default => throw new DomainException('La categoría no tiene un perfil configurado.'),
        };
        $profile = $this->db->table($profileTable)->where('application_id', $applicationId)
            ->get()->getRowArray() ?? [];
        $form = json_decode((string) ($profile['form_data'] ?? '{}'), true);
        $documents = $this->db->query(
            'SELECT d.*, v.id AS version_id, v.original_name, v.mime_type, v.size_bytes, v.created_at AS uploaded_at
             FROM documents d
             LEFT JOIN document_versions v ON v.document_id = d.id AND v.version_number = d.active_version_number
             WHERE d.application_id = ? ORDER BY d.id',
            [$applicationId],
        )->getResultArray();
        foreach ($documents as &$document) {
            $document['versions'] = $this->db->table('document_versions')
                ->select('id, version_number, original_name, mime_type, size_bytes, sha256, uploaded_by_type, created_at')
                ->where('document_id', $document['id'])->orderBy('version_number', 'DESC')
                ->get()->getResultArray();
        }
        unset($document);
        $video = $this->db->table('application_videos')->where('application_id', $applicationId)
            ->get()->getRowArray();
        if ($video !== null && $video['source_type'] === 'url') {
            $video['preview'] = (new ExternalVideoPreviewService())->describe((string) $video['external_url']);
        }

        return [
            'application' => $application,
            'participants' => $this->db->table('participants')->where('application_id', $applicationId)
                ->orderBy('member_number', 'ASC')->get()->getResultArray(),
            'profile' => $profile,
            'form' => is_array($form) ? $form : [],
            'documents' => $documents,
            'video' => $video,
            'comments' => $this->db->table('admin_comments')->where('application_id', $applicationId)
                ->orderBy('created_at', 'DESC')->get()->getResultArray(),
            'history' => $this->db->table('application_histories')->where('application_id', $applicationId)
                ->orderBy('created_at', 'DESC')->get()->getResultArray(),
        ];
    }

    public function updatePersonalData(
        int $applicationId,
        string $email,
        array $participantsInput,
        ?string $actor,
    ): void {
        $normalizedEmail = $this->emailNormalizer->normalize($email);
        $this->db->resetTransStatus();
        $this->db->transBegin();
        try {
            $application = $this->db->query(
                'SELECT * FROM applications WHERE id = ? FOR UPDATE',
                [$applicationId],
            )->getRowArray();
            if ($application === null) {
                throw new DomainException('No fue posible localizar la solicitud.');
            }
            if ($application['status'] === 'cancelada') {
                throw new DomainException('No se pueden editar datos de una solicitud cancelada.');
            }
            $participants = $this->db->table('participants')->where('application_id', $applicationId)
                ->orderBy('member_number', 'ASC')->get()->getResultArray();
            if (count($participantsInput) !== count($participants)) {
                throw new DomainException('No es posible agregar o eliminar integrantes desde esta operación.');
            }

            $changes = [];
            if ($normalizedEmail !== $application['email']) {
                $changes['email'] = ['from' => $this->maskEmail((string) $application['email']), 'to' => $this->maskEmail($normalizedEmail)];
            }
            $now = date('Y-m-d H:i:s');
            $this->assertWrite($this->db->table('applications')->where('id', $applicationId)->update([
                'email' => $normalizedEmail,
                'email_hash' => hash('sha256', $normalizedEmail),
                'version' => ((int) $application['version']) + 1,
                'updated_at' => $now,
            ]));

            foreach ($participants as $index => $existing) {
                $input = $participantsInput[$index] ?? [];
                $curp = $this->curpNormalizer->normalize((string) ($input['curp'] ?? ''));
                $firstName = trim((string) ($input['first_name'] ?? ''));
                $lastName = trim((string) ($input['last_name'] ?? ''));
                $secondLastName = trim((string) ($input['second_last_name'] ?? ''));
                if ($firstName === '' || $lastName === '' || mb_strlen($firstName) > 100
                    || mb_strlen($lastName) > 150 || mb_strlen($secondLastName) > 150) {
                    throw new DomainException('Revisa el nombre y los apellidos de las personas participantes.');
                }
                if ($curp !== $existing['curp']) {
                    $changes["participant_{$existing['id']}_curp"] = [
                        'from_hash' => hash('sha256', (string) $existing['curp']),
                        'to_hash' => hash('sha256', $curp),
                    ];
                }
                if ($firstName !== $existing['first_name'] || $lastName !== $existing['last_name']
                    || $secondLastName !== (string) ($existing['second_last_name'] ?? '')) {
                    $changes["participant_{$existing['id']}_name"] = true;
                }
                $this->assertWrite($this->db->table('participants')->where('id', $existing['id'])->update([
                    'curp' => $curp,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'second_last_name' => $secondLastName !== '' ? $secondLastName : null,
                    'updated_at' => $now,
                ]));
            }
            $this->history($applicationId, 'admin_personal_data_updated', $actor, ['changes' => $changes], $now);
            if ($this->db->transStatus() === false) {
                throw new DatabaseException('No fue posible actualizar los datos personales.');
            }
            $this->db->transCommit();
        } catch (Throwable $exception) {
            $this->db->transRollback();
            if ($exception instanceof DatabaseException && (int) $exception->getCode() === 1062) {
                throw new DomainException(str_contains($exception->getMessage(), 'uq_participants_curp')
                    ? 'La CURP ya tiene un registro en el sistema.'
                    : 'Ya existe una solicitud con el correo proporcionado.', 0, $exception);
            }
            throw $exception;
        }
    }

    public function addComment(int $applicationId, string $comment, bool $visible, ?string $actor): void
    {
        $comment = trim($comment);
        if ($comment === '' || mb_strlen($comment) > 4000) {
            throw new DomainException('Escribe un comentario de hasta 4000 caracteres.');
        }
        $now = date('Y-m-d H:i:s');
        $this->assertWrite($this->db->table('admin_comments')->insert([
            'application_id' => $applicationId,
            'document_id' => null,
            'comment' => $comment,
            'is_visible_to_participant' => $visible ? 1 : 0,
            'actor_reference' => $actor,
            'created_at' => $now,
        ]));
        $this->history($applicationId, 'admin_comment_added', $actor, ['visible_to_participant' => $visible], $now);
    }

    /** @return array{rows:list<array<string,mixed>>,page:int,pages:int,total:int} */
    public function audit(int $page = 1, int $perPage = 50): array
    {
        $page = max(1, $page);
        $builder = $this->db->table('application_histories h')
            ->select('h.*, a.folio')->join('applications a', 'a.id = h.application_id');
        $total = (clone $builder)->countAllResults();

        return [
            'rows' => $builder->orderBy('h.created_at', 'DESC')
                ->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray(),
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'total' => $total,
        ];
    }

    private function baseListBuilder()
    {
        $municipality = 'COALESCE(cp.municipality, rp.municipality, sp.municipality, bp.municipality)';
        return $this->db->table('applications a')
            ->select("a.id, a.folio, a.email, a.status, a.created_at, a.updated_at,
                c.code AS category_code, c.name AS category_name,
                p.curp, p.first_name, p.last_name,
                {$municipality} AS municipality", false)
            ->join('categories c', 'c.id = a.category_id')
            ->join('participants p', 'p.application_id = a.id AND p.is_primary = 1')
            ->join('cook_profiles cp', 'cp.application_id = a.id', 'left')
            ->join('restaurant_profiles rp', 'rp.application_id = a.id', 'left')
            ->join('student_team_profiles sp', 'sp.application_id = a.id', 'left')
            ->join('beverage_profiles bp', 'bp.application_id = a.id', 'left');
    }

    private function applyFilters($builder, array $filters): void
    {
        if ($filters['q'] !== '') {
            $builder->groupStart()
                ->like('a.folio', $filters['q'])
                ->orLike('a.email', $filters['q'])
                ->orLike('p.first_name', $filters['q'])
                ->orLike('p.last_name', $filters['q'])
                ->orLike('p.curp', strtoupper(str_replace(' ', '', $filters['q'])))
                ->groupEnd();
        }
        if ($filters['category'] !== '') {
            $builder->where('c.code', $filters['category']);
        }
        if ($filters['status'] !== '') {
            $builder->where('a.status', $filters['status']);
        }
        if ($filters['municipality'] !== '') {
            $builder->where(
                'COALESCE(cp.municipality, rp.municipality, sp.municipality, bp.municipality)',
                $filters['municipality'],
            );
        }
    }

    private function history(int $applicationId, string $action, ?string $actor, array $metadata, string $now): void
    {
        $this->assertWrite($this->db->table('application_histories')->insert([
            'application_id' => $applicationId,
            'action' => $action,
            'from_status' => null,
            'to_status' => null,
            'actor_type' => 'admin',
            'actor_reference' => $actor,
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => $now,
        ]));
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        return mb_substr($local, 0, 2) . '***@' . $domain;
    }

    private function maskCurp(string $curp): string
    {
        return mb_strlen($curp) >= 8 ? mb_substr($curp, 0, 4) . '**********' . mb_substr($curp, -4) : '********';
    }

    private function assertWrite(bool $success): void
    {
        if (! $success) {
            $error = $this->db->error();
            throw new DatabaseException($error['message'] ?: 'No fue posible completar la operación.', (int) $error['code']);
        }
    }
}
