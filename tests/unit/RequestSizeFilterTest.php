<?php

use App\Filters\RequestSizeFilter;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RequestSizeFilterTest extends CIUnitTestCase
{
    public function testParsesPhpUploadSizeUnits(): void
    {
        $this->assertSame(520 * 1024 * 1024, RequestSizeFilter::parseIniSize('520M'));
        $this->assertSame(2 * 1024 * 1024 * 1024, RequestSizeFilter::parseIniSize('2G'));
        $this->assertSame(1024, RequestSizeFilter::parseIniSize('1K'));
        $this->assertSame(0, RequestSizeFilter::parseIniSize(''));
    }

    public function testRejectsAjaxPostLargerThanPhpPostLimitBeforeCsrf(): void
    {
        $limit = RequestSizeFilter::parseIniSize((string) ini_get('post_max_size'));
        $request = $this->createMock(IncomingRequest::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaderLine')->with('Content-Length')->willReturn((string) ($limit + 1));
        $request->method('isAJAX')->willReturn(true);

        $response = (new RequestSizeFilter())->before($request);

        $this->assertNotNull($response);
        $this->assertSame(413, $response->getStatusCode());
        $payload = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($payload['ok']);
        $this->assertStringContainsString('varias cargas', $payload['message']);
    }
}
