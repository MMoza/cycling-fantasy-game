<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateStage3PredictionsCommand extends Command
{
    protected $signature = 'predictions:create-stage3 {leagueId} {userId}';

    protected $description = 'Create stage 3 predictions for a user (hotfix)';

    public function handle(): int
    {
        $leagueId = $this->argument('leagueId');
        $userId = $this->argument('userId');

        $league = LeagueModel::findOrFail($leagueId);
        $stage = $league->stages()->where('number', 3)->firstOrFail();

        $this->info("Etapa {$stage->number}: {$stage->name}");

        $healy = RiderModel::where('last_name', 'Healy')->firstOrFail();
        $aranburu = RiderModel::where('last_name', 'Aranburu')->firstOrFail();
        $pogacar = RiderModel::where('last_name', 'Pogačar')->firstOrFail();
        $molenaar = RiderModel::where('last_name', 'Molenaar')->firstOrFail();

        $this->line("Healy: {$healy->full_name}");
        $this->line("Aranburu: {$aranburu->full_name}");
        $this->line("Pogacar: {$pogacar->full_name}");
        $this->line("Molenaar: {$molenaar->full_name}");

        PredictionModel::where('league_id', $leagueId)
            ->where('user_id', $userId)
            ->where('stage_id', $stage->id)
            ->delete();

        $predictions = [
            'stage_winner' => $healy->id,
            'stage_second' => $aranburu->id,
            'stage_third' => $pogacar->id,
            'stage_leader' => $pogacar->id,
            'stage_combativo' => $molenaar->id,
        ];

        foreach ($predictions as $category => $riderId) {
            PredictionModel::create([
                'id' => Str::uuid()->toString(),
                'user_id' => $userId,
                'league_id' => $leagueId,
                'type' => 'pre_stage',
                'category' => $category,
                'stage_id' => $stage->id,
                'prediction_value' => ['rider_id' => $riderId],
            ]);
            $this->info("  Creado: {$category}");
        }

        $this->newLine();
        $this->info("¡Listo! 5 pronósticos creados.");

        return self::SUCCESS;
    }
}
