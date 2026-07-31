<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateParticipantAccess extends Migration
{
    public function up(): void
    {
        $this->createAdminComments();
        $this->createAccessCodes();
        $this->createParticipantSessions();
        $this->createAccessEvents();
    }

    public function down(): void
    {
        $this->forge->dropTable('participant_access_events', true);
        $this->forge->dropTable('participant_sessions', true);
        $this->forge->dropTable('access_codes', true);
        $this->forge->dropTable('admin_comments', true);
    }

    private function createAdminComments(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'comment' => ['type' => 'TEXT'],
            'is_visible_to_participant' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'actor_reference' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['application_id', 'created_at'], false, false, 'idx_admin_comments_application');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE', 'fk_admin_comments_application');
        $this->forge->createTable('admin_comments', true, ['ENGINE' => 'InnoDB']);
    }

    private function createAccessCodes(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'code_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'expires_at' => ['type' => 'DATETIME'],
            'attempts' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'max_attempts' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 5],
            'sent_at' => ['type' => 'DATETIME'],
            'last_attempt_at' => ['type' => 'DATETIME', 'null' => true],
            'used_at' => ['type' => 'DATETIME', 'null' => true],
            'invalidated_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['application_id', 'created_at'], false, false, 'idx_access_codes_application');
        $this->forge->addKey(['expires_at', 'invalidated_at'], false, false, 'idx_access_codes_expiry');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE', 'fk_access_codes_application');
        $this->forge->createTable('access_codes', true, ['ENGINE' => 'InnoDB']);
    }

    private function createParticipantSessions(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'token_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'expires_at' => ['type' => 'DATETIME'],
            'last_activity_at' => ['type' => 'DATETIME'],
            'revoked_at' => ['type' => 'DATETIME', 'null' => true],
            'ip_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'user_agent_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token_hash', 'uq_participant_sessions_token');
        $this->forge->addKey(['application_id', 'expires_at'], false, false, 'idx_participant_sessions_application');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE', 'fk_participant_sessions_application');
        $this->forge->createTable('participant_sessions', true, ['ENGINE' => 'InnoDB']);
    }

    private function createAccessEvents(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'application_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'event' => ['type' => 'VARCHAR', 'constraint' => 60],
            'email_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'folio_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'session_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'metadata' => ['type' => 'JSON', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['event', 'created_at'], false, false, 'idx_access_events_event');
        $this->forge->addKey(['email_hash', 'created_at'], false, false, 'idx_access_events_email');
        $this->forge->addKey(['session_hash', 'created_at'], false, false, 'idx_access_events_session');
        $this->forge->addKey(['ip_address', 'created_at'], false, false, 'idx_access_events_ip');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'SET NULL', 'CASCADE', 'fk_access_events_application');
        $this->forge->createTable('participant_access_events', true, ['ENGINE' => 'InnoDB']);
    }
}
