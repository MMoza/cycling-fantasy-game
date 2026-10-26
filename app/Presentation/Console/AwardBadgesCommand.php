<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Services\BadgeService;
use App\Models\User;
use Illuminate\Console\Command;

class AwardBadgesCommand extends Command
{
    protected $signature = 'season:award-badges
        {year? : Only users with predictions in this season}
        {--user= : Recalculate a single user}
        {--dry-run : Show what would change without applying it}';

    protected $description = 'Recalculate user badges and their milestone history (also awards the TESTER de Pedales badge)';

    public function handle(BadgeService $badgeService): int
    {
        $year = $this->argument('year') !== null ? (int) $this->argument('year') : null;
        $userId = $this->option('user') ?: null;
        $dryRun = (bool) $this->option('dry-run');

        if ($userId !== null && User::query()->find($userId) === null) {
            $this->error("Usuario no encontrado: {$userId}");

            return self::FAILURE;
        }

        $report = $badgeService->recalculate($year, $userId, $dryRun);

        if ($dryRun) {
            $this->warn('DRY RUN — no se ha guardado ningún badge.');
        }

        $names = User::whereIn('id', array_keys($report))->pluck('name', 'id');
        $earnedCount = 0;
        $progressedCount = 0;

        foreach ($report as $uid => $changes) {
            if ($changes['earned'] === [] && $changes['progressed'] === []) {
                continue;
            }

            $this->line($names[$uid] ?? $uid);

            foreach ($changes['earned'] as $badge) {
                $this->line("  + badge: {$badge}");
                $earnedCount++;
            }

            foreach ($changes['progressed'] as $badge) {
                $this->line("  ~ progreso: {$badge}");
                $progressedCount++;
            }
        }

        $this->info('Usuarios procesados: '.count($report)." | nuevos niveles: {$earnedCount} | avances de progreso: {$progressedCount}");

        return self::SUCCESS;
    }
}
