<?php

use App\Services\MunicipalityCatalog;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MunicipalityCatalogTest extends CIUnitTestCase
{
    public function testContainsThe125OfficialMunicipalitiesWithoutDuplicates(): void
    {
        $catalog = new MunicipalityCatalog();
        $municipalities = $catalog->all();

        $this->assertSame(125, $catalog->count());
        $this->assertCount(125, array_unique($municipalities));
        $this->assertContains('Acambay de Ruíz Castañeda', $municipalities);
        $this->assertContains('Toluca', $municipalities);
        $this->assertContains('Tonanitla', $municipalities);
    }

    public function testCanonicalizesCaseAndSurroundingWhitespace(): void
    {
        $catalog = new MunicipalityCatalog();

        $this->assertSame('Naucalpan de Juárez', $catalog->canonicalize('  naucalpan de juárez  '));
        $this->assertNull($catalog->canonicalize('Municipio inventado'));
    }
}
