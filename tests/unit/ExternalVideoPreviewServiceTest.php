<?php

use App\Services\ExternalVideoPreviewService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ExternalVideoPreviewServiceTest extends CIUnitTestCase
{
    public function testBuildsPrivacyEnhancedYouTubePreview(): void
    {
        $preview = (new ExternalVideoPreviewService())
            ->describe('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertSame('embed', $preview['kind']);
        $this->assertSame('YouTube', $preview['provider']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $preview['embed_url']);
    }

    public function testBuildsVimeoPreview(): void
    {
        $preview = (new ExternalVideoPreviewService())
            ->describe('https://vimeo.com/76979871');

        $this->assertSame('embed', $preview['kind']);
        $this->assertSame('https://player.vimeo.com/video/76979871', $preview['embed_url']);
    }

    public function testUnknownHttpsProviderUsesSafeExternalFallback(): void
    {
        $preview = (new ExternalVideoPreviewService())
            ->describe('https://videos.example.test/presentacion');

        $this->assertSame('external', $preview['kind']);
        $this->assertNull($preview['embed_url']);
        $this->assertSame('videos.example.test', $preview['host']);
    }

    public function testRejectsNonHttpsPreview(): void
    {
        $preview = (new ExternalVideoPreviewService())
            ->describe('http://videos.example.test/presentacion');

        $this->assertSame('unavailable', $preview['kind']);
        $this->assertSame('', $preview['url']);
    }
}
