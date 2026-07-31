<?php

use App\Services\CsvExportService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CsvExportServiceTest extends CIUnitTestCase
{
    public function testDangerousSpreadsheetValuesAreNeutralized(): void
    {
        $service = new CsvExportService();

        $this->assertSame("'=2+2", $service->safeCell('=2+2'));
        $this->assertSame("'+SUM(A1:A2)", $service->safeCell('+SUM(A1:A2)'));
        $this->assertSame("'-10", $service->safeCell('-10'));
        $this->assertSame("'@IMPORTDATA(test)", $service->safeCell('@IMPORTDATA(test)'));
        $this->assertSame('TG-2026-CCT-000001', $service->safeCell('TG-2026-CCT-000001'));
    }

    public function testCsvContainsOnlyTheApprovedColumns(): void
    {
        $csv = (new CsvExportService())->generate([[
            'folio' => 'TG-2026-CCT-000001',
            'category_name' => 'Cocineras',
            'status' => 'enviada',
            'municipality' => 'Nezahualcóyotl',
            'email' => 'persona@example.test',
            'curp' => 'GODE561231HDFBCD09',
            'first_name' => 'Persona',
            'last_name' => 'Prueba',
            'private_path' => 'never/export/this.pdf',
            'access_token' => 'secret',
        ]]);

        $this->assertStringContainsString('TG-2026-CCT-000001', $csv);
        $this->assertStringNotContainsString('never/export/this.pdf', $csv);
        $this->assertStringNotContainsString('secret', $csv);
    }
}
