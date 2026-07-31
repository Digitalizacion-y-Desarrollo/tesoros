<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('adminAuth');

        if (! $auth->isConfigured()) {
            return redirect()->route('admin.unavailable');
        }
        if (! $auth->isAuthenticated()) {
            return redirect()->route('admin.login')->with(
                'error',
                $auth->lastError() !== '' ? $auth->lastError() : 'Inicia sesión para continuar.',
            );
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
