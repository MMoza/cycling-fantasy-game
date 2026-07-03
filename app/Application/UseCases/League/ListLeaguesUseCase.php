<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Application\DTOs\LeagueDTO;
use App\Models\User;
use Illuminate\Support\Collection;

class ListLeaguesUseCase
{
    public function execute(User $user): Collection
    {
        return $user->leagues()
            ->with(['edition.competition', 'scoringSystem'])
            ->get()
            ->map(fn ($league) => new LeagueDTO(
                id: $league->id,
                name: $league->name,
                editionId: $league->edition_id,
                editionName: $league->edition->competition->name,
                editionYear: $league->edition->year,
                scoringSystemId: $league->scoring_system_id,
                scoringSystemName: $league->scoringSystem->name,
                ownerId: $league->owner_id,
                inviteCode: $league->invite_code,
                memberCount: $league->users()->count(),
                isOfficial: $league->is_official,
                isPublic: $league->is_public,
            ));
    }
}
