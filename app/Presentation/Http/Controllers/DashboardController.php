<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\LeagueModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->last_visited_league_id) {
            $league = LeagueModel::find($user->last_visited_league_id);

            if ($league && $user->leagues()->where('leagues.id', $league->id)->exists()) {
                return redirect()->route('leagues.show', $league->id);
            }
        }

        $leagues = $user->leagues()->with(['edition.competition', 'scoringSystem'])->get();

        if ($leagues->isNotEmpty()) {
            $firstLeague = $leagues->first();

            return redirect()->route('leagues.show', $firstLeague->id);
        }

        return Inertia::render('Dashboard');
    }
}
