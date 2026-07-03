<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Application\DTOs\CreateLeagueDTO;
use App\Application\Exceptions\ApplicationException;
use App\Domain\Entities\League;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Domain\ValueObjects\UserPlan;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Models\User;
use Illuminate\Support\Str;

class CreateLeagueUseCase
{
    public function execute(User $user, CreateLeagueDTO $dto): LeagueModel
    {
        if ($user->plan === UserPlan::Free && ! $user->is_admin) {
            throw new ApplicationException('Necesitas una suscripción para crear ligas.');
        }

        $scoringSystemId = $dto->scoringSystemId;
        $isOfficial = false;
        $isPublic = $dto->isPublic;

        if ($user->is_admin && $dto->isOfficial) {
            $isOfficial = true;
            $isPublic = true;

            $conservative = ScoringSystemModel::where('type', ScoringSystemType::Conservative)->first();
            if ($conservative === null) {
                throw new ApplicationException('No se encontró el sistema de puntuación Conservador.');
            }
            $scoringSystemId = $conservative->id;
        }

        $league = League::create(
            name: $dto->name,
            editionId: $dto->editionId,
            scoringSystemId: $scoringSystemId,
            ownerId: $user->id,
            isOfficial: $isOfficial,
        );

        $leagueModel = LeagueModel::create([
            'id' => $league->id,
            'name' => $league->name,
            'edition_id' => $league->editionId,
            'scoring_system_id' => $league->scoringSystemId,
            'owner_id' => $league->ownerId,
            'invite_code' => $league->inviteCode,
            'is_official' => $league->isOfficial,
            'is_public' => $isPublic,
        ]);

        $leagueModel->users()->attach($user->id, [
            'id' => Str::uuid()->toString(),
            'role' => 'owner',
        ]);

        return $leagueModel;
    }
}
