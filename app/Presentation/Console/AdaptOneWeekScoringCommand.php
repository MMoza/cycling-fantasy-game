<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\League\AdaptOneWeekScoringUseCase;
use Illuminate\Console\Command;

class AdaptOneWeekScoringCommand extends Command
{
    protected $signature = 'season:adapt-one-week-scoring
        {year=2027 : Season (year) whose one-week races should be adapted}
        {--all : Also replace non-standard scoring systems}
        {--dry-run : Show what would change without applying it}';

    protected $description = 'Set the OneWeek scoring system on the one-week stage race leagues of a season (default: only Standard systems)';

    public function handle(AdaptOneWeekScoringUseCase $useCase): int
    {
        $year = (int) $this->argument('year');
        $all = (bool) $this->option('all');
        $dryRun = (bool) $this->option('dry-run');

        try {
            $result = $useCase->execute($year, $all, $dryRun);
        } catch (ApplicationException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn('DRY RUN — no se ha aplicado ningún cambio.');
        }

        if ($result->updated !== []) {
            $this->info("Ligas adaptadas al sistema OneWeek ({$year}):");
            $this->table(
                ['Liga', 'Sistema anterior', 'Sistema nuevo'],
                array_map(fn (array $row) => [$row['league'], $row['from'], $row['to']], $result->updated),
            );
        } else {
            $this->info("Ninguna liga que adaptar en {$year}.");
        }

        if ($result->alreadyOneWeek !== []) {
            $this->line('Ya usaban OneWeek: '.implode(', ', array_column($result->alreadyOneWeek, 'league')));
        }

        if ($result->skipped !== []) {
            $this->warn('Omitidas (sistema distinto de Standard; usa --all para incluirlas):');
            $this->table(
                ['Liga', 'Sistema actual'],
                array_map(fn (array $row) => [$row['league'], $row['system']], $result->skipped),
            );
        }

        if (! $dryRun && $result->updated !== []) {
            $this->newLine();
            $this->warn('Los puntos existentes se calcularon con el sistema anterior. Para dejarlos coherentes:');
            foreach ($result->updated as $row) {
                $this->line("  php artisan race:rebuild-scores {$row['id']}");
            }
        }

        return self::SUCCESS;
    }
}
