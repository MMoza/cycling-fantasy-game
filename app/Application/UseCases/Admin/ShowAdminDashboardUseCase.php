<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;

class ShowAdminDashboardUseCase
{
    public function execute(): array
    {
        return [
            'competitions_count' => CompetitionModel::count(),
            'editions_count' => EditionModel::count(),
            'stages_count' => StageModel::count(),
            'users_count' => User::count(),
            'active_competitions' => CompetitionModel::where('active', true)->count(),
        ];
    }
}
