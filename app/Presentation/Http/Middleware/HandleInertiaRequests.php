<?php

namespace App\Presentation\Http\Middleware;

use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\LeagueModel;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(private readonly MediaUrlResolverInterface $mediaUrls) {}

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

        $user = $request->user();
        $userData = null;
        if ($user) {
            $userData = $user->toArray();
            $userData['avatar'] = $user->avatar
                ? ($this->mediaUrls->resolve($user->avatar) ?? '')
                : null;
        }

        $userLeagues = [];
        if ($user) {
            $userLeagues = $user->leagues()
                ->limit(5)
                ->get()
                ->map(fn ($league) => [
                    'id' => $league->id,
                    'name' => $league->name,
                ])
                ->toArray();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userData,
                'user_leagues' => $userLeagues,
            ],
            'currentLeague' => $currentLeague,
        ];
    }
}
