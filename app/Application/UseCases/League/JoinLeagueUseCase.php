<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Models\User;
use Illuminate\Support\Str;

class JoinLeagueUseCase
{
    public function execute(User $user, string $inviteCode): LeagueModel
    {
        $league = LeagueModel::where('invite_code', $inviteCode)->firstOrFail();

        if (! $user->leagues()->where('leagues.id', $league->id)->exists()) {
            $league->users()->attach($user->id, [
                'id' => Str::uuid()->toString(),
                'role' => 'member',
            ]);
        }

        return $league;
    }
}
