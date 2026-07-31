<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class ExtendAdministrativeWorkflow extends Migration
{
    public function up(): void
    {
        if (! $this->columnExists('document_id')) {
            $this->forge->addColumn('admin_comments', [
                'document_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'application_id',
                ],
            ]);
        }
        if (! $this->indexExists('idx_admin_comments_document')) {
            $this->db->query('ALTER TABLE admin_comments ADD INDEX idx_admin_comments_document (document_id)');
        }
        if (! $this->foreignKeyExists('fk_admin_comments_document')) {
            $this->db->query(
                'ALTER TABLE admin_comments
                 ADD CONSTRAINT fk_admin_comments_document
                    FOREIGN KEY (document_id) REFERENCES documents(id)
                    ON DELETE SET NULL ON UPDATE CASCADE',
            );
        }
    }

    public function down(): void
    {
        if ($this->columnExists('document_id')) {
            if ($this->foreignKeyExists('fk_admin_comments_document')) {
                $this->db->query('ALTER TABLE admin_comments DROP FOREIGN KEY fk_admin_comments_document');
            }
            if ($this->indexExists('idx_admin_comments_document')) {
                $this->db->query('ALTER TABLE admin_comments DROP INDEX idx_admin_comments_document');
            }
            $this->forge->dropColumn('admin_comments', 'document_id');
        }
    }

    private function foreignKeyExists(string $name): bool
    {
        return $this->db->query(
            'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = "FOREIGN KEY" LIMIT 1',
            ['admin_comments', $name],
        )->getRowArray() !== null;
    }

    private function columnExists(string $name): bool
    {
        return $this->db->query(
            'SELECT 1 FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
            ['admin_comments', $name],
        )->getRowArray() !== null;
    }

    private function indexExists(string $name): bool
    {
        return $this->db->query(
            'SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            ['admin_comments', $name],
        )->getRowArray() !== null;
    }
}
