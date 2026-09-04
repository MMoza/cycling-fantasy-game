<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicSeasonClassificationController
{
    public function __invoke(): JsonResponse
    {
        $leaderboard = Cache::remember('public:season-classification', 300, function () {
            return DB::table('score_events')
                ->join('users', 'users.id', '=', 'score_events.user_id')
                ->select(
                    'users.id as user_id',
                    'users.name as user_name',
                    'users.avatar',
                    DB::raw('SUM(score_events.points) as total_points')
                )
                ->groupBy('users.id', 'users.name', 'users.avatar')
                ->orderByDesc('total_points')
                ->limit(10)
                ->get()
                ->map(fn ($row, $index) => [
                    'position' => $index + 1,
                    'user_name' => $row->user_name,
                    'avatar' => $row->avatar,
                    'total_points' => (int) $row->total_points,
                ])
                ->toArray();
        });

        return response()->json([
            'leaderboard' => $leaderboard,
        ]);
    }
}
