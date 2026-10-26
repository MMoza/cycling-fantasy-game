<?php

declare(strict_types=1);

namespace App\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Shared query for predictions that correctly guessed the winner of a stage
 * (the rider matching the stage_results row with position 1).
 */
class PredictionHitService
{
    /**
     * @param  string|null  $leagueId  limit to a league (null = every league)
     * @param  string  $category  prediction category ('stage_winner', 'stage_position_N'…)
     * @param  array<int, string>|null  $stageIds  restrict to specific stages
     * @param  bool  $excludeUpcomingStages  skip stages that have not started
     * @return Collection<int, object{user_id: string, stage_id: string}>
     */
    public function correctWinnerPredictions(
        ?string $leagueId = null,
        ?string $userId = null,
        string $category = 'stage_winner',
        ?array $stageIds = null,
        bool $excludeUpcomingStages = false,
    ): Collection {
        $query = DB::table('predictions')
            ->join('stage_results', function ($join) {
                $join->on('predictions.stage_id', '=', 'stage_results.stage_id')
                    ->where('stage_results.position', '=', 1);
            })
            ->where('predictions.category', '=', $category);

        if ($leagueId !== null) {
            $query->where('predictions.league_id', $leagueId);
        }

        if ($userId !== null) {
            $query->where('predictions.user_id', $userId);
        }

        if ($stageIds !== null) {
            $query->whereIn('predictions.stage_id', $stageIds);
        }

        if ($excludeUpcomingStages) {
            $query->join('stages', 'stages.id', '=', 'predictions.stage_id')
                ->where('stages.status', '!=', 'upcoming');
        }

        // La comparación del JSON se hace en PHP: es portable entre SQLite y
        // MySQL y soporta los formatos reales de prediction_value
        // (['rider_id' => x] y, en legado, arrays planos).
        return $query->select('predictions.user_id', 'predictions.stage_id', 'predictions.prediction_value', 'stage_results.rider_id')
            ->get()
            ->filter(function ($row) {
                $value = json_decode($row->prediction_value ?? 'null', true);
                $predicted = $value['rider_id'] ?? $value;

                return is_string($predicted) && $predicted === $row->rider_id;
            })
            ->map(fn ($row) => (object) ['user_id' => $row->user_id, 'stage_id' => $row->stage_id])
            ->values();
    }
}
