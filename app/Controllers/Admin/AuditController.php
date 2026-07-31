<?php

namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Services\AuditLogService;

final class AuditController extends AdminController
{
    public function index(): string
    {
        return view('admin/audit', [
            'title' => 'Bitácora',
            'audit' => (new AuditLogService())->listing((int) ($this->request->getGet('page') ?: 1)),
        ]);
    }
}
