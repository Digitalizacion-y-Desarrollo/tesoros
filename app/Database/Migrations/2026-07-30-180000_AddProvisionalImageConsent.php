<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddProvisionalImageConsent extends Migration
{
    public function up(): void
    {
        $type = 'image_consent';
        $version = 'PROVISIONAL-DEV-2026-07';
        $exists = $this->db->table('legal_documents')
            ->where('document_type', $type)
            ->where('version', $version)
            ->countAllResults() > 0;
        if (! $exists) {
            $now = date('Y-m-d H:i:s');
            $this->db->table('legal_documents')->insert([
                'document_type' => $type,
                'version' => $version,
                'title' => 'Consentimiento de tratamiento de datos e imagen',
                'content' => "DOCUMENTO PROVISIONAL PARA DESARROLLO.\n\n"
                    . "El consentimiento definitivo para tratamiento de datos personales, fotografías y material audiovisual "
                    . "debe ser proporcionado y aprobado por la institución antes de producción.",
                'is_provisional' => 1,
                'is_active' => 1,
                'effective_at' => $now,
                'created_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $this->db->table('legal_documents')
            ->where('document_type', 'image_consent')
            ->where('version', 'PROVISIONAL-DEV-2026-07')
            ->delete();
    }
}
