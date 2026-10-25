<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\UseCases\Admin\ShowAdminDashboardUseCase;
use App\Presentation\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ShowAdminDashboardUseCase $showAdminDashboardUseCase,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', $this->showAdminDashboardUseCase->execute()->toArray());
    }
}
