<?php

declare(strict_types=1);

namespace App\Application\UseCases\Classification;

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
    public function execute(User $user, string $leagueId): array
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

            $stageScores = $perStageScores->where('stage_id', $stage->id);
            $stageLeaderboards[] = [
                'stage_id' => $stage->id,
                'stage_number' => $stage->number,
                'leaderboard' => $this->buildLeaderboard($members, $stageScores, $user->id),
            ];

            if ($stage->number > $lastStageNumber) {
                $lastStageNumber = $stage->number;
                $lastScoredStageId = $stage->id;
            }
        }

        $userEntry = $generalLeaderboard->firstWhere('is_current_user');

        return [
            'leagueId' => $league->id,
            'leagueName' => $league->name,
            'stages' => $stages->map(fn ($s) => [
                'id' => $s->id,
                'number' => $s->number,
                'name' => $s->name,
                'has_scores' => in_array($s->id, $stageIdsWithScores, true),
            ])->values()->all(),
            'general_leaderboard' => $generalLeaderboard->values()->all(),
            'stage_leaderboards' => $stageLeaderboards,
            'last_scored_stage_id' => $lastScoredStageId,
            'general_details' => $this->buildGeneralDetails($league, $members, $user->id),
            'user_position' => $userEntry ? [
                'rank' => $userEntry['rank'],
                'points' => $userEntry['points'],
                'behind_leader' => $userEntry['behind_leader'],
            ] : [
                'rank' => '-',
                'points' => 0,
                'behind_leader' => 0,
            ],
        ];
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

    private function buildLeaderboard($members, $scores, string $currentUserId): \Illuminate\Support\Collection
    {
        $scoresPerUser = $scores->pluck('total_points', 'user_id');

        $leaderboard = $members
            ->map(fn ($member) => [
                'user_id' => $member->id,
                'user_name' => $member->name,
                'points' => (int) ($scoresPerUser[$member->id] ?? 0),
                'is_current_user' => $member->id === $currentUserId,
            ])
            ->sortByDesc('points')
            ->values()
            ->map(fn ($entry, $index) => [
                'rank' => $index + 1,
                ...$entry,
            ]);

        $topPoints = $leaderboard->first()['points'] ?? 0;

        return $leaderboard->map(fn ($entry) => [
            ...$entry,
            'behind_leader' => $topPoints - $entry['points'],
        ]);
    }
}
