<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Application\DTOs\CreateLeagueDTO;
use App\Domain\Entities\League;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Models\User;
use Illuminate\Support\Str;

class CreateLeagueUseCase
{
    public function execute(User $user, CreateLeagueDTO $dto): LeagueModel
    {
        $league = League::create(
            name: $dto->name,
            editionId: $dto->editionId,
            scoringSystemId: $dto->scoringSystemId,
            ownerId: $user->id,
        );

        $leagueModel = LeagueModel::create([
            'id' => $league->id,
            'name' => $league->name,
            'edition_id' => $league->editionId,
            'scoring_system_id' => $league->scoringSystemId,
            'owner_id' => $league->ownerId,
            'invite_code' => $league->inviteCode,
            'max_players' => $dto->maxPlayers,
            'is_public' => $dto->isPublic,
        ]);

        $leagueModel->users()->attach($user->id, [
            'id' => Str::uuid()->toString(),
            'role' => 'owner',
        ]);

        return $leagueModel;
    }
}
