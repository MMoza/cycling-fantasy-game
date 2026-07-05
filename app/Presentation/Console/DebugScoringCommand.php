<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\StageResultModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use Illuminate\Console\Command;

class DebugScoringCommand extends Command
{
    protected $signature = 'race:debug-scoring {stage_id : The stage UUID to inspect}';

    protected $description = 'Show predictions, results, and scores for a stage to debug scoring';

    public function handle(): int
    {
        $stage = StageModel::find($this->argument('stage_id'));

        if (! $stage) {
            $this->error('Stage not found.');

            return self::FAILURE;
        }

        $riderNames = RiderModel::pluck('first_name', 'id');
        $teamNames = TeamModel::pluck('name', 'id');

        $this->info("Stage {$stage->number}: {$stage->name}");
        $this->line("Status: {$stage->status->value} | Difficulty: {$stage->difficulty}");
        $this->newLine();

        // Results
        $results = StageResultModel::where('stage_id', $stage->id)->orderBy('position')->get();

        if ($results->isEmpty()) {
            $this->warn('No results saved for this stage.');
        } else {
            $this->info('REAL RESULTS');
            $resultRows = $results->map(fn ($r) => [
                $r->position === 0 ? 'Winner' : "#{$r->position}",
                $riderNames[$r->rider_id] ?? "? ({$r->rider_id})",
                $r->is_gc_leader ? 'GC Leader' : '',
                $r->is_combativo ? 'Combativo' : '',
            ]);
            $this->table(['Position', 'Rider', 'GC Leader', 'Combativo'], $resultRows);
        }

        $this->newLine();

        // Predictions
        $predictions = PredictionModel::with('user', 'league')
            ->where('stage_id', $stage->id)
            ->where('type', 'pre_stage')
            ->orderBy('league_id')
            ->get();

        if ($predictions->isEmpty()) {
            $this->warn('No predictions for this stage.');
        } else {
            $this->info('PREDICTIONS');

            $predRows = $predictions->map(function ($p) use ($riderNames, $teamNames) {
                $cat = $p->category->value;
                $val = $p->prediction_value;

                $predicted = isset($val['team_id'])
                    ? ($teamNames[$val['team_id']] ?? "? ({$val['team_id']})")
                    : (isset($val['rider_id'])
                        ? ($riderNames[$val['rider_id']] ?? "? ({$val['rider_id']})")
                        : json_encode($val));

                return [
                    $p->league->name,
                    $p->user->name,
                    $cat,
                    $predicted,
                    $p->locked_at?->diffForHumans() ?? 'Not locked',
                ];
            });

            $this->table(['League', 'User', 'Category', 'Predicted', 'Locked'], $predRows);
        }

        $this->newLine();

        // Score events
        $scoreEvents = ScoreEventModel::with('user')
            ->where('stage_id', $stage->id)
            ->orderBy('user_id')
            ->get();

        if ($scoreEvents->isEmpty()) {
            $this->warn('No score events for this stage.');
        } else {
            $this->info('SCORE EVENTS');

            $scoreRows = $scoreEvents->map(fn ($s) => [
                $s->user->name,
                $s->points > 0 ? "+{$s->points}" : '0',
                $s->description,
            ]);

            $this->table(['User', 'Points', 'Description'], $scoreRows);

            $totalByUser = $scoreEvents->groupBy('user_id')->map(fn ($events) => $events->sum('points'));
            $this->newLine();
            $this->line('Total per user:');
            foreach ($totalByUser as $userId => $total) {
                $userName = $scoreEvents->firstWhere('user_id', $userId)->user->name;
                $this->line("  {$userName}: {$total} pts");
            }
        }

        return self::SUCCESS;
    }
}
