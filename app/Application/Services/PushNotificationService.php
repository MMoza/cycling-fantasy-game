<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PushSubscriptionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NotificationChannels\FCM\FCMMessage;

class PushNotificationService
{
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $subscriptions = PushSubscriptionModel::where('user_id', $user->id)->get();

        foreach ($subscriptions as $subscription) {
            try {
                $message = FCMMessage::create()
                    ->topic(null)
                    ->data($data)
                    ->notification([
                        'title' => $title,
                        'body' => $body,
                        'icon' => '/icons/icon-192.svg',
                    ]);

                $user->notify($message);
                $subscription->update(['last_used_at' => now()]);
            } catch (\Exception $e) {
                Log::error("Failed to send push notification to user {$user->id}: {$e->getMessage()}");

                if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                    $subscription->delete();
                }
            }
        }
    }

    public function sendToLeague(LeagueModel $league, string $title, string $body, array $data = []): void
    {
        $users = $league->users()->get();

        foreach ($users as $user) {
            $this->sendToUser($user, $title, $body, $data);
        }
    }

    public function sendStageReminder(LeagueModel $league, StageModel $stage): void
    {
        $usersWithPrediction = DB::table('predictions')
            ->where('stage_id', $stage->id)
            ->pluck('user_id');

        $usersWithoutPrediction = $league->users()
            ->whereNotIn('users.id', $usersWithPrediction)
            ->get();

        foreach ($usersWithoutPrediction as $user) {
            $this->sendToUser(
                $user,
                'Recordatorio de pronóstico',
                "La etapa {$stage->number} cierra en 5 minutos — ¡haz tu pronóstico!",
                [
                    'type' => 'stage_reminder',
                    'stage_id' => $stage->id,
                    'league_id' => $league->id,
                    'url' => "/leagues/{$league->id}/stage/{$stage->id}",
                ]
            );
        }
    }

    public function sendStageResults(LeagueModel $league, StageModel $stage, int $userPoints): void
    {
        $this->sendToLeague(
            $league,
            'Resultados publicados',
            "Etapa {$stage->number}: resultados listos. Puntuaste +{$userPoints} pts",
            [
                'type' => 'stage_results',
                'stage_id' => $stage->id,
                'league_id' => $league->id,
                'points' => $userPoints,
                'url' => "/leagues/{$league->id}/classification",
            ]
        );
    }

    public function sendCompetitionFinished(LeagueModel $league, string $userName, int $position): void
    {
        $this->sendToUser(
            $league->owner,
            'Competición finalizada',
            "{$league->edition->competition->name} {$league->edition->year} ha terminado. Posición final: #{$position}",
            [
                'type' => 'competition_finished',
                'league_id' => $league->id,
                'url' => "/leagues/{$league->id}/classification",
            ]
        );
    }

    public function cleanStaleSubscriptions(): int
    {
        return PushSubscriptionModel::where('last_used_at', '<', now()->subDays(30))
            ->orWhereNull('last_used_at')
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
    }
}
