<?php

use App\Services\CurpNormalizer;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CurpNormalizerTest extends CIUnitTestCase
{
    public function testNormalizesUppercaseAndWhitespace(): void
    {
        $normalizer = new CurpNormalizer();

        $this->assertSame('GODE561231HDFBCD09', $normalizer->normalize(' gode561231hdfbcd09 '));
    }

    public function testRejectsInvalidStructure(): void
    {
        $this->expectException(\DomainException::class);

        (new CurpNormalizer())->normalize('CURP-INVALIDA');
    }

    public function testCanonicalizesWithoutValidatingForDuplicateLookup(): void
    {
        $normalizer = new CurpNormalizer();

        $this->assertSame('CURPINVALIDA000000', $normalizer->canonicalize(' curp invalida 000000 '));
    }
}
