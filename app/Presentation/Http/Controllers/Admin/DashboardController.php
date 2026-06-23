<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;
use App\Presentation\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'competitions_count' => CompetitionModel::count(),
            'editions_count' => EditionModel::count(),
            'stages_count' => StageModel::count(),
            'users_count' => User::count(),
            'active_competitions' => CompetitionModel::where('active', true)->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
