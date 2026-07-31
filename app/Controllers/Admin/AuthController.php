<?php

namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Services\AuditLogService;

final class AuthController extends AdminController
{
    public function index()
    {
        $auth = service('adminAuth');
        if (! $auth->isConfigured()) {
            return redirect()->route('admin.unavailable');
        }
        if ($auth->isAuthenticated()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin/auth/login', ['title' => 'Acceso administrativo']);
    }

    public function login()
    {
        $auth = service('adminAuth');
        if (! $auth->isConfigured()) {
            return redirect()->route('admin.unavailable');
        }
        $email = (string) $this->request->getPost('email');
        $throttleKey = 'admin-login-' . hash('sha256', $this->request->getIPAddress());
        if (! service('throttler')->check($throttleKey, 10, 900)) {
            $this->audit('admin_login_rate_limited', 'anonymous', null);
            return redirect()->route('admin.login')
                ->withInput()
                ->with('error', 'Se alcanzó el límite temporal de intentos. Inténtalo más tarde.');
        }
        if (! $auth->attempt(
            $email,
            (string) $this->request->getPost('password'),
        )) {
            $this->audit('admin_login_failed', 'anonymous', hash('sha256', strtolower(trim($email))));
            return redirect()->route('admin.login')->withInput()->with('error', $auth->lastError());
        }

        $this->audit('admin_login_succeeded', 'admin', (string) $this->session->get('admin_actor_reference'));
        return redirect()->route('admin.dashboard')->with('message', 'Sesión administrativa iniciada.');
    }

    public function logout()
    {
        $actor = (string) $this->session->get('admin_actor_reference');
        service('adminAuth')->logout();
        $this->audit('admin_logout', 'admin', $actor);
        return redirect()->route('admin.login')->with('message', 'La sesión administrativa se cerró correctamente.');
    }

    public function forgot(): string
    {
        return view('admin/auth/forgot', ['title' => 'Recuperar acceso']);
    }

    public function sendRecovery()
    {
        $auth = service('adminAuth');
        $loginUrl = url_to('admin.login');
        $email = (string) $this->request->getPost('email');
        if (! $auth->forgotPassword($email, $loginUrl)) {
            $this->audit('admin_recovery_failed', 'anonymous', hash('sha256', strtolower(trim($email))));
            return redirect()->route('admin.forgot')->withInput()->with('error', $auth->lastError());
        }

        $this->audit('admin_recovery_requested', 'anonymous', hash('sha256', strtolower(trim($email))));
        return redirect()->route('admin.login')->with(
            'message',
            'Si la cuenta es válida, el proveedor institucional enviará las instrucciones de recuperación.',
        );
    }

    private function audit(string $action, string $actorType, ?string $actorReference): void
    {
        (new AuditLogService())->record(
            $actorType,
            $action,
            null,
            $actorReference !== '' ? $actorReference : null,
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString(),
        );
    }
}
