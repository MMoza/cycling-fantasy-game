<?php

declare(strict_types=1);

namespace App\Application\UseCases\Classification;

use App\Application\DTOs\View\ClassificationLeaderboardEntryViewDTO;
use App\Application\DTOs\View\ClassificationPageDTO;
use App\Application\Services\BadgeService;
use App\Application\Services\LeaderboardService;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ShowClassificationUseCase
{
    public function __construct(private readonly BadgeService $badgeService) {}

    public function execute(User $user, string $leagueId): ClassificationPageDTO
    {
        $league = LeagueModel::findOrFail($leagueId);

        if (! $user->leagues()->where('leagues.id', $leagueId)->exists()) {
            abort(404);
        }

        $user->update(['last_visited_league_id' => $leagueId]);

        $members = DB::table('league_user')
            ->where('league_id', $leagueId)
            ->join('users', 'users.id', '=', 'league_user.user_id')
            ->select('users.id', 'users.name')
            ->get();

        $allScoreEvents = ScoreEventModel::where('league_id', $leagueId)
            ->selectRaw('user_id, stage_id, SUM(points) as total_points')
            ->groupBy('user_id', 'stage_id')
            ->get();

        $generalScores = $allScoreEvents->whereNull('stage_id');
        $perStageScores = $allScoreEvents->whereNotNull('stage_id');

        $generalLeaderboard = $this->buildLeaderboard($members, $generalScores, $user->id);

        $stages = $league->stages()->orderBy('number')->get(['id', 'number', 'name']);

        $stageIdsWithScores = $perStageScores->pluck('stage_id')->unique()->toArray();

        $stageLeaderboards = [];
        $lastScoredStageId = null;
        $lastStageNumber = 0;

        foreach ($stages as $stage) {
            if (! in_array($stage->id, $stageIdsWithScores, true)) {
                continue;
            }

            $stageOnlyScores = $perStageScores->where('stage_id', $stage->id);

            $stageLeaderboard = $this->buildLeaderboard($members, $stageOnlyScores, $user->id);

            $stageLeaderboards[] = [
                'stage_id' => $stage->id,
                'stage_number' => $stage->number,
                'leaderboard' => $stageLeaderboard,
            ];

            if ($stage->number > $lastStageNumber) {
                $lastStageNumber = $stage->number;
                $lastScoredStageId = $stage->id;
            }
        }

        $userEntry = collect($generalLeaderboard)->firstWhere('isCurrentUser', true);

        return new ClassificationPageDTO(
            leagueId: $league->id,
            leagueName: $league->name,
            stages: $stages->map(fn ($s) => [
                'id' => $s->id,
                'number' => $s->number,
                'name' => $s->name,
                'has_scores' => in_array($s->id, $stageIdsWithScores, true),
            ])->values()->all(),
            generalLeaderboard: $generalLeaderboard,
            stageLeaderboards: $stageLeaderboards,
            lastScoredStageId: $lastScoredStageId,
            generalDetails: $this->buildGeneralDetails($league, $members, $user->id),
            userPosition: $userEntry ? [
                'rank' => $userEntry->rank,
                'points' => $userEntry->points,
                'behind_leader' => $userEntry->behindLeader,
            ] : [
                'rank' => '-',
                'points' => 0,
                'behind_leader' => 0,
            ],
        );
    }

    private function buildGeneralDetails($league, $members, string $currentUserId): array
    {
        $edition = $league->edition;
        $finalClassifications = FinalClassificationModel::where('edition_id', $edition->id)->get()->groupBy('category');

        $riderNames = RiderModel::pluck('first_name', 'id');
        $teamNames = TeamModel::pluck('name', 'id');

        $categoryLabels = [
            'gc_top_5' => 'Top 5 General',
            'points_winner' => 'Maillot Verde',
            'mountains_winner' => 'Montaña',
            'youth_winner' => 'Maillot Blanco',
            'teams_winner' => 'Equipos',
            'super_combativo' => 'Supercombativo',
        ];

        $scoreEventsByUserCategory = ScoreEventModel::where('league_id', $league->id)
            ->whereNull('stage_id')
            ->selectRaw('user_id, context, SUM(points) as total_points')
            ->groupBy('user_id', 'context')
            ->get()
            ->groupBy(fn ($e) => $e->user_id.'|'.$e->context);

        $predictions = PredictionModel::where('league_id', $league->id)
            ->whereNull('stage_id')
            ->where('type', 'pre_race')
            ->get()
            ->groupBy('category');

        $details = [];

        foreach ($categoryLabels as $category => $label) {
            $actualItems = $finalClassifications->get($category, collect());
            $predictionSet = $predictions->get($category, collect());

            $actual = $this->formatActual($actualItems, $category, $riderNames, $teamNames);

            $users = [];
            foreach ($members as $member) {
                $userPrediction = $predictionSet->firstWhere('user_id', $member->id);
                $scoreKey = $member->id.'|'.$category;
                $points = (int) ($scoreEventsByUserCategory->get($scoreKey)?->first()?->total_points ?? 0);

                $users[] = [
                    'user_id' => $member->id,
                    'user_name' => $member->name,
                    'is_current_user' => $member->id === $currentUserId,
                    'predicted' => $userPrediction
                        ? $this->formatPrediction($userPrediction->prediction_value, $category, $riderNames, $teamNames)
                        : null,
                    'points' => $points,
                ];
            }

            $details[] = [
                'category' => $category,
                'label' => $label,
                'actual' => $actual,
                'users' => $users,
            ];
        }

        return $details;
    }

    private function formatActual($items, string $category, $riderNames, $teamNames): array
    {
        if ($items->isEmpty()) {
            return [];
        }

        if ($category === 'teams_winner') {
            $teamId = $items->first()->team_id;

            return [['label' => 'Ganador', 'value' => $teamNames[$teamId] ?? '—']];
        }

        if ($category === 'super_combativo') {
            $riderId = $items->first()->rider_id;

            return [['label' => 'Ganador', 'value' => $riderNames[$riderId] ?? '—']];
        }

        return $items->sortBy('position')->values()->map(fn ($item) => [
            'label' => "#{$item->position}",
            'value' => $riderNames[$item->rider_id] ?? '—',
        ])->toArray();
    }

    private function formatPrediction(array $value, string $category, $riderNames, $teamNames): string
    {
        if ($category === 'gc_top_5' || str_contains($category, 'winner') || str_contains($category, 'youth') || str_contains($category, 'mountains')) {
            $ids = $value['rider_ids'] ?? [];

            return collect($ids)->map(fn ($id) => $riderNames[$id] ?? '—')->implode(', ');
        }

        if ($category === 'teams_winner') {
            $teamId = $value['team_id'] ?? null;

            return $teamNames[$teamId] ?? '—';
        }

        $riderId = $value['rider_id'] ?? null;

        return $riderNames[$riderId] ?? '—';
    }

    /**
     * @return array<int, ClassificationLeaderboardEntryViewDTO>
     */
    private function buildLeaderboard($members, $scores, string $currentUserId): array
    {
        $scoresPerUser = $scores->pluck('total_points', 'user_id');

        $pointsByUser = [];
        $names = [];

        foreach ($members as $member) {
            $pointsByUser[$member->id] = (int) ($scoresPerUser[$member->id] ?? 0);
            $names[$member->id] = $member->name;
        }

        $badgesByUser = $this->badgeService->badgesForUsers(array_keys($pointsByUser));

        return array_map(fn (array $row) => new ClassificationLeaderboardEntryViewDTO(
            userId: $row['user_id'],
            userName: $names[$row['user_id']],
            points: $row['points'],
            isCurrentUser: $row['user_id'] === $currentUserId,
            rank: $row['rank'],
            behindLeader: $row['behind_leader'],
            badges: $badgesByUser[$row['user_id']] ?? [],
        ), LeaderboardService::rankPoints($pointsByUser));
    }
}
