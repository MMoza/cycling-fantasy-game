<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Dashboard\ShowDashboardUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ShowDashboardUseCase $showDashboardUseCase,
    ) {}

    public function index(Request $request)
    {
        $data = $this->showDashboardUseCase->execute($request->user());

        if (! $data['league']) {
            return Inertia::render('Dashboard/Index', [
                'league' => null,
                'stage' => null,
            ]);
        }

        return Inertia::render('Dashboard/Index', [
            'league' => $data['league'],
            'stage' => $data['stage'],
        ]);
    }
}
