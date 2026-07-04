<?php

declare(strict_types=1);

namespace App\Application\UseCases\Dashboard;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Models\User;

class ShowDashboardUseCase
{
    public function execute(User $user): ?string
    {
        if ($user->last_visited_league_id) {
            $league = LeagueModel::find($user->last_visited_league_id);

            if ($league && $user->leagues()->where('leagues.id', $league->id)->exists()) {
                return $league->id;
            }
        }

        $firstLeague = $user->leagues()->first();

        return $firstLeague?->id;
    }
}
