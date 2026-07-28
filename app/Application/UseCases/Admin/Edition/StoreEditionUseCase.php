<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Edition;

use App\Application\Exceptions\ApplicationException;
use App\Domain\Entities\League;
use App\Domain\ValueObjects\CompetitionType;
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

        $this->createOfficialLeague($edition, $competition, $data['year'], $adminUserId);

        return $edition;
    }

    private function createOfficialLeague(EditionModel $edition, CompetitionModel $competition, int $year, string $adminUserId): void
    {
        $scoringType = match ($competition->type) {
            CompetitionType::Monument, CompetitionType::Championship, CompetitionType::Classic => ScoringSystemType::OneDay,
            default => ScoringSystemType::Standard,
        };

        $scoringSystem = ScoringSystemModel::where('type', $scoringType)->first();

        if ($scoringSystem === null) {
            throw new ApplicationException("No se encontró el sistema de puntuación {$scoringType->label()}.");
        }

        $league = League::create(
            name: "Liga Oficial {$competition->name} {$year}",
            editionId: $edition->id,
            scoringSystemId: $scoringSystem->id,
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
