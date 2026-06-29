<?php

declare(strict_types=1);

namespace App\Application\UseCases\Classification;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
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

        $scoresPerUser = ScoreEventModel::where('league_id', $leagueId)
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->pluck('total_points', 'user_id');

        $members = DB::table('league_user')
            ->where('league_id', $leagueId)
            ->join('users', 'users.id', '=', 'league_user.user_id')
            ->select('users.id', 'users.name')
            ->get();

        $leaderboard = $members
            ->map(fn ($member) => [
                'user_id' => $member->id,
                'user_name' => $member->name,
                'points' => (int) ($scoresPerUser[$member->id] ?? 0),
                'is_current_user' => $member->id === $user->id,
            ])
            ->sortByDesc('points')
            ->values()
            ->map(fn ($entry, $index) => [
                'rank' => $index + 1,
                ...$entry,
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
            'leaderboard' => $leaderboard->values()->all(),
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
