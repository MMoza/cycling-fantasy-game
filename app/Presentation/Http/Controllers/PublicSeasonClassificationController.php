<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicSeasonClassificationController
{
    public function __invoke(): JsonResponse
    {
        $leaderboard = Cache::remember('public:season-classification', 300, function () {
            $rows = DB::table('score_events')
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
                ->get();

            $rowsByUser = $rows->keyBy('user_id');

            return array_map(fn (array $row) => [
                'position' => $row['rank'],
                'user_name' => $rowsByUser[$row['user_id']]->user_name,
                'avatar' => $rowsByUser[$row['user_id']]->avatar,
                'total_points' => $row['points'],
            ], LeaderboardService::rankPoints($rows->pluck('total_points', 'user_id')->all()));
        });

        return response()->json([
            'leaderboard' => $leaderboard,
        ]);
    }
}
