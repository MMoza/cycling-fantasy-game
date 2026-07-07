<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Services\PushNotificationService;
use App\Infrastructure\Persistence\Models\PushSubscriptionModel;
use App\Models\User;
use Illuminate\Console\Command;

class TestPushNotificationCommand extends Command
{
    protected $signature = 'push:test {user_id? : Send to a specific user UUID (omit for all subscribed users)}';

    protected $description = 'Send a test push notification';

    public function handle(PushNotificationService $pushNotification): int
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            $subscriptions = PushSubscriptionModel::where('user_id', $userId)->get();
        } else {
            $subscriptions = PushSubscriptionModel::all();
        }

        if ($subscriptions->isEmpty()) {
            $this->warn('No push subscriptions found');

            return self::SUCCESS;
        }

        $userIds = $subscriptions->pluck('user_id')->unique();

        foreach ($userIds as $uid) {
            $user = User::find($uid);
            if ($user) {
                $pushNotification->sendToUser(
                    $user,
                    'Pedales',
                    'Las notificaciones push están funcionando correctamente',
                    ['type' => 'test', 'url' => '/dashboard'],
                );
                $this->info("Sent to {$user->name} ({$uid})");
            }
        }

        $this->info("Test notification sent to {$userIds->count()} user(s)");

        return self::SUCCESS;
    }
}
