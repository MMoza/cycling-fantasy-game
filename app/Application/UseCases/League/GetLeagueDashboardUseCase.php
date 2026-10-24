<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Application\DTOs\View\ActivityLogViewDTO;
use App\Application\DTOs\View\CompetitionViewDTO;
use App\Application\DTOs\View\LeagueDashboardDTO;
use App\Application\DTOs\View\LeagueViewDTO;
use App\Application\DTOs\View\NextStageViewDTO;
use App\Application\DTOs\View\ScoringRuleViewDTO;
use App\Application\DTOs\View\ScoringSystemViewDTO;
use App\Application\DTOs\View\StageProgressViewDTO;
use App\Application\DTOs\View\StageSummaryViewDTO;
use App\Application\Services\LeaderboardService;
use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\ActivityLogModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringRuleModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;

/**
 * Assembles every prop of the Leagues/Show page as typed view DTOs.
 */
class GetLeagueDashboardUseCase
{
    public function __construct(
        private readonly ShowLeagueUseCase $showLeagueUseCase,
        private readonly GetUserSeasonLeaguesUseCase $getUserSeasonLeaguesUseCase,
        private readonly LeaderboardService $leaderboardService,
        private readonly MediaUrlResolverInterface $mediaUrls,
    ) {}

    public function execute(User $user, string $leagueId): LeagueDashboardDTO
    {
        $league = $this->showLeagueUseCase->execute($user, $leagueId);

        $nextStage = $league->stages()
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->orderByRaw("CASE WHEN status = 'ongoing' THEN 0 ELSE 1 END")
            ->orderBy('date')
            ->orderBy('scheduled_start')
            ->first();

        $nextStageHasPredictions = $nextStage
            ? PredictionModel::where('league_id', $league->id)
                ->where('user_id', $user->id)
                ->where('stage_id', $nextStage->id)
                ->where('type', PredictionType::PreStage)
                ->exists()
            : false;

        $totalStages = $league->stages()->where('type', '!=', 'rest')->count();
        $completedStages = $league->stages()->where('status', 'finished')->count();

        $standings = $this->leaderboardService->leagueStandings($league, $user->id);

        $stages = $league->stages()
            ->orderBy('number')
            ->limit(5)
            ->get()
            ->map(fn (StageModel $stage) => StageSummaryViewDTO::fromModel($stage))
            ->values()
            ->all();

        $activityLogs = $league->activityLogs
            ->map(fn (ActivityLogModel $log) => ActivityLogViewDTO::fromModel($log, $this->mediaUrls))
            ->values()
            ->all();

        return new LeagueDashboardDTO(
            leagueId: $league->id,
            seasonLeagues: $this->getUserSeasonLeaguesUseCase->execute($user, $league->id),
            league: new LeagueViewDTO(
                id: $league->id,
                name: $league->name,
                inviteCode: $league->invite_code,
                competition: CompetitionViewDTO::fromModel(
                    $league->edition->competition,
                    (int) $league->edition->year,
                    $this->mediaUrls,
                ),
                ownerId: $league->owner_id,
                scoringSystem: new ScoringSystemViewDTO(
                    name: $league->scoringSystem->name,
                    type: $league->scoringSystem->type->value,
                    description: $league->scoringSystem->type->description(),
                    rules: $league->scoringSystem->rules
                        ->map(fn (ScoringRuleModel $rule) => ScoringRuleViewDTO::fromModel($rule))
                        ->values()
                        ->all(),
                ),
                isOfficial: $league->is_official,
                isPublic: $league->is_public,
                maxPlayers: $league->max_players,
                memberCount: count($standings->entries),
                isOwner: $league->owner_id === $user->id,
                editionStatus: $league->edition->status->value,
                progress: new StageProgressViewDTO(
                    currentStage: $completedStages + 1,
                    totalStages: $totalStages,
                ),
            ),
            nextStage: $nextStage ? NextStageViewDTO::fromModel($nextStage, $nextStageHasPredictions) : null,
            userPosition: $standings->userPosition,
            stages: $stages,
            leaderboard: $standings->entries,
            activityLogs: $activityLogs,
        );
    }
}
