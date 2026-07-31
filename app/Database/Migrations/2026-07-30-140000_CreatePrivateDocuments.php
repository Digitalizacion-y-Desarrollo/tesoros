<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreatePrivateDocuments extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 80],
            'label' => ['type' => 'VARCHAR', 'constraint' => 255],
            'is_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_locked' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'correction_unlocked' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'active_version_number' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'removed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['application_id', 'document_type'], 'uq_documents_application_type');
        $this->forge->addKey(['application_id', 'is_locked'], false, false, 'idx_documents_application_locked');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE', 'fk_documents_application');
        $this->forge->createTable('documents', true, ['ENGINE' => 'InnoDB']);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'document_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'version_number' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'private_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'size_bytes' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'sha256' => ['type' => 'CHAR', 'constraint' => 64],
            'uploaded_by_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'uploaded_by_reference' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['document_id', 'version_number'], 'uq_document_versions_number');
        $this->forge->addKey(['document_id', 'created_at'], false, false, 'idx_document_versions_created');
        $this->forge->addForeignKey('document_id', 'documents', 'id', 'CASCADE', 'CASCADE', 'fk_document_versions_document');
        $this->forge->createTable('document_versions', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('document_versions', true);
        $this->forge->dropTable('documents', true);
    }
}
