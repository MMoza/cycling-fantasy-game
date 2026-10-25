<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin;

use App\Application\DTOs\View\AdminDashboardPageDTO;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;

class ShowAdminDashboardUseCase
{
    public function execute(): AdminDashboardPageDTO
    {
        return new AdminDashboardPageDTO(
            competitionsCount: CompetitionModel::count(),
            editionsCount: EditionModel::count(),
            stagesCount: StageModel::count(),
            usersCount: User::count(),
            activeCompetitions: CompetitionModel::where('active', true)->count(),
        );
    }
}
