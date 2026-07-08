<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\UserPlan;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Models\User;
use Illuminate\Support\Str;

class JoinLeagueUseCase
{
    private const MAX_PLAYERS_NON_OFFICIAL = 20;

    public function execute(User $user, string $inviteCode): LeagueModel
    {
        $league = LeagueModel::where('invite_code', $inviteCode)->firstOrFail();

        return $this->joinLeague($user, $league);
    }

    public function executeById(User $user, string $leagueId): LeagueModel
    {
        $league = LeagueModel::findOrFail($leagueId);

        if (! $league->is_official) {
            throw new ApplicationException('Solo puedes unirte directamente a ligas oficiales.');
        }

        return $this->joinLeague($user, $league);
    }

    private function joinLeague(User $user, LeagueModel $league): LeagueModel
    {
        if (! $user->leagues()->where('leagues.id', $league->id)->exists()) {
            $plan = $user->plan;

            if ($plan === UserPlan::Free && ! $league->is_official) {
                throw new ApplicationException(
                    'Necesitas una suscripción para unirte a ligas que no son oficiales.'
                );
            }

            $leagueCount = $user->leagues()->count();
            if ($leagueCount >= $plan->maxLeagues()) {
                throw new ApplicationException(
                    "Has alcanzado el límite de {$plan->maxLeagues()} ligas de tu plan {$plan->label()}."
                );
            }

            if (! $league->is_official) {
                $memberCount = $league->users()->count();
                if ($memberCount >= self::MAX_PLAYERS_NON_OFFICIAL) {
                    throw new ApplicationException('La liga ha alcanzado el máximo de 20 participantes.');
                }
            }

            $league->users()->attach($user->id, [
                'id' => Str::uuid()->toString(),
                'role' => 'member',
            ]);
        }

        return $league;
    }
}
