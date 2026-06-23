<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassificationController extends Controller
{
    public function index(Request $request, string $league)
    {
        $user = $request->user();

        $leagueModel = LeagueModel::findOrFail($league);

        if (! $user->leagues()->where('leagues.id', $league)->exists()) {
            abort(404);
        }

        $user->update(['last_visited_league_id' => $league]);

        $scores = ScoreEventModel::where('league_id', $league)
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->with('user')
            ->orderBy('total_points', 'desc')
            ->get();

        $leaderboard = $scores->map(fn ($score, $index) => [
            'rank' => $index + 1,
            'user_name' => $score->user->name,
            'user_id' => $score->user_id,
            'points' => $score->total_points,
            'is_current_user' => $score->user_id === $user->id,
        ]);

        $topPoints = $leaderboard->first()['points'] ?? 0;

        $leaderboard = $leaderboard->map(fn ($entry) => [
            ...$entry,
            'behind_leader' => $topPoints - $entry['points'],
        ]);

        $userEntry = $leaderboard->firstWhere('is_current_user');

        return Inertia::render('Classification/Index', [
            'league_id' => $leagueModel->id,
            'league_name' => $leagueModel->name,
            'leaderboard' => $leaderboard,
            'user_position' => $userEntry ? [
                'rank' => $userEntry['rank'],
                'points' => $userEntry['points'],
                'behind_leader' => $userEntry['behind_leader'],
            ] : [
                'rank' => '-',
                'points' => 0,
                'behind_leader' => 0,
            ],
        ]);
    }
}
