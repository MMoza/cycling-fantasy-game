<?php

namespace App\Domain\Services;

use Carbon\Carbon;

class OnlineStatusService
{
    private const ONLINE_THRESHOLD_MINUTES = 5;

    public static function isOnline(?Carbon $lastActiveAt): bool
    {
        return $lastActiveAt !== null && $lastActiveAt->diffInMinutes(now()) < self::ONLINE_THRESHOLD_MINUTES;
    }
}
