<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApplicationDomain extends Migration
{
    public function up(): void
    {
        if ($this->db->DBDriver === 'MySQLi') {
            $this->db->query('ALTER TABLE categories ENGINE = InnoDB');
        }

        $this->createFolioCounters();
        $this->createApplications();
        $this->createParticipants();
        $this->createCategoryProfiles();
        $this->createApplicationHistories();
    }

    public function down(): void
    {
        $this->forge->dropTable('application_histories', true);
        $this->forge->dropTable('beverage_profiles', true);
        $this->forge->dropTable('student_team_profiles', true);
        $this->forge->dropTable('restaurant_profiles', true);
        $this->forge->dropTable('cook_profiles', true);
        $this->forge->dropTable('participants', true);
        $this->forge->dropTable('applications', true);
        $this->forge->dropTable('folio_counters', true);
    }

    private function createFolioCounters(): void
    {
        $this->forge->addField([
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'last_sequence' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('category_id', true);
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'RESTRICT', 'fk_folio_counters_category');
        $this->forge->createTable('folio_counters', true, ['ENGINE' => 'InnoDB']);
    }

    private function createApplications(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'folio' => [
                'type'       => 'CHAR',
                'constraint' => 18,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 254,
            ],
            'email_hash' => [
                'type'       => 'CHAR',
                'constraint' => 64,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'borrador',
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'cancelled_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('folio', 'uq_applications_folio');
        $this->forge->addUniqueKey('email_hash', 'uq_applications_email_hash');
        $this->forge->addKey(['category_id', 'status'], false, false, 'idx_applications_category_status');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'RESTRICT', 'RESTRICT', 'fk_applications_category');
        $this->forge->createTable('applications', true, ['ENGINE' => 'InnoDB']);
    }

    private function createParticipants(): void
    {
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
            'member_number' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'responsable',
            ],
            'is_primary' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'curp' => [
                'type'       => 'CHAR',
                'constraint' => 18,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'second_last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('curp', 'uq_participants_curp');
        $this->forge->addUniqueKey(['application_id', 'member_number'], 'uq_participants_application_member');
        $this->forge->addKey(['application_id', 'is_primary'], false, false, 'idx_participants_application_primary');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE', 'fk_participants_application');
        $this->forge->createTable('participants', true, ['ENGINE' => 'InnoDB']);
    }

    private function createCategoryProfiles(): void
    {
        $commonFields = [
            'application_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'municipality' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'form_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        $this->forge->addField($commonFields + [
            'years_experience' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true,
            ],
            'signature_dish' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
        ]);
        $this->addProfileKeys('cook_profiles', 'fk_cook_profiles_application');

        $this->forge->addField($commonFields + [
            'business_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'legal_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'culinary_concept' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->addProfileKeys('restaurant_profiles', 'fk_restaurant_profiles_application');

        $this->forge->addField($commonFields + [
            'institution_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 220,
                'null'       => true,
            ],
            'campus' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'proposal_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
        ]);
        $this->addProfileKeys('student_team_profiles', 'fk_student_profiles_application');

        $this->forge->addField($commonFields + [
            'project_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'beverage_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'beverage_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
        ]);
        $this->addProfileKeys('beverage_profiles', 'fk_beverage_profiles_application');
    }

    private function addProfileKeys(string $table, string $foreignKeyName): void
    {
        $this->forge->addKey('application_id', true);
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE', $foreignKeyName);
        $this->forge->createTable($table, true, ['ENGINE' => 'InnoDB']);
    }

    private function createApplicationHistories(): void
    {
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
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
            ],
            'from_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'to_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'actor_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'system',
            ],
            'actor_reference' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['application_id', 'created_at'], false, false, 'idx_application_histories_timeline');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE', 'fk_histories_application');
        $this->forge->createTable('application_histories', true, ['ENGINE' => 'InnoDB']);
    }
}
