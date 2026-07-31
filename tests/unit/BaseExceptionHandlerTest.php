<?php

use CodeIgniter\Debug\BaseExceptionHandler;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Exceptions;

/**
 * @internal
 */
final class BaseExceptionHandlerTest extends CIUnitTestCase
{
    public function testSensitiveDataMaskingAcceptsTraceLinesWithoutArguments(): void
    {
        $handler = new class(new Exceptions()) extends BaseExceptionHandler {
            public function handle(
                Throwable $exception,
                RequestInterface $request,
                ResponseInterface $response,
                int $statusCode,
                int $exitCode,
            ): void {
            }

            public function maskTrace(array $trace): array
            {
                return $this->maskSensitiveData($trace, ['password']);
            }
        };

        $trace = [
            ['file' => '/var/www/app.php', 'line' => 10],
            ['args' => [['password' => 'secret']]],
        ];

        $masked = $handler->maskTrace($trace);

        $this->assertArrayNotHasKey('args', $masked[0]);
        $this->assertMatchesRegularExpression('/^\*+$/', $masked[1]['args'][0]['password']);
        $this->assertNotSame('secret', $masked[1]['args'][0]['password']);
    }
}
