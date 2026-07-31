<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateSecurityAuditAndLegalDocuments extends Migration
{
    public function up(): void
    {
        $this->createAuditLog();
        $this->createLegalDocuments();
        $this->seedProvisionalLegalDocuments();
    }

    public function down(): void
    {
        $this->forge->dropTable('legal_documents', true);
        $this->forge->dropTable('audit_log', true);
    }

    private function createAuditLog(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'actor_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'actor_reference' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 80],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'metadata' => ['type' => 'JSON', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['action', 'created_at'], false, false, 'idx_audit_log_action');
        $this->forge->addKey(['application_id', 'created_at'], false, false, 'idx_audit_log_application');
        $this->forge->addKey(['actor_reference', 'created_at'], false, false, 'idx_audit_log_actor');
        $this->forge->addForeignKey(
            'application_id',
            'applications',
            'id',
            'SET NULL',
            'CASCADE',
            'fk_audit_log_application',
        );
        $this->forge->createTable('audit_log', true, ['ENGINE' => 'InnoDB']);
    }

    private function createLegalDocuments(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 60],
            'version' => ['type' => 'VARCHAR', 'constraint' => 80],
            'title' => ['type' => 'VARCHAR', 'constraint' => 180],
            'content' => ['type' => 'TEXT'],
            'is_provisional' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'effective_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['document_type', 'version'], 'uq_legal_documents_type_version');
        $this->forge->addKey(['document_type', 'is_active'], false, false, 'idx_legal_documents_active');
        $this->forge->createTable('legal_documents', true, ['ENGINE' => 'InnoDB']);
    }

    private function seedProvisionalLegalDocuments(): void
    {
        $now = date('Y-m-d H:i:s');
        $version = 'PROVISIONAL-DEV-2026-07';
        $documents = [
            [
                'document_type' => 'privacy_notice',
                'title' => 'Aviso de privacidad',
                'content' => "DOCUMENTO PROVISIONAL PARA DESARROLLO.\n\n"
                    . "La institución responsable deberá proporcionar el aviso de privacidad aprobado antes de publicar la plataforma. "
                    . "Este texto únicamente permite validar el versionado, la consulta y el registro técnico de aceptación.",
            ],
            [
                'document_type' => 'terms',
                'title' => 'Términos y condiciones',
                'content' => "DOCUMENTO PROVISIONAL PARA DESARROLLO.\n\n"
                    . "Los términos definitivos de participación se encuentran pendientes de aprobación institucional. "
                    . "Este contenido no establece derechos, obligaciones ni reglas oficiales de la convocatoria.",
            ],
            [
                'document_type' => 'retention_policy',
                'title' => 'Política de conservación de información',
                'content' => "DOCUMENTO PROVISIONAL PARA DESARROLLO.\n\n"
                    . "Los plazos de conservación y eliminación todavía no han sido definidos por la institución. "
                    . "La arquitectura conserva trazabilidad y permite aplicar la política definitiva cuando sea entregada.",
            ],
        ];

        foreach ($documents as $document) {
            $this->db->table('legal_documents')->insert($document + [
                'version' => $version,
                'is_provisional' => 1,
                'is_active' => 1,
                'effective_at' => $now,
                'created_at' => $now,
            ]);
        }
    }
}
