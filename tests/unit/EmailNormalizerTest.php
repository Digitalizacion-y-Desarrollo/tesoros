<?php

use App\Services\EmailNormalizer;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EmailNormalizerTest extends CIUnitTestCase
{
    public function testNormalizesCaseAndWhitespace(): void
    {
        $this->assertSame(
            'participante@example.com',
            (new EmailNormalizer())->normalize(' Participante@Example.COM '),
        );
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->expectException(\DomainException::class);

        (new EmailNormalizer())->normalize('correo-invalido');
    }
}
