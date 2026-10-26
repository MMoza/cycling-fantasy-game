<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\View\UserBadgesViewDTO;
use App\Domain\Entities\BadgeDefinition;
use App\Domain\Services\BadgeCatalog;
use App\Domain\ValueObjects\BadgeCode;
use App\Domain\ValueObjects\BadgeMode;
use App\Infrastructure\Persistence\Models\BadgeEventModel;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\UserBadgeModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Motor de cálculo de badges: recalcula desde las fuentes (predicciones,
 * resultados, clasificaciones finales) y persiste niveles e histórico de
 * hitos. Idempotente: re-ejecutar no duplica eventos ni baja niveles.
 */
class BadgeService
{
    /**
     * Competiciones de las Grandes Vueltas y del Mundial (slug PCS + alias).
     *
     * @var array<string, array{slug: string, names: array<int, string>, code: BadgeCode}>
     */
    private const SPECIAL_COMPETITIONS = [
        'tour' => ['slug' => 'tour-de-france', 'names' => ['Tour de France', 'Tour de Francia'], 'code' => BadgeCode::TourWinner],
        'giro' => ['slug' => 'giro-d-italia', 'names' => ['Giro d\'Italia', 'Giro de Italia'], 'code' => BadgeCode::GiroWinner],
        'vuelta' => ['slug' => 'vuelta-a-espana', 'names' => ['Vuelta a España', 'La Vuelta', 'Vuelta de España'], 'code' => BadgeCode::VueltaWinner],
        'worlds' => ['slug' => 'world-championship', 'names' => ['World Championships', 'World Championship'], 'code' => BadgeCode::WorldChampion],
    ];

    public function __construct(private readonly PredictionHitService $predictionHits) {}

    /**
     * Recalcula todos los badges de un usuario.
     *
     * @return array{earned: array<int, string>, progressed: array<int, string>}
     */
    public function recalculateForUser(User $user, bool $dryRun = false): array
    {
        $values = $this->computeValues($user);
        $earned = [];
        $progressed = [];

        foreach (BadgeCatalog::definitions() as $definition) {
            $result = $this->syncBadge($user, $definition, (int) ($values[$definition->code->value] ?? 0), $dryRun);

            if ($result === 'earned') {
                $earned[] = $definition->code->value;
            } elseif ($result === 'progressed') {
                $progressed[] = $definition->code->value;
            }
        }

        return ['earned' => $earned, 'progressed' => $progressed];
    }

    /**
     * Recalcula los badges de todos los usuarios (o de uno concreto).
     *
     * @return array<string, array{earned: array<int, string>, progressed: array<int, string>}>
     */
    public function recalculate(?int $year = null, ?string $userId = null, bool $dryRun = false): array
    {
        $query = User::query();

        if ($userId !== null) {
            $query->where('id', $userId);
        }

        if ($year !== null) {
            $query->whereIn('id', function ($sub) use ($year) {
                $sub->select('predictions.user_id')
                    ->from('predictions')
                    ->join('leagues', 'leagues.id', '=', 'predictions.league_id')
                    ->join('editions', 'editions.id', '=', 'leagues.edition_id')
                    ->where('editions.year', $year)
                    ->distinct();
            });
        }

        $report = [];

        foreach ($query->orderBy('name')->get() as $user) {
            $report[$user->id] = $this->recalculateForUser($user, $dryRun);
        }

        return $report;
    }

    /**
     * Recalcula los badges de todos los miembros de las ligas indicadas
     * (tras puntuar una etapa o cerrar una competición).
     *
     * @param  iterable<int, string>  $leagueIds
     * @return array<string, array{earned: array<int, string>, progressed: array<int, string>}>
     */
    public function recalculateForLeagues(iterable $leagueIds, bool $dryRun = false): array
    {
        $leagueIds = is_array($leagueIds) ? $leagueIds : iterator_to_array($leagueIds, false);

        if ($leagueIds === []) {
            return [];
        }

        $userIds = DB::table('league_user')
            ->whereIn('league_id', $leagueIds)
            ->distinct()
            ->pluck('user_id');

        $report = [];

        foreach ($userIds as $userId) {
            $user = User::query()->find($userId);

            if ($user === null) {
                continue;
            }

            $report[$userId] = $this->recalculateForUser($user, $dryRun);
        }

        return $report;
    }

