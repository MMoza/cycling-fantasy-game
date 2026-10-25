<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Application\DTOs\AdaptOneWeekScoringResultDTO;
use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;

/**
 * Adapta las ligas de las vueltas de una semana de una temporada al sistema
 * OneWeek (solo clasificación general Top 3), reemplazando el sistema Standard
 * que se asignaba por defecto antes de ese cambio.
 *
 * No recalcula puntos: tras el cambio hay que ejecutar
 * `race:rebuild-scores {league_id}` para dejarlos coherentes.
 */
class AdaptOneWeekScoringUseCase
{
    public function execute(int $year, bool $all = false, bool $dryRun = false): AdaptOneWeekScoringResultDTO
    {
        $oneWeek = ScoringSystemModel::where('type', ScoringSystemType::OneWeek->value)->first();

        if ($oneWeek === null) {
            throw new ApplicationException('No existe el sistema de puntuación "Carrera de una semana" (OneWeek).');
        }

        $leagues = LeagueModel::with(['edition.competition', 'scoringSystem'])
            ->whereHas('edition', fn ($q) => $q->where('year', $year))
            ->whereHas('edition.competition', fn ($q) => $q->where('type', CompetitionType::Major))
            ->orderBy('name')
            ->get();

        $updated = [];
        $skipped = [];
        $alreadyOneWeek = [];

        foreach ($leagues as $league) {
            $currentSystem = $league->scoringSystem;

            if ($league->scoring_system_id === $oneWeek->id) {
                $alreadyOneWeek[] = ['id' => $league->id, 'league' => $league->name];

                continue;
            }

            if (! $all && $currentSystem?->type !== ScoringSystemType::Standard) {
                $skipped[] = [
                    'id' => $league->id,
                    'league' => $league->name,
                    'system' => $currentSystem?->name ?? '—',
                ];

                continue;
            }

            if (! $dryRun) {
                $league->update(['scoring_system_id' => $oneWeek->id]);
            }

            $updated[] = [
                'id' => $league->id,
                'league' => $league->name,
                'from' => $currentSystem?->name ?? '—',
                'to' => $oneWeek->name,
            ];
        }

        return new AdaptOneWeekScoringResultDTO(
            year: $year,
            dryRun: $dryRun,
            updated: $updated,
            skipped: $skipped,
            alreadyOneWeek: $alreadyOneWeek,
        );
    }
}
