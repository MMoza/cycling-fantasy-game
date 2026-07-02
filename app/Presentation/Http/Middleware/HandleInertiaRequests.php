<?php

namespace App\Presentation\Http\Middleware;

use App\Infrastructure\Persistence\Models\LeagueModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        $user = $request->user();
        $userData = null;
        if ($user) {
            $userData = $user->toArray();
            $userData['avatar'] = $user->avatar
                ? $this->resolveS3Url($user->avatar)
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

    private function resolveS3Url(string $path): string
    {
        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl($path, now()->addHours(24));
        } catch (\Exception) {
            // fall through
        }

        try {
            return $disk->url($path);
        } catch (\Exception) {
            // fall through
        }

        $endpoint = rtrim(config('filesystems.disks.s3.endpoint', ''), '/');
        $bucket = config('filesystems.disks.s3.bucket', '');

        if ($endpoint && $bucket) {
            return "{$endpoint}/{$bucket}/".ltrim($path, '/');
        }

        return '';
    }
}
