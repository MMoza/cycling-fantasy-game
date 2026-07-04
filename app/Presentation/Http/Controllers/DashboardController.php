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
        $user = $request->user();

        $leagueId = $this->showDashboardUseCase->execute($user);

        if ($leagueId) {
            return redirect()->route('leagues.show', $leagueId);
        }

        return Inertia::render('Dashboard');
    }
}
