<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Services\PushNotificationService;
use Illuminate\Console\Command;

class CleanPushSubscriptionsCommand extends Command
{
    protected $signature = 'push:clean';

    protected $description = 'Clean stale push subscriptions (older than 30 days)';

    public function handle(PushNotificationService $pushNotification): int
    {
        $deleted = $pushNotification->cleanStaleSubscriptions();

        $this->info("Cleaned {$deleted} stale push subscriptions");

        return self::SUCCESS;
    }
}
