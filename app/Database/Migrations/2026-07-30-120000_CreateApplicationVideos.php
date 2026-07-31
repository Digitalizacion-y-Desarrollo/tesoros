<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateApplicationVideos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'source_type' => ['type' => 'VARCHAR', 'constraint' => 10],
            'private_path' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'external_url' => ['type' => 'VARCHAR', 'constraint' => 2048, 'null' => true],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'size_bytes' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'sha256' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('application_id', 'uq_application_videos_application');
        $this->forge->addKey(['source_type', 'created_at'], false, false, 'idx_application_videos_source');
        $this->forge->addForeignKey(
            'application_id',
            'applications',
            'id',
            'CASCADE',
            'CASCADE',
            'fk_application_videos_application',
        );
        $this->forge->createTable('application_videos', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('application_videos', true);
    }
}
