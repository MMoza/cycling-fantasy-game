<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use Illuminate\Console\Command;

class RebuildScoresCommand extends Command
{
    protected $signature = 'race:rebuild-scores {league_id? : The league UUID to rebuild (omit for all)}';

    protected $description = 'Recalculate all scores from ScoreEvents for a league or all leagues';

    public function handle(): int
    {
        $leagueId = $this->argument('league_id');

        $query = \Illuminate\Support\Facades\DB::table('score_events');

        if ($leagueId) {
            $query->where('league_id', $leagueId);
        }

        $totalScoreEvents = $query->count();

        if ($totalScoreEvents === 0) {
            $this->warn('No score events found');

            return self::SUCCESS;
        }

        $this->info("Found {$totalScoreEvents} score events to process");

        $totals = $query
            ->selectRaw('league_id, user_id, SUM(points) as total_points, COUNT(*) as events')
            ->groupBy('league_id', 'user_id')
            ->get();

        $this->table(
            ['League', 'User', 'Total Points', 'Events'],
            $totals->map(fn ($row) => [
                substr($row->league_id, 0, 8) . '...',
                substr($row->user_id, 0, 8) . '...',
                $row->total_points,
                $row->events,
            ])->toArray()
        );

        $this->info("Score totals calculated for {$totals->count()} user-league combinations");

        return self::SUCCESS;
    }
}