    /**
     * Badges ganados/activo de varios usuarios para mostrarlos en perfiles y
     * clasificaciones.
     *
     * @param  array<int, string>  $userIds
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function badgesForUsers(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $definitions = BadgeCatalog::definitions();

        return UserBadgeModel::whereIn('user_id', $userIds)
            ->where('level', '>', 0)
            ->orderBy('awarded_at')
            ->get()
            ->groupBy('user_id')
            ->map(function ($badges) use ($definitions) {
                return $badges->map(function (UserBadgeModel $badge) use ($definitions) {
                    $definition = $definitions[$badge->badge_code];

                    return [
                        'code' => $badge->badge_code,
                        'name' => $definition->name,
                        'description' => $definition->description,
                        'category' => $definition->category,
                        'mode' => $definition->mode->value,
                        'svg' => $definition->svg(),
                        'level' => $badge->level,
                        'progress' => $badge->progress,
                        'target' => $definition->target(),
                        'level_label' => $this->levelLabel($definition, $badge->level),
                        'awarded_at' => $badge->awarded_at?->toISOString(),
                    ];
                })->values()->all();
            })
            ->all();
    }

    /**
     * Estado completo de un usuario: badges ganados + progreso de los
     * pendientes (para la sección "Mis badges").
     */
    public function badgeStatusForUser(User $user): UserBadgesViewDTO
    {
        $rows = $this->badgesForUsers([$user->id])[$user->id] ?? [];
        $earnedCodes = array_column($rows, 'code');

        $values = $this->computeValues($user);
        $pending = [];

        foreach (BadgeCatalog::definitions() as $definition) {
            if (in_array($definition->code->value, $earnedCodes, true)) {
                continue;
            }

            $value = (int) ($values[$definition->code->value] ?? 0);
            $next = $definition->nextLevel($value);

            $pending[] = [
                'code' => $definition->code->value,
                'name' => $definition->name,
                'description' => $definition->description,
                'category' => $definition->category,
                'mode' => $definition->mode->value,
                'svg' => $definition->svg(),
                'progress' => $value,
                'target' => $next ?? $definition->target(),
                'level_label' => null,
            ];
        }

        return new UserBadgesViewDTO(earned: $rows, pending: $pending);
    }

    /**
     * Contadores fuente de todos los badges del usuario.
     *
     * @return array<string, int>
     */
    private function computeValues(User $user): array
    {
        $hits = $this->winningStageHits($user);

        $values = [
            BadgeCode::StagesPlayed->value => $this->stagesPlayed($user),
            BadgeCode::StagesWon->value => $hits['stage_count'],
            BadgeCode::GrandTourParticipation->value => $this->participationByType($user, 'gc'),
            BadgeCode::MonumentParticipation->value => $this->participationByType($user, 'monument'),
            BadgeCode::ChampionshipParticipation->value => $this->participationByType($user, 'championship'),
            BadgeCode::ClassicParticipation->value => $this->participationByType($user, 'classic'),
            BadgeCode::MonumentWinner->value => $hits['monument_wins'],
            BadgeCode::WorldChampion->value => $hits['world_champion'],
            BadgeCode::MonumentCollection->value => $hits['monument_wins'],
            BadgeCode::SeasonChampion->value => $this->seasonChampionYears($user),
            BadgeCode::Tester->value => $this->firstSeasonParticipant($user),
        ];

        $gtWins = 0;

        foreach (self::SPECIAL_COMPETITIONS as $key => $special) {
            if ($key === 'worlds') {
                continue;
            }

            $wins = $this->gtWins($user, $special['slug'], $special['names']);
            $values[$special['code']->value] = $wins;
            $gtWins += $wins;
        }

        // Trilogía = número de las 3 Grandes Vueltas ganadas (objetivo 3)
        $values[BadgeCode::Trilogy->value] = $gtWins;

        return $values;
    }

    /**
     * Etapas con al menos un pronóstico del usuario y ya disputadas.
     */
    private function stagesPlayed(User $user): int
    {
        return DB::table('predictions')
            ->join('stages', 'stages.id', '=', 'predictions.stage_id')
            ->where('predictions.user_id', $user->id)
            ->where('stages.status', '!=', 'upcoming')
            ->distinct()
            ->count('predictions.stage_id');
    }

    /**
     * Aciertos de ganador de etapa del usuario en todas sus ligas, con el
     * contexto de competición para derivar monumentos y mundial.
     *
     * @return array{stage_count: int, monument_wins: int, world_champion: int}
     */
    private function winningStageHits(User $user): array
    {
        $stageIds = $this->predictionHits
            ->correctWinnerPredictions(null, $user->id)
            ->pluck('stage_id')
            ->unique()
            ->values();

        if ($stageIds->isEmpty()) {
            return ['stage_count' => 0, 'monument_wins' => 0, 'world_champion' => 0];
        }

        $stages = StageModel::with('edition.competition')
            ->whereIn('id', $stageIds)
            ->get();

        $monumentWins = 0;
        $worldChampion = 0;

        foreach ($stages->groupBy(fn (StageModel $stage) => $stage->edition->competition_id) as $competitionStages) {
            $competition = $competitionStages->first()->edition->competition;

            if ($competition->type->value === 'monument') {
                $monumentWins++;
            }

            if ($this->isSpecialCompetition($competition, 'worlds')) {
                $worldChampion = 1;
            }
        }

        return [
            'stage_count' => $stages->count(),
            'monument_wins' => $monumentWins,
            'world_champion' => $worldChampion,
        ];
    }

    /**
     * Ediciones distintas del tipo con algún pronóstico del usuario.
     */
    private function participationByType(User $user, string $type): int
    {
        return DB::table('predictions')
            ->join('leagues', 'leagues.id', '=', 'predictions.league_id')
            ->join('editions', 'editions.id', '=', 'leagues.edition_id')
            ->join('competitions', 'competitions.id', '=', 'editions.competition_id')
            ->where('predictions.user_id', $user->id)
            ->where('competitions.type', $type)
            ->distinct()
            ->count('editions.id');
    }

