<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\View\BadgeDetailViewDTO;
use App\Application\DTOs\View\UserBadgesViewDTO;
use App\Domain\Entities\BadgeDefinition;
use App\Domain\Services\BadgeCatalog;
use App\Domain\ValueObjects\BadgeCode;
use App\Domain\ValueObjects\BadgeDisplay;
use App\Domain\ValueObjects\BadgeMode;
use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\BadgeEventModel;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\UserBadgeModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
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
        $report = [];

        foreach ($this->userIdsFor($year, $userId) as $id) {
            $user = User::query()->find($id);

            if ($user === null) {
                continue;
            }

            $report[$id] = $this->recalculateForUser($user, $dryRun);
        }

        return $report;
    }

    /**
     * Borra los badges e hitos del alcance y los recalcula desde cero
     * (para rehacerlos con las fechas reales de los logros).
     *
     * @return array<string, array{earned: array<int, string>, progressed: array<int, string>}>
     */
    public function rebuild(?int $year = null, ?string $userId = null, bool $dryRun = false): array
    {
        if (! $dryRun) {
            $ids = $this->userIdsFor($year, $userId);

            UserBadgeModel::whereIn('user_id', $ids)->delete();
            BadgeEventModel::whereIn('user_id', $ids)->delete();
        }

        return $this->recalculate($year, $userId, $dryRun);
    }

    /**
     * @return Collection<int, string>
     */
    private function userIdsFor(?int $year, ?string $userId): Collection
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

        return $query->orderBy('name')->pluck('id');
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
                        'levels' => $definition->levels,
                        'level_index' => $this->levelIndex($definition, $badge->level),
                        'progress' => $badge->progress,
                        'target' => $definition->target(),
                        'level_label' => $this->levelLabel($definition, $badge->level),
                        'special' => $definition->special,
                        'display' => $definition->display->value,
                        'overlay' => $this->overlayValue($definition, $badge->progress, $this->levelIndex($definition, $badge->level)),
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
            // Los badges especiales (TESTER, futuros…) solo se consiguen por
            // eventos concretos: nunca aparecen como pendientes.
            if ($definition->special || in_array($definition->code->value, $earnedCodes, true)) {
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
                'level' => 0,
                'levels' => $definition->levels,
                'level_index' => 0,
                'progress' => $value,
                'target' => $next ?? $definition->target(),
                'level_label' => null,
                'special' => $definition->special,
                'display' => $definition->display->value,
                'overlay' => $this->overlayValue($definition, $value, 0),
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
        $monumentTitles = $this->monumentTitleEditions($user);

        $values = [
            BadgeCode::StagesPlayed->value => $this->stagesPlayed($user),
            BadgeCode::StagesWon->value => $this->wonStageCount($user),
            BadgeCode::GrandTourParticipation->value => $this->participationByType($user, 'gc'),
            BadgeCode::MonumentParticipation->value => $this->participationByType($user, 'monument'),
            BadgeCode::ChampionshipParticipation->value => $this->participationByType($user, 'championship'),
            BadgeCode::ClassicParticipation->value => $this->participationByType($user, 'classic'),
            BadgeCode::MonumentWinner->value => $monumentTitles->count(),
            BadgeCode::WorldChampion->value => $this->titleEditionsFor($user, 'worlds')->count(),
            BadgeCode::MonumentCollection->value => $monumentTitles->unique('competition_id')->count(),
            BadgeCode::SeasonChampion->value => $this->seasonChampionYearList($user)->count(),
            BadgeCode::Tester->value => $this->firstSeasonParticipant($user),
        ];

        $gtTitles = 0;

        foreach (['tour', 'giro', 'vuelta'] as $key) {
            $titles = $this->titleEditionsFor($user, $key)->count();
            $values[self::SPECIAL_COMPETITIONS[$key]['code']->value] = $titles;
            $gtTitles += $titles;
        }

        // Trilogía = número de títulos de las 3 Grandes Vueltas (objetivo 3)
        $values[BadgeCode::Trilogy->value] = $gtTitles;

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
     * Etapas con ganador acertado por el usuario en todas sus ligas.
     */
    private function wonStageCount(User $user): int
    {
        return $this->predictionHits
            ->correctWinnerPredictions(null, $user->id)
            ->pluck('stage_id')
            ->unique()
            ->count();
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
     * Ediciones donde el usuario terminó 1º en la liga oficial (título de
     * liga). Solo cuentan ediciones finalizadas y ligas con puntuación.
     *
     * @return Collection<int, EditionModel>
     */
    private function leagueTitleEditions(User $user): Collection
    {
        $leagueRows = DB::table('leagues')
            ->join('league_user', 'league_user.league_id', '=', 'leagues.id')
            ->join('editions', 'editions.id', '=', 'leagues.edition_id')
            ->where('league_user.user_id', $user->id)
            ->where('leagues.is_official', true)
            ->where('editions.status', EditionStatus::Finished)
            ->select('leagues.id as league_id', 'editions.id as edition_id')
            ->get();

        $wonEditionIds = [];

        foreach ($leagueRows as $row) {
            $champion = LeaderboardService::leagueChampion($row->league_id);

            if ($champion !== null && $champion['user_id'] === $user->id) {
                $wonEditionIds[] = $row->edition_id;
            }
        }

        return EditionModel::with('competition')
            ->whereIn('id', array_unique($wonEditionIds))
            ->orderBy('end_date')
            ->get();
    }

    /**
     * Títulos de liga en las ediciones de una competición concreta.
     *
     * @return Collection<int, EditionModel>
     */
    private function titleEditionsFor(User $user, string $key): Collection
    {
        return $this->leagueTitleEditions($user)
            ->filter(fn (EditionModel $edition) => $this->isSpecialCompetition($edition->competition, $key))
            ->values();
    }

    /**
     * Títulos de liga en ediciones de carreras Monumento.
     *
     * @return Collection<int, EditionModel>
     */
    private function monumentTitleEditions(User $user): Collection
    {
        return $this->leagueTitleEditions($user)
            ->filter(fn (EditionModel $edition) => $edition->competition->type === CompetitionType::Monument)
            ->values();
    }

    /**
     * Temporadas terminadas primero en la clasificación general oficial.
     *
     * Solo se premian temporadas cerradas: la de un año en curso no se
     * toca hasta que todas sus ediciones oficiales estén `finished`.
     *
     * @return Collection<int, int>
     */
    private function seasonChampionYearList(User $user): Collection
    {
        $totals = DB::table('score_events')
            ->join('leagues', 'leagues.id', '=', 'score_events.league_id')
            ->join('editions', 'editions.id', '=', 'leagues.edition_id')
            ->where('leagues.is_official', true)
            ->groupBy('editions.year', 'score_events.user_id')
            ->selectRaw('editions.year as year, score_events.user_id as user_id, SUM(score_events.points) as total')
            ->get();

        $wins = collect();

        foreach ($totals->groupBy('year') as $year => $yearTotals) {
            if (! $this->seasonCompleted((int) $year)) {
                continue;
            }

            $pointsByUser = [];

            foreach ($yearTotals as $row) {
                $pointsByUser[$row->user_id] = (int) $row->total;
            }

            $ranked = LeaderboardService::rankPoints($pointsByUser);

            if (($ranked[0]['user_id'] ?? null) === $user->id) {
                $wins->push((int) $year);
            }
        }

        return $wins->sort()->values();
    }

    /**
     * Una temporada está cerrada cuando todas sus ediciones con liga
     * oficial están `finished`.
     */
    private function seasonCompleted(int $year): bool
    {
        $incomplete = EditionModel::where('year', $year)
            ->whereHas('leagues', fn ($q) => $q->where('is_official', true))
            ->where('status', '!=', EditionStatus::Finished)
            ->exists();

        return ! $incomplete;
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

        $dateFor = fn (int $milestone): \DateTimeInterface => $this->milestoneDate($user, $definition, $milestone) ?? now();

        $badge->level = max($oldLevel, $level);
        $badge->progress = max($oldProgress, $value);

        if ($badge->awarded_at === null && $badge->level > 0) {
            $badge->awarded_at = $definition->mode === BadgeMode::Once
                ? $dateFor($definition->target())
                : $dateFor((int) $definition->levels[0]);
        }

        $badge->save();

        // Progreso: los hitos intermedios con la fecha real en que se consiguieron
        if ($definition->mode === BadgeMode::Once) {
            for ($step = $oldProgress + 1; $step <= $badge->progress; $step++) {
                $this->recordEvent($user, $definition, 'progress', $badge->level, $step, $dateFor($step));
            }
        } elseif ($badge->progress > $oldProgress) {
            $this->recordEvent($user, $definition, 'progress', $badge->level, $badge->progress, $dateFor($badge->progress));
        }

        // Niveles: un evento por tier alcanzado, fechado con su fecha real
        if ($definition->mode === BadgeMode::Once) {
            if ($badge->level > $oldLevel) {
                $this->recordEvent($user, $definition, 'level', $definition->target(), $badge->progress, $dateFor($definition->target()));
            }
        } else {
            foreach ($definition->levels as $tier) {
                if ($tier > $oldLevel && $tier <= $badge->level) {
                    $this->recordEvent($user, $definition, 'level', $tier, $badge->progress, $dateFor($tier));
                }
            }
        }

        return $badge->level > $oldLevel ? 'earned' : 'progressed';
    }

    private function recordEvent(
        User $user,
        BadgeDefinition $definition,
        string $event,
        int $level,
        int $progress,
        \DateTimeInterface $when,
    ): void {
        BadgeEventModel::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'badge_code' => $definition->code->value,
            'event' => $event,
            'level' => $level,
            'progress' => $progress,
            'context' => null,
            'created_at' => $when,
        ]);
    }

    /**
     * Vista de detalle de un badge para un usuario: niveles con fechas e
     * histórico completo de hitos.
     */
    public function badgeDetail(User $user, string $badgeCode): BadgeDetailViewDTO
    {
        $definition = BadgeCatalog::definitions()[$badgeCode] ?? null;

        if ($definition === null) {
            throw (new ModelNotFoundException)->setModel(UserBadgeModel::class, $badgeCode);
        }

        $badge = UserBadgeModel::where('user_id', $user->id)
            ->where('badge_code', $badgeCode)
            ->first();

        $value = (int) ($this->computeValues($user)[$badgeCode] ?? 0);
        $level = $definition->mode === BadgeMode::Once
            ? ($value >= $definition->target() ? 1 : 0)
            : $definition->levelFor($value);
        $levelIndex = $this->levelIndex($definition, $level);

        $events = BadgeEventModel::where('user_id', $user->id)
            ->where('badge_code', $badgeCode)
            ->orderByDesc('created_at')
            ->get();

        $levelsDetail = [];

        foreach ($definition->levels as $index => $tier) {
            $reached = $value >= $tier;
            $achievedAt = $events->first(fn (BadgeEventModel $event) => $event->event === 'level' && $event->level === $tier)?->created_at
                ?? ($reached ? $badge?->awarded_at : null);

            $levelsDetail[] = [
                'value' => $tier,
                'level_label' => $definition->mode === BadgeMode::Once ? 'Objetivo' : 'Nivel '.($index + 1),
                'reached' => $reached,
                'achieved_at' => $achievedAt?->toISOString(),
            ];
        }

        return new BadgeDetailViewDTO(
            badge: [
                'code' => $definition->code->value,
                'name' => $definition->name,
                'description' => $definition->description,
                'category' => $definition->category,
                'mode' => $definition->mode->value,
                'svg' => $definition->svg(),
                'levels' => $definition->levels,
                'special' => $definition->special,
                'display' => $definition->display->value,
                'overlay' => $this->overlayValue($definition, $value, $levelIndex),
            ],
            user: ['id' => $user->id, 'name' => $user->name],
            level: $level,
            levelIndex: $levelIndex,
            progress: $value,
            target: $definition->nextLevel($value) ?? $definition->target(),
            awardedAt: $badge?->awarded_at?->toISOString(),
            levelsDetail: $levelsDetail,
            history: $events->map(fn (BadgeEventModel $event) => [
                'event' => $event->event,
                'level' => $event->level,
                'progress' => $event->progress,
                'created_at' => $event->created_at?->toISOString(),
            ])->values()->all(),
        );
    }

    /**
     * Fecha real en la que el usuario alcanzó un hito (valor/tier) del badge.
     */
    private function milestoneDate(User $user, BadgeDefinition $definition, int $milestone): ?\DateTimeInterface
    {
        return match (true) {
            $definition->code === BadgeCode::StagesPlayed => $this->nthPredictedStageDate($user, $milestone),
            $definition->code === BadgeCode::StagesWon => $this->nthWonStageDate($user, $milestone),
            $definition->code === BadgeCode::GrandTourParticipation,
            $definition->code === BadgeCode::MonumentParticipation,
            $definition->code === BadgeCode::ChampionshipParticipation,
            $definition->code === BadgeCode::ClassicParticipation => $this->nthParticipationStart($user, $definition->code, $milestone),
            $definition->code === BadgeCode::TourWinner => $this->nthGtWinDate($user, 'tour', $milestone),
            $definition->code === BadgeCode::GiroWinner => $this->nthGtWinDate($user, 'giro', $milestone),
            $definition->code === BadgeCode::VueltaWinner => $this->nthGtWinDate($user, 'vuelta', $milestone),
            $definition->code === BadgeCode::MonumentWinner => $this->nthMonumentWinDate($user, $milestone),
            $definition->code === BadgeCode::MonumentCollection => $this->nthMonumentCollectionDate($user, $milestone),
            $definition->code === BadgeCode::WorldChampion => $this->worldChampionDate($user),
            $definition->code === BadgeCode::Trilogy => $this->nthTrilogyDate($user, $milestone),
            $definition->code === BadgeCode::SeasonChampion => $this->nthSeasonChampionDate($user, $milestone),
            $definition->code === BadgeCode::Tester => $this->firstSeasonDate($user),
            default => null,
        };
    }

    private function nthPredictedStageDate(User $user, int $n): ?\DateTimeInterface
    {
        return StageModel::whereIn('id', DB::table('predictions')->where('user_id', $user->id)->whereNotNull('stage_id')->select('stage_id'))
            ->where('status', '!=', StageStatus::Upcoming)
            ->orderBy('date')
            ->offset($n - 1)
            ->first()?->date;
    }

    private function nthWonStageDate(User $user, int $n): ?\DateTimeInterface
    {
        $stageIds = $this->predictionHits->correctWinnerPredictions(null, $user->id)
            ->pluck('stage_id')
            ->unique()
            ->values();

        return StageModel::whereIn('id', $stageIds)
            ->orderBy('date')
            ->offset($n - 1)
            ->first()?->date;
    }

    private function nthParticipationStart(User $user, BadgeCode $code, int $n): ?\DateTimeInterface
    {
        $type = match ($code) {
            BadgeCode::GrandTourParticipation => 'gc',
            BadgeCode::MonumentParticipation => 'monument',
            BadgeCode::ChampionshipParticipation => 'championship',
            default => 'classic',
        };

        return EditionModel::query()
            ->join('competitions', 'competitions.id', '=', 'editions.competition_id')
            ->join('leagues', 'leagues.edition_id', '=', 'editions.id')
            ->join('predictions', function ($join) use ($user) {
                $join->on('predictions.league_id', '=', 'leagues.id')
                    ->where('predictions.user_id', $user->id);
            })
            ->where('competitions.type', $type)
            ->select('editions.*')
            ->distinct()
            ->orderBy('editions.start_date')
            ->offset($n - 1)
            ->first()?->start_date;
    }

    private function nthGtWinDate(User $user, string $key, int $n): ?\DateTimeInterface
    {
        return $this->titleEditionsFor($user, $key)
            ->skip($n - 1)
            ->first()?->end_date;
    }

    private function nthMonumentWinDate(User $user, int $n): ?\DateTimeInterface
    {
        return $this->monumentTitleEditions($user)->get($n - 1)?->end_date;
    }

    private function nthMonumentCollectionDate(User $user, int $n): ?\DateTimeInterface
    {
        return $this->monumentTitleEditions($user)
            ->unique('competition_id')
            ->values()
            ->get($n - 1)?->end_date;
    }

    private function nthTrilogyDate(User $user, int $n): ?\DateTimeInterface
    {
        $dates = collect(['tour', 'giro', 'vuelta'])
            ->flatMap(fn (string $key) => $this->titleEditionsFor($user, $key)->pluck('end_date'))
            ->sort()
            ->values();

        return $dates->get($n - 1);
    }

    private function worldChampionDate(User $user): ?\DateTimeInterface
    {
        return $this->titleEditionsFor($user, 'worlds')->first()?->end_date;
    }

    /**
     * Fecha de cierre de la última edición oficial del año ganado (fallback:
     * fin del año).
     */
    private function nthSeasonChampionDate(User $user, int $n): ?\DateTimeInterface
    {
        $year = $this->seasonChampionYearList($user)->get($n - 1);

        if ($year === null) {
            return null;
        }

        $lastEdition = EditionModel::where('year', $year)
            ->whereHas('leagues', fn ($q) => $q->where('is_official', true))
            ->orderByDesc('end_date')
            ->first();

        return $lastEdition?->end_date ?? Carbon::create((int) $year, 12, 31);
    }

    /**
     * Fecha de la primera participación en la primera temporada del juego.
     */
    private function firstSeasonDate(User $user): ?\DateTimeInterface
    {
        $firstYear = DB::table('editions')->min('year');

        if ($firstYear === null) {
            return null;
        }

        $date = DB::table('predictions')
            ->join('leagues', 'leagues.id', '=', 'predictions.league_id')
            ->join('editions', 'editions.id', '=', 'leagues.edition_id')
            ->where('predictions.user_id', $user->id)
            ->where('editions.year', (int) $firstYear)
            ->min('predictions.created_at');

        return $date === null ? null : Carbon::parse($date);
    }

    private function levelLabel(BadgeDefinition $definition, int $level): string
    {
        if ($definition->mode === BadgeMode::Once) {
            return $definition->name;
        }

        $index = array_search($level, $definition->levels, true);

        return $index === false ? '' : 'Nivel '.($index + 1);
    }

    /**
     * Número de niveles alcanzados (para el relleno/hueco de la UI).
     */
    private function levelIndex(BadgeDefinition $definition, int $level): int
    {
        if ($level <= 0) {
            return 0;
        }

        $index = array_search($level, $definition->levels, true);

        return $index === false ? 0 : $index + 1;
    }

    /**
     * Número que se superpone al icono del badge (veces conseguido o nivel).
     */
    private function overlayValue(BadgeDefinition $definition, int $progress, int $levelIndex): ?int
    {
        return match ($definition->display) {
            BadgeDisplay::Times => $progress > 0 ? $progress : null,
            BadgeDisplay::Level => $levelIndex > 0 ? $levelIndex : null,
            BadgeDisplay::None => null,
        };
    }
}
