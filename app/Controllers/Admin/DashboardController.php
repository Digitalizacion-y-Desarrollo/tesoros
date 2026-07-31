<?php

namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Services\AdminApplicationService;

class DashboardController extends AdminController
{
    public function index(): string
    {
        return view('admin/dashboard', [
            'title' => 'Panel administrativo',
            'dashboard' => (new AdminApplicationService())->dashboard(),
        ]);
    }

    public function unavailable(): string
    {
        return view('admin/unavailable');
    }
}
