<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApplicationFormWorkflow extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('applications', [
            'version' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 1,
                'after'      => 'status',
            ],
        ]);

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'application_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'document_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'document_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'accepted_at' => [
                'type' => 'DATETIME',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(
            ['application_id', 'document_type', 'document_version'],
            'uq_legal_acceptances_application_document',
        );
        $this->forge->addKey(['application_id', 'accepted_at'], false, false, 'idx_legal_acceptances_application');
        $this->forge->addForeignKey(
            'application_id',
            'applications',
            'id',
            'CASCADE',
            'CASCADE',
            'fk_legal_acceptances_application',
        );
        $this->forge->createTable('legal_acceptances', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('legal_acceptances', true);
        $this->forge->dropColumn('applications', 'version');
    }
}
