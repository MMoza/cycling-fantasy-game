<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Application\Exceptions\ApplicationException;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Models\User;
use Illuminate\Support\Str;

class JoinLeagueUseCase
{
    public function execute(User $user, string $inviteCode): LeagueModel
    {
        $league = LeagueModel::where('invite_code', $inviteCode)->firstOrFail();

        if (! $user->leagues()->where('leagues.id', $league->id)->exists()) {
            $plan = $user->plan;

            $leagueCount = $user->leagues()->count();
            if ($leagueCount >= $plan->maxLeagues()) {
                throw new ApplicationException(
                    "Has alcanzado el límite de {$plan->maxLeagues()} ligas de tu plan {$plan->label()}."
                );
            }

            $memberCount = $league->users()->count();
            if ($memberCount >= $league->max_players) {
                throw new ApplicationException('La liga ha alcanzado el máximo de participantes.');
            }

            $league->users()->attach($user->id, [
                'id' => Str::uuid()->toString(),
                'role' => 'member',
            ]);
        }

        return $league;
    }
}
