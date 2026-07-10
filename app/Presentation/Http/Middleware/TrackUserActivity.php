<?php

namespace App\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->last_active_at === null || $user->last_active_at->diffInSeconds(now()) > 60)) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['last_active_at' => now()]);
        }

        return $next($request);
    }
}
