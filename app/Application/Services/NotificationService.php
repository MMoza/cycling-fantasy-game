<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\NotificationModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationService
{
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

        $allUserScores = DB::table('score_events')
            ->where('league_id', $league->id)
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->get();

        $position = 1;

        foreach ($allUserScores as $index => $score) {
            if ($score->user_id === $userId) {
                $position = $index + 1;

                break;
            }
        }

        $leaderboard = $this->buildLeaderboard($league->id, $userId);

        $stagesWon = DB::table('predictions')
            ->join('stage_results', function ($join) {
                $join->on('predictions.stage_id', '=', 'stage_results.stage_id')
                    ->where('stage_results.position', '=', 1);
            })
            ->where('predictions.league_id', $league->id)
            ->where('predictions.user_id', $userId)
            ->where('predictions.category', 'stage_winner')
            ->whereRaw("JSON_EXTRACT(predictions.prediction_value, '$.rider_id') = stage_results.rider_id")
            ->count();

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
            ->orderByDesc('total_points')
            ->get();

        $users = DB::table('users')
            ->whereIn('id', $allScores->pluck('user_id')->toArray())
            ->get()
            ->keyBy('id');

        $leaderboard = [];
        $rank = 1;

        foreach ($allScores as $score) {
            $user = $users->get($score->user_id);

            $leaderboard[] = [
                'rank' => $rank,
                'user_id' => $score->user_id,
                'user_name' => $user->name ?? 'Unknown',
                'avatar' => $user->avatar ?? null,
                'points' => (int) $score->total_points,
                'behind_leader' => $rank === 1 ? 0 : (int) $allScores[0]->total_points - (int) $score->total_points,
                'is_current_user' => $score->user_id === $currentUserId,
                'is_online' => false,
                'previous_rank' => null,
                'rank_change' => null,
            ];

            $rank++;
        }

        return $leaderboard;
    }
}
