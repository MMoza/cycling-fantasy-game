<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\ValueObjects\ActivityLogType;
use App\Infrastructure\Persistence\Models\ActivityLogModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MockPredictionsLockedLogCommand extends Command
{
    protected $signature = 'mock:predictions-locked {league_id?}';

    protected $description = 'Insert a mock predictions_locked activity log for testing';

    public function handle(): int
    {
        $leagueId = $this->argument('league_id');

        $query = LeagueModel::query();
        if ($leagueId) {
            $query->where('id', $leagueId);
        }

        $league = $query->firstOrFail();
        $stage = $league->edition->stages()->orderBy('number')->first();

        if (! $stage) {
            $this->error('No stages found for this league');

            return self::FAILURE;
        }

        $riderNames = [
            ['rider_id' => 'mock-1', 'name' => 'Pogačar', 'count' => 8],
            ['rider_id' => 'mock-2', 'name' => 'Vingegaard', 'count' => 5],
            ['rider_id' => 'mock-3', 'name' => 'Evenepoel', 'count' => 3],
        ];

        $names = array_column($riderNames, 'name');
        $counts = array_column($riderNames, 'count');
        $formatted = array_map(fn ($n, $c) => "{$n} ({$c})", $names, $counts);

        ActivityLogModel::create([
            'id' => Str::uuid(),
            'league_id' => $league->id,
            'type' => ActivityLogType::PredictionsLocked,
            'title' => "Apuestas cerradas — Etapa {$stage->number}: {$stage->name}",
            'description' => 'Favoritos: '.implode(', ', $formatted),
            'data' => [
                'stage_id' => $stage->id,
                'stage_number' => $stage->number,
                'top_riders' => $riderNames,
            ],
        ]);

        $this->info("Mock predictions_locked log created for league {$league->id}, stage {$stage->number}");

        return self::SUCCESS;
    }
}
