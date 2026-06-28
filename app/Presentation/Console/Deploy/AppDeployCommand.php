<?php

declare(strict_types=1);

namespace App\Presentation\Console\Deploy;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AppDeployCommand extends Command
{
    protected $signature = 'app:deploy {--seed : Run database seeders}';

    protected $description = 'Run deployment tasks: migrations, and optionally seeders';

    public function handle(): int
    {
        $this->components->task('Running migrations', function () {
            Artisan::call('migrate', ['--force' => true]);
        });

        if ($this->option('seed')) {
            $this->components->task('Running database seeders', function () {
                Artisan::call('db:seed', ['--force' => true]);
            });
        }

        $this->components->info('Deployment completed successfully.');

        return Command::SUCCESS;
    }
}
