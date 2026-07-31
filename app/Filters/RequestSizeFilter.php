<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class RequestSizeFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (strtoupper($request->getMethod()) !== 'POST') {
            return null;
        }

        $contentLength = (int) $request->getHeaderLine('Content-Length');
        $postLimit = self::parseIniSize((string) ini_get('post_max_size'));
        if ($contentLength <= 0 || $postLimit <= 0 || $contentLength <= $postLimit) {
            return null;
        }

        $message = 'El tamaño total de la carga excede el límite del servidor. Guarda los archivos en varias cargas.';
        $response = service('response')->setStatusCode(413);
        if ($request instanceof IncomingRequest && $request->isAJAX()) {
            return $response->setJSON([
                'ok' => false,
                'message' => $message,
                'errors' => [],
            ]);
        }

        return $response
            ->setContentType('text/plain', 'UTF-8')
            ->setBody($message);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }

    public static function parseIniSize(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }
}