    /**
     * Victorias en la clasificación general de una Gran Vuelta concreta:
     * la predicción pre-race gc_top_5 acierta al 1º de la final.
     */
    private function gtWins(User $user, string $slug, array $names): int
    {
        $competition = CompetitionModel::query()
            ->where('pcs_slug', $slug)
            ->orWhereIn('name', $names)
            ->first();

        if ($competition === null) {
            return 0;
        }

        return DB::table('predictions')
            ->join('leagues', 'leagues.id', '=', 'predictions.league_id')
            ->join('editions', 'editions.id', '=', 'leagues.edition_id')
            ->join('final_classifications as fc', function ($join) {
                $join->on('fc.edition_id', '=', 'editions.id')
                    ->where('fc.category', 'gc_top_5')
                    ->where('fc.position', 1);
            })
            ->where('predictions.user_id', $user->id)
            ->where('predictions.category', 'gc_top_5')
            ->where('editions.competition_id', $competition->id)
            ->whereRaw("JSON_EXTRACT(predictions.prediction_value, '$.rider_ids[0]') = fc.rider_id")
            ->distinct()
            ->count('editions.id');
    }

    /**
     * Temporadas terminadas primero en la clasificación general oficial.
     */
    private function seasonChampionYears(User $user): int
    {
        $totals = DB::table('score_events')
            ->join('leagues', 'leagues.id', '=', 'score_events.league_id')
            ->join('editions', 'editions.id', '=', 'leagues.edition_id')
            ->where('leagues.is_official', true)
            ->groupBy('editions.year', 'score_events.user_id')
            ->selectRaw('editions.year as year, score_events.user_id as user_id, SUM(score_events.points) as total')
            ->get();

        $wins = 0;

        foreach ($totals->groupBy('year') as $yearTotals) {
            $pointsByUser = [];

            foreach ($yearTotals as $row) {
                $pointsByUser[$row->user_id] = (int) $row->total;
            }

            $ranked = LeaderboardService::rankPoints($pointsByUser);

            if (($ranked[0]['user_id'] ?? null) === $user->id) {
                $wins++;
            }
        }

        return $wins;
    }

    /**
     * Participó en la primera temporada del juego (año de la edición más
     * antigua registrada).
     */
    private function firstSeasonParticipant(User $user): int
    {
        $firstYear = DB::table('editions')->min('year');

        if ($firstYear === null) {
            return 0;
        }

        $participated = DB::table('predictions')
            ->join('leagues', 'leagues.id', '=', 'predictions.league_id')
            ->join('editions', 'editions.id', '=', 'leagues.edition_id')
            ->where('predictions.user_id', $user->id)
            ->where('editions.year', (int) $firstYear)
            ->exists();

        return $participated ? 1 : 0;
    }

    private function isSpecialCompetition(CompetitionModel $competition, string $key): bool
    {
        $special = self::SPECIAL_COMPETITIONS[$key];

        return $competition->pcs_slug === $special['slug']
            || in_array($competition->name, $special['names'], true);
    }

    /**
     * @return 'earned'|'progressed'|null
     */
    private function syncBadge(User $user, BadgeDefinition $definition, int $value, bool $dryRun): ?string
    {
        $level = $definition->mode === BadgeMode::Once
            ? ($value >= $definition->target() ? 1 : 0)
            : $definition->levelFor($value);

        $badge = UserBadgeModel::firstOrNew([
            'user_id' => $user->id,
            'badge_code' => $definition->code->value,
        ]);

        $oldLevel = (int) ($badge->level ?? 0);
        $oldProgress = (int) ($badge->progress ?? 0);

        if ($level === $oldLevel && $value === $oldProgress) {
            return null;
        }

        if ($dryRun) {
            return $level > $oldLevel ? 'earned' : 'progressed';
        }

        $badge->level = max($oldLevel, $level);
        $badge->progress = max($oldProgress, $value);

        if ($badge->awarded_at === null && $badge->level > 0) {
            $badge->awarded_at = now();
        }

        $badge->save();

        if ($value > $oldProgress) {
            $this->recordEvent($user, $definition, 'progress', $badge->level, $badge->progress);
        }

        if ($badge->level > $oldLevel) {
            $this->recordEvent($user, $definition, 'level', $badge->level, $badge->progress);
        }

        return $badge->level > $oldLevel ? 'earned' : 'progressed';
    }

    private function recordEvent(User $user, BadgeDefinition $definition, string $event, int $level, int $progress): void
    {
        BadgeEventModel::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'badge_code' => $definition->code->value,
            'event' => $event,
            'level' => $level,
            'progress' => $progress,
            'context' => null,
            'created_at' => now(),
        ]);
    }

    private function levelLabel(BadgeDefinition $definition, int $level): string
    {
        if ($definition->mode === BadgeMode::Once) {
            return $definition->name;
        }

        $index = array_search($level, $definition->levels, true);

        return $index === false ? '' : 'Nivel '.($index + 1);
    }
}
