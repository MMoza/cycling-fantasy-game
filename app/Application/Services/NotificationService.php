<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\NotificationModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationService
{
    public function __construct(private readonly PredictionHitService $predictionHits) {}

    public function notifyCompetitionEnded(string $leagueId): void
    {
        $league = LeagueModel::with(['edition.competition', 'users'])->find($leagueId);

        if (! $league) {
            return;
        }

        foreach ($league->users as $user) {
            $this->createCompetitionEndedNotification($league, $user->id);
        }
    }

    private function createCompetitionEndedNotification(LeagueModel $league, string $userId): void
    {
        $userScores = DB::table('score_events')
            ->where('league_id', $league->id)
            ->where('user_id', $userId)
            ->selectRaw('SUM(points) as total_points')
            ->value('total_points');

        $totalPoints = (int) ($userScores ?? 0);

        $leaderboard = $this->buildLeaderboard($league->id, $userId);

        $position = 1;

        foreach ($leaderboard as $row) {
            if ($row['user_id'] === $userId) {
                $position = $row['rank'];

                break;
            }
        }

        $stagesWon = $this->predictionHits->correctWinnerPredictions($league->id, $userId)->count();

        $bestStage = DB::table('score_events')
            ->join('stages', 'stages.id', '=', 'score_events.stage_id')
            ->where('score_events.user_id', $userId)
            ->where('score_events.league_id', $league->id)
            ->whereNotNull('score_events.stage_id')
            ->selectRaw('stages.id, stages.number, stages.name, SUM(score_events.points) as stage_points')
            ->groupBy('stages.id', 'stages.number', 'stages.name')
            ->orderByDesc('stage_points')
            ->first();

        $user = DB::table('users')->where('id', $userId)->first();

        NotificationModel::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $userId,
            'league_id' => $league->id,
            'type' => 'competition_ended',
            'title' => "¡{$league->edition->competition->name} {$league->edition->year} ha terminado!",
            'description' => "Has finalizado en posición #{$position} con {$totalPoints} puntos.",
            'data' => [
                'user_name' => $user->name,
                'user_avatar' => $user->avatar,
                'position' => $position,
                'total_points' => $totalPoints,
                'stages_won' => $stagesWon,
                'competition_name' => $league->edition->competition->name,
                'competition_year' => $league->edition->year,
                'best_stage' => $bestStage ? [
                    'number' => $bestStage->number,
                    'name' => $bestStage->name,
                    'points' => (int) $bestStage->stage_points,
                ] : null,
                'is_official' => $league->is_official,
                'league_id' => $league->id,
                'leaderboard' => $leaderboard,
            ],
        ]);
    }

    private function buildLeaderboard(string $leagueId, string $currentUserId): array
    {
        $allScores = DB::table('score_events')
            ->where('league_id', $leagueId)
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->get();

        $users = DB::table('users')
            ->whereIn('id', $allScores->pluck('user_id')->toArray())
            ->get()
            ->keyBy('id');

        return array_map(function (array $row) use ($users, $currentUserId) {
            $user = $users->get($row['user_id']);

            return [
                'rank' => $row['rank'],
                'user_id' => $row['user_id'],
                'user_name' => $user->name ?? 'Unknown',
                'avatar' => $user->avatar ?? null,
                'points' => $row['points'],
                'behind_leader' => $row['behind_leader'],
                'is_current_user' => $row['user_id'] === $currentUserId,
                'is_online' => false,
                'previous_rank' => null,
                'rank_change' => null,
            ];
        }, LeaderboardService::rankPoints($allScores->pluck('total_points', 'user_id')->all()));
    }
}
