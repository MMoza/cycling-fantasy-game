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

        $riderNames = RiderModel::pluck('first_name', 'id');
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

            $preRacePredictions = PredictionModel::where('league_id', $leagueId)
                ->where('user_id', $targetUserId)
                ->whereNull('stage_id')
                ->where('type', 'pre_race')
                ->get()
                ->map(fn ($p) => [
                    'category' => $p->category,
                    'value' => $this->formatPrediction($p->prediction_value, $p->category->value, $riderNames, $teamNames),
                    'points' => (int) ($preRacePoints[$p->category->value] ?? 0),
                ])
                ->values()
                ->all();
        }

        $stageCategoryPoints = ScoreEventModel::where('league_id', $leagueId)
            ->where('user_id', $targetUserId)
            ->whereNotNull('stage_id')
            ->selectRaw('stage_id, context, SUM(points) as total_points')
            ->groupBy('stage_id', 'context')
            ->get()
            ->keyBy(fn ($e) => $e->stage_id . '|' . $e->context);

        $stagePredictions = PredictionModel::where('league_id', $leagueId)
            ->where('user_id', $targetUserId)
            ->whereNotNull('stage_id')
            ->where('type', 'pre_stage')
            ->get()
            ->groupBy('stage_id');

        $stageDetails = [];
        foreach ($stages as $stage) {
            if ($stage->status === StageStatus::Upcoming) {
                continue;
            }

            $predictions = $stagePredictions->get($stage->id);
            if (! $predictions) {
                continue;
            }

            $totalPoints = 0;
            $mappedPredictions = $predictions->map(fn ($p) => [
                'category' => $p->category,
                'value' => $this->formatPrediction($p->prediction_value, $p->category->value, $riderNames, $teamNames),
                'points' => (int) ($stageCategoryPoints->get($stage->id . '|' . $p->category->value)?->total_points ?? 0),
            ])->values();

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

    private function formatPrediction(array $value, string $category, $riderNames, $teamNames): string
    {
        if ($category === 'gc_top_5' || str_contains($category, 'winner') || str_contains($category, 'youth') || str_contains($category, 'mountains')) {
            $ids = $value['rider_ids'] ?? $value;

            return collect($ids)->map(fn ($id) => $riderNames[$id] ?? '—')->implode(', ');
        }

        if ($category === 'teams_winner') {
            $teamId = $value['team_id'] ?? null;

            return $teamNames[$teamId] ?? '—';
        }

        $riderId = $value['rider_id'] ?? null;

        return $riderNames[$riderId] ?? '—';
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
