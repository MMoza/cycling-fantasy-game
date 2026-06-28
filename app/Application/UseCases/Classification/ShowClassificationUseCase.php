<?php

declare(strict_types=1);

namespace App\Application\UseCases\Classification;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Models\User;

class ShowClassificationUseCase
{
    public function execute(User $user, string $leagueId): array
    {
        $league = LeagueModel::findOrFail($leagueId);

        if (! $user->leagues()->where('leagues.id', $leagueId)->exists()) {
            abort(404);
        }

        $user->update(['last_visited_league_id' => $leagueId]);

        $scores = ScoreEventModel::where('league_id', $leagueId)
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->with('user')
            ->orderBy('total_points', 'desc')
            ->get();

        $leaderboard = $scores->map(fn ($score, $index) => [
            'rank' => $index + 1,
            'user_name' => $score->user->name,
            'user_id' => $score->user_id,
            'points' => (int) $score->total_points,
            'is_current_user' => $score->user_id === $user->id,
        ]);

        $topPoints = $leaderboard->first()['points'] ?? 0;

        $leaderboard = $leaderboard->map(fn ($entry) => [
            ...$entry,
            'behind_leader' => $topPoints - $entry['points'],
        ]);

        $userEntry = $leaderboard->firstWhere('is_current_user');

        return [
            'leagueId' => $league->id,
            'leagueName' => $league->name,
            'leaderboard' => $leaderboard,
            'userPosition' => $userEntry ? [
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
}
