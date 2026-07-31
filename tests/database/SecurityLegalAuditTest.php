<?php

use App\Services\AuditLogService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class SecurityLegalAuditTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';

    public function testProvisionalLegalDocumentsAreVersionedAndExplicit(): void
    {
        $documents = $this->db->table('legal_documents')->orderBy('document_type')->get()->getResultArray();

        $this->assertCount(4, $documents);
        foreach ($documents as $document) {
            $this->assertSame(1, (int) $document['is_provisional']);
            $this->assertSame(1, (int) $document['is_active']);
            $this->assertStringStartsWith('PROVISIONAL-', (string) $document['version']);
            $this->assertStringContainsString('PROVISIONAL', (string) $document['content']);
        }
    }

    public function testAuditLogDropsSecretsAndKeepsControlledContext(): void
    {
        (new AuditLogService($this->db))->record(
            'admin',
            'security_test',
            null,
            'access-api:7',
            '127.0.0.1',
            'PHPUnit',
            [
                'token' => 'must-not-be-stored',
                'code' => '123456',
                'result' => 'allowed',
            ],
        );

        $row = $this->db->table('audit_log')->where('action', 'security_test')->get()->getRowArray();
        $this->assertNotNull($row);
        $this->assertStringNotContainsString('must-not-be-stored', (string) $row['metadata']);
        $this->assertStringNotContainsString('123456', (string) $row['metadata']);
        $this->assertStringContainsString('allowed', (string) $row['metadata']);
    }
}
