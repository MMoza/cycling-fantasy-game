<?php

declare(strict_types=1);

namespace App\Application\UseCases\UserProfile;

use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShowUserProfileUseCase
{
    public function execute(User $currentUser, string $leagueId, string $targetUserId): array
    {
        $league = LeagueModel::findOrFail($leagueId);

        if (! $currentUser->leagues()->where('leagues.id', $leagueId)->exists()) {
            abort(404);
        }

        $currentUser->update(['last_visited_league_id' => $leagueId]);

        $targetUser = User::findOrFail($targetUserId);

        if (! $targetUser->leagues()->where('leagues.id', $leagueId)->exists()) {
            abort(404);
        }

        $members = DB::table('league_user')
            ->where('league_id', $leagueId)
            ->join('users', 'users.id', '=', 'league_user.user_id')
            ->select('users.id', 'users.name', 'users.avatar')
            ->get();

        $scoresPerUser = ScoreEventModel::where('league_id', $leagueId)
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->pluck('total_points', 'user_id');

        $leaderboard = $members
            ->map(fn ($member) => [
                'user_id' => $member->id,
                'points' => (int) ($scoresPerUser[$member->id] ?? 0),
            ])
            ->sortByDesc('points')
            ->values()
            ->map(fn ($entry, $index) => [
                'rank' => $index + 1,
                ...$entry,
            ]);

        $topPoints = $leaderboard->first()['points'] ?? 0;
        $targetEntry = $leaderboard->firstWhere('user_id', $targetUserId);

        $riders = RiderModel::select('id', 'first_name', 'last_name')->get()->keyBy('id');
        $teamNames = TeamModel::pluck('name', 'id');

        $stages = $league->stages()->orderBy('number')->get(['id', 'number', 'name', 'status']);
        $competitionStarted = $stages->contains(fn ($s) => $s->status !== StageStatus::Upcoming);

        $preRacePredictions = [];
        if ($competitionStarted) {
            $preRacePoints = ScoreEventModel::where('league_id', $leagueId)
                ->where('user_id', $targetUserId)
                ->whereNull('stage_id')
                ->selectRaw('context, SUM(points) as total_points')
                ->groupBy('context')
                ->pluck('total_points', 'context');

            $preRaceOrder = [
                'gc_top_5' => 0,
                'points_winner' => 1,
                'youth_winner' => 2,
                'mountains_winner' => 3,
                'teams_winner' => 4,
                'super_combativo' => 5,
            ];

            $preRacePredictions = PredictionModel::where('league_id', $leagueId)
                ->where('user_id', $targetUserId)
                ->whereNull('stage_id')
                ->where('type', 'pre_race')
                ->get()
                ->map(fn ($p) => [
                    'category' => $p->category->value,
                    ...$this->formatPrediction($p->prediction_value, $p->category->value, $riders, $teamNames),
                    'points' => collect($preRacePoints)
                        ->filter(fn ($pts, $ctx) => str_starts_with((string) $ctx, $p->category->value))
                        ->sum(),
                ])
                ->sortBy(fn ($p) => $preRaceOrder[$p['category']] ?? 999)
                ->values()
                ->all();
        }

        $stageCategoryPoints = ScoreEventModel::where('league_id', $leagueId)
            ->where('user_id', $targetUserId)
            ->whereNotNull('stage_id')
            ->selectRaw('stage_id, context, SUM(points) as total_points')
            ->groupBy('stage_id', 'context')
            ->get()
            ->keyBy(fn ($e) => $e->stage_id.'|'.$e->context);

        $stagePredictions = PredictionModel::where('league_id', $leagueId)
            ->where('user_id', $targetUserId)
            ->whereNotNull('stage_id')
            ->where('type', 'pre_stage')
            ->get()
            ->groupBy('stage_id');

        $stageOrder = [
            'stage_winner' => 0,
            'stage_second' => 1,
            'stage_third' => 2,
            'stage_combativo' => 3,
            'stage_leader' => 4,
        ];

        $stageDetails = [];
        foreach ($stages as $stage) {
            if ($stage->status === StageStatus::Upcoming) {
                continue;
            }

            $predictions = $stagePredictions->get($stage->id);
            if (! $predictions) {
                continue;
            }

            $stageResultFlags = DB::table('stage_results')
                ->where('stage_id', $stage->id)
                ->select(['position', 'is_combativo', 'is_gc_leader'])
                ->get()
                ->keyBy('position');

            $contextPoints = $stageCategoryPoints
                ->filter(fn ($e) => $e->stage_id === $stage->id)
                ->mapWithKeys(fn ($e) => [$e->context => (int) $e->total_points]);

            $mappedPredictions = $predictions->map(function ($p) use ($stage, $contextPoints, $stageResultFlags, $riders, $teamNames) {
                $cat = $p->category->value;

                $ctx = match ($cat) {
                    'stage_winner' => 'stage_1',
                    'stage_second' => 'stage_2',
                    'stage_third' => 'stage_3',
                    'stage_combativo' => $this->flagContext('is_combativo', $stageResultFlags),
                    'stage_leader' => $this->flagContext('is_gc_leader', $stageResultFlags),
                    default => null,
                };

                return [
                    'category' => $cat,
                    ...$this->formatPrediction($p->prediction_value, $cat, $riders, $teamNames),
                    'points' => $ctx ? ($contextPoints[$ctx] ?? 0) : 0,
                ];
            })->sortBy(fn ($p) => $stageOrder[$p['category']] ?? 999)->values();

            $totalPoints = $mappedPredictions->sum('points');

            $stageDetails[] = [
                'stage_id' => $stage->id,
                'stage_number' => $stage->number,
                'stage_name' => $stage->name,
                'stage_status' => $stage->status->value,
                'points' => $totalPoints,
                'predictions' => $mappedPredictions->all(),
            ];
        }

        $avatarUrl = $targetUser->avatar
            ? $this->resolveAvatarUrl($targetUser->avatar)
            : null;

        $hasStagePredictions = ! empty($stageDetails);

        return [
            'league_id' => $league->id,
            'league_name' => $league->name,
            'competition_started' => $competitionStarted,
            'has_stage_predictions' => $hasStagePredictions,
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'avatar' => $avatarUrl,
                'rank' => $targetEntry ? $targetEntry['rank'] : '-',
                'points' => $targetEntry ? $targetEntry['points'] : 0,
                'behind_leader' => $targetEntry ? $topPoints - $targetEntry['points'] : 0,
            ],
            'pre_race_predictions' => $preRacePredictions,
            'stage_details' => $stageDetails,
        ];
    }

    private function formatPrediction(array $value, string $category, Collection $riders, $teamNames): array
    {
        if (isset($value['team_id'])) {
            $name = $teamNames[$value['team_id']] ?? '—';

            return [
                'label' => $name,
                'items' => [
                    ['id' => $value['team_id'], 'name' => $name, 'type' => 'team'],
                ],
            ];
        }

        if ($category === 'gc_top_5' || str_contains($category, 'winner') || str_contains($category, 'youth') || str_contains($category, 'mountains')) {
            $ids = $value['rider_ids'] ?? $value;

            $items = collect($ids)->map(fn ($id) => [
                'id' => $id,
                'name' => isset($riders[$id]) ? $riders[$id]->full_name : '—',
                'type' => 'rider',
            ])->all();

            return [
                'label' => collect($items)->pluck('name')->implode(', '),
                'items' => $items,
            ];
        }

        $riderId = $value['rider_id'] ?? null;
        $name = $riderId && isset($riders[$riderId]) ? $riders[$riderId]->full_name : '—';

        return [
            'label' => $name,
            'items' => [
                ['id' => $riderId, 'name' => $name, 'type' => 'rider'],
            ],
        ];
    }

    private function flagContext(string $flag, $stageResultFlags): ?string
    {
        $position = $stageResultFlags->firstWhere($flag, true)?->position;

        return $position ? 'stage_'.$position : null;
    }

    private function resolveAvatarUrl(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl($path, now()->addHours(24));
        } catch (\Exception) {
            try {
                return $disk->url($path);
            } catch (\Exception) {
                return null;
            }
        }
    }
}
