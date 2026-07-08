<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Edition;

use App\Application\Exceptions\ApplicationException;
use App\Domain\Entities\League;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use Illuminate\Support\Str;

class StoreEditionUseCase
{
    public function execute(string $competitionId, array $data, string $adminUserId): EditionModel
    {
        $competition = CompetitionModel::findOrFail($competitionId);

        $edition = EditionModel::create([
            'id' => Str::uuid()->toString(),
            'competition_id' => $competitionId,
            'year' => $data['year'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'upcoming',
        ]);

        $this->createOfficialLeague($edition, $competition->name, $data['year'], $adminUserId);

        return $edition;
    }

    private function createOfficialLeague(EditionModel $edition, string $competitionName, int $year, string $adminUserId): void
    {
        $conservative = ScoringSystemModel::where('type', ScoringSystemType::Conservative)->first();

        if ($conservative === null) {
            throw new ApplicationException('No se encontró el sistema de puntuación Conservador.');
        }

        $league = League::create(
            name: "Liga Oficial {$competitionName} {$year}",
            editionId: $edition->id,
            scoringSystemId: $conservative->id,
            ownerId: $adminUserId,
            isOfficial: true,
        );

        $leagueModel = LeagueModel::create([
            'id' => $league->id,
            'name' => $league->name,
            'edition_id' => $league->editionId,
            'scoring_system_id' => $league->scoringSystemId,
            'owner_id' => $league->ownerId,
            'invite_code' => $league->inviteCode,
            'is_official' => true,
            'is_public' => true,
            'max_players' => 0,
        ]);

        $leagueModel->users()->attach($adminUserId, [
            'id' => Str::uuid()->toString(),
            'role' => 'owner',
        ]);
    }
}
