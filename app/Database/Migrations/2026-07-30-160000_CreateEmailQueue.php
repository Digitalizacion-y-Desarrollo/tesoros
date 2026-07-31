<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateEmailQueue extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'application_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'event' => ['type' => 'VARCHAR', 'constraint' => 60],
            'template' => ['type' => 'VARCHAR', 'constraint' => 80],
            'recipient_email' => ['type' => 'VARCHAR', 'constraint' => 254],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 180],
            'payload' => ['type' => 'JSON', 'null' => true],
            'idempotency_key' => ['type' => 'CHAR', 'constraint' => 64],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'attempts' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'max_attempts' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 5],
            'available_at' => ['type' => 'DATETIME'],
            'locked_at' => ['type' => 'DATETIME', 'null' => true],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'last_error' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('idempotency_key', 'uq_email_queue_idempotency');
        $this->forge->addKey(['status', 'available_at'], false, false, 'idx_email_queue_dispatch');
        $this->forge->addKey(['application_id', 'created_at'], false, false, 'idx_email_queue_application');
        $this->forge->addForeignKey(
            'application_id',
            'applications',
            'id',
            'SET NULL',
            'CASCADE',
            'fk_email_queue_application',
        );
        $this->forge->createTable('email_queue', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('email_queue', true);
    }
}
