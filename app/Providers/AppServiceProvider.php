<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Services\PushNotificationService;
use App\Domain\Interfaces\CyclingDataFetcherInterface;
use App\Infrastructure\Services\MockCyclingDataFetcher;
use App\Presentation\Console\CleanPushSubscriptionsCommand;
use App\Presentation\Console\Deploy\AppDeployCommand;
use App\Presentation\Console\ImportResultsCommand;
use App\Presentation\Console\ImportStagesCommand;
use App\Presentation\Console\LockPredictionsCommand;
use App\Presentation\Console\RebuildScoresCommand;
use App\Presentation\Console\ScorePreRaceCommand;
use App\Presentation\Console\ScoreStageCommand;
use App\Presentation\Console\TestPushNotificationCommand;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Messaging;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CyclingDataFetcherInterface::class, MockCyclingDataFetcher::class);

        $this->app->bind(PushNotificationService::class, function (): PushNotificationService {
            try {
                $messaging = app(Messaging::class);

                return new PushNotificationService($messaging);
            } catch (\Throwable) {
                return new PushNotificationService;
            }
        });
    }

    public function boot(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        $this->commands([
            AppDeployCommand::class,
            ImportStagesCommand::class,
            ImportResultsCommand::class,
            ScoreStageCommand::class,
            RebuildScoresCommand::class,
            ScorePreRaceCommand::class,
            LockPredictionsCommand::class,
            CleanPushSubscriptionsCommand::class,
            TestPushNotificationCommand::class,
        ]);
    }
}
