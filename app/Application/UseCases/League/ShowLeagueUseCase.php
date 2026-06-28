<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShowLeagueUseCase
{
    public function execute(User $user, string $leagueId): LeagueModel
    {
        $league = LeagueModel::with(['edition.competition', 'scoringSystem', 'stages', 'users'])
            ->find($leagueId);

        if (! $league || ! $user->leagues()->where('leagues.id', $leagueId)->exists()) {
            throw new ModelNotFoundException('League not found');
        }

        $user->update(['last_visited_league_id' => $leagueId]);

        return $league;
    }
}
