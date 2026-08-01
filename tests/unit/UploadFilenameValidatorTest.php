<?php

use App\Services\UploadFilenameValidator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class UploadFilenameValidatorTest extends CIUnitTestCase
{
    public function testAcceptsDescriptiveNamesWithSeveralDots(): void
    {
        $validator = new UploadFilenameValidator();

        $this->assertSame('pdf', $validator->assertSafe('identificacion.juan.perez.pdf', ['pdf', 'jpg', 'jpeg']));
        $this->assertSame('jpg', $validator->assertSafe('foto.restaurante.2026.jpg', ['pdf', 'jpg', 'jpeg']));
        $this->assertSame('mp4', $validator->assertSafe('video.presentacion.final.mp4', ['mp4']));
    }

    public function testRejectsDangerousIntermediateExtension(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('extensión intermedia peligrosa');

        (new UploadFilenameValidator())->assertSafe('identificacion.php.pdf', ['pdf']);
    }

    public function testRejectsUnapprovedFinalExtension(): void
    {
        $this->expectException(\DomainException::class);

        (new UploadFilenameValidator())->assertSafe('documento.pdf.exe', ['pdf']);
    }
}
