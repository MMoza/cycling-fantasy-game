<?php

namespace App\Presentation\Http\Middleware;

use App\Infrastructure\Persistence\Models\LeagueModel;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $currentLeague = null;

        if ($request->user() && $request->user()->last_visited_league_id) {
            $league = LeagueModel::find($request->user()->last_visited_league_id);
            if ($league && $request->user()->leagues()->where('leagues.id', $league->id)->exists()) {
                $currentLeague = [
                    'id' => $league->id,
                    'name' => $league->name,
                ];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'currentLeague' => $currentLeague,
        ];
    }
}
