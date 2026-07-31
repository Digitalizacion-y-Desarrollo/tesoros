<?php

namespace App\Models;

use App\Domain\ApplicationStatus;
use App\Entities\Application;
use CodeIgniter\Model;

class ApplicationModel extends Model
{
    protected $table = 'applications';
    protected $returnType = Application::class;
    protected $allowedFields = [
        'category_id',
        'folio',
        'email',
        'email_hash',
        'status',
        'version',
        'submitted_at',
        'cancelled_at',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'category_id' => 'required|is_natural_no_zero',
        'folio'       => 'required|regex_match[/^TG-2026-(CCT|RES|JTG|BTA)-\d{6}$/]',
        'email'       => 'required|valid_email|max_length[254]',
        'status'      => 'required|in_list[borrador,enviada,en_revision,incompleta,seleccionada,rechazada,cancelada]',
    ];
    protected $validationMessages = [
        'status' => [
            'in_list' => 'El estado de la solicitud no es válido.',
        ],
    ];

    /**
     * Exposes the canonical list without duplicating it in consumers.
     *
     * @return list<string>
     */
    public function statuses(): array
    {
        return ApplicationStatus::values();
    }
}
