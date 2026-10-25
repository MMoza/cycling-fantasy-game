<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\View\LeaderboardEntryViewDTO;
use App\Application\DTOs\View\LeagueStandingsDTO;
use App\Application\DTOs\View\UserPositionViewDTO;
use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Domain\Services\OnlineStatusService;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds league standings: leaderboard rows with rank movement, behind-leader
 * gaps and winner streaks, plus the current user position.
 */
class LeaderboardService
{
    public function __construct(
        private readonly MediaUrlResolverInterface $mediaUrls,
        private readonly PredictionHitService $predictionHits,
    ) {}

    /**
     * Ranks points per user descending.
     *
     * @param  array<string, int>  $pointsByUser
     * @return array<int, array{user_id: string, points: int, rank: int, behind_leader: int}>
     */
    public static function rankPoints(array $pointsByUser): array
    {
        arsort($pointsByUser);

        $topPoints = $pointsByUser === [] ? 0 : (int) reset($pointsByUser);

        $rows = [];
        $rank = 1;

        foreach ($pointsByUser as $userId => $points) {
            $rows[] = [
                'user_id' => (string) $userId,
                'points' => (int) $points,
                'rank' => $rank,
                'behind_leader' => $topPoints - (int) $points,
            ];
            $rank++;
        }

        return $rows;
    }

    public function leagueStandings(LeagueModel $league, string $currentUserId): LeagueStandingsDTO
    {
        $allScoreEvents = ScoreEventModel::where('league_id', $league->id)
            ->selectRaw('user_id, stage_id, SUM(points) as total_points')
            ->groupBy('user_id', 'stage_id')
            ->get();

        $generalScores = $allScoreEvents->whereNull('stage_id');
        $perStageScores = $allScoreEvents->whereNotNull('stage_id');

        $members = DB::table('league_user')
            ->where('league_id', $league->id)
            ->join('users', 'users.id', '=', 'league_user.user_id')
            ->select('users.id', 'users.name', 'users.avatar', 'users.last_active_at')
            ->get();

        $scoredStages = $league->stages()
            ->where('status', 'finished')
            ->whereIn('id', $perStageScores->pluck('stage_id')->unique())
            ->orderBy('number')
            ->get(['id', 'number']);

        $previousRanks = $this->previousRanks($members, $generalScores, $perStageScores, $scoredStages);

        $totalScoresPerUser = $allScoreEvents
            ->groupBy('user_id')
            ->map(fn ($events) => $events->sum('total_points'));

        $leaderboard = $members
            ->map(fn ($member) => [
                'user_id' => $member->id,
                'user_name' => $member->name,
                'avatar' => $this->mediaUrls->resolve($member->avatar),
                'points' => (int) ($totalScoresPerUser[$member->id] ?? 0),
                'is_current_user' => $member->id === $currentUserId,
                'is_online' => OnlineStatusService::isOnline(
                    $member->last_active_at ? Carbon::parse($member->last_active_at) : null
                ),
            ])
            ->sortByDesc('points')
            ->values()
            ->map(fn ($entry, $index) => [
                'rank' => $index + 1,
                ...$entry,
            ]);

        $topPoints = $leaderboard->first()['points'] ?? 0;
        $winnerStreaks = $this->winnerStreaks($league->id, $scoredStages);

        $entries = $leaderboard
            ->map(function (array $entry) use ($previousRanks, $topPoints, $winnerStreaks): LeaderboardEntryViewDTO {
                $previousRank = $previousRanks[$entry['user_id']] ?? null;
                $rankChange = $previousRank !== null ? $previousRank - $entry['rank'] : null;

                return new LeaderboardEntryViewDTO(
                    userId: $entry['user_id'],
                    userName: $entry['user_name'],
                    avatar: $entry['avatar'],
                    points: $entry['points'],
                    isCurrentUser: $entry['is_current_user'],
                    isOnline: $entry['is_online'],
                    rank: $entry['rank'],
                    previousRank: $previousRank,
                    rankChange: $rankChange,
                    behindLeader: $topPoints - $entry['points'],
                    winnerStreak: $winnerStreaks[$entry['user_id']] ?? 0,
                );
            })
            ->values()
            ->all();

        $userEntry = collect($entries)->firstWhere('isCurrentUser', true);

        $userPosition = $userEntry !== null
            ? UserPositionViewDTO::fromStanding($userEntry->rank, $userEntry->points, $userEntry->behindLeader)
            : UserPositionViewDTO::empty();

        return new LeagueStandingsDTO(entries: $entries, userPosition: $userPosition);
    }

    /**
     * Ranks of the previous scored stage, used to compute rank movement.
     *
     * @return array<string, int>
     */
    private function previousRanks(Collection $members, Collection $generalScores, Collection $perStageScores, Collection $scoredStages): array
    {
        if ($scoredStages->count() < 2) {
            return [];
        }

        $previousScores = $generalScores->concat(
            $perStageScores->filter(fn ($s) => in_array($s->stage_id, $scoredStages->pluck('id')->take($scoredStages->count() - 1)->toArray(), true))
        );

        $previousScoresPerUser = $previousScores
            ->groupBy('user_id')
            ->map(fn ($events) => $events->sum('total_points'));

        $previousLeaderboard = $members
            ->map(fn ($member) => [
                'user_id' => $member->id,
                'points' => (int) ($previousScoresPerUser[$member->id] ?? 0),
            ])
            ->sortByDesc('points')
            ->values()
            ->map(fn ($entry, $index) => [
                'rank' => $index + 1,
                ...$entry,
            ]);

        return $previousLeaderboard->pluck('rank', 'user_id')->toArray();
    }

    /**
     * Consecutive stage-winner hits from the most recent scored stages.
     *
     * @return array<string, int>
     */
    private function winnerStreaks(string $leagueId, Collection $scoredStages): array
    {
        if ($scoredStages->isEmpty()) {
            return [];
        }

        $lastStageIds = $scoredStages->take(-5)->pluck('id')->reverse()->values()->all();

        $correctPredictions = $this->predictionHits
            ->correctWinnerPredictions($leagueId, null, 'stage_winner', $lastStageIds)
            ->groupBy('user_id');

        $streaks = [];

        foreach ($correctPredictions as $userId => $hits) {
            $hitStageIds = $hits->pluck('stage_id')->toArray();
            $streak = 0;

            foreach ($lastStageIds as $stageId) {
                if (in_array($stageId, $hitStageIds, true)) {
                    $streak++;
                } else {
                    break;
                }
            }

            if ($streak >= 2) {
                $streaks[$userId] = $streak;
            }
        }

        return $streaks;
    }
}
