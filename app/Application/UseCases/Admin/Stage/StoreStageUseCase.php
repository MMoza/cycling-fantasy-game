<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\CompetitionType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Support\Str;

class StoreStageUseCase
{
    public function execute(string $editionId, array $data): StageModel
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);

        if ($edition->competition->type === CompetitionType::Classic) {
            $existing = StageModel::where('edition_id', $editionId)->count();
            if ($existing >= 1) {
                throw new ApplicationException('Las clásicas solo pueden tener una etapa.');
            }
        }

        return StageModel::create([
            'id' => Str::uuid()->toString(),
            'edition_id' => $edition->id,
            'number' => $data['number'],
            'name' => $data['name'],
            'date' => $data['date'],
            'type' => $data['type'],
            'distance' => $data['distance'] ?? null,
            'elevation_gain' => $data['elevation_gain'] ?? null,
            'difficulty' => $data['difficulty'] ?? null,
            'origin' => $data['origin'],
            'destination' => $data['destination'],
            'scheduled_start' => $data['scheduled_start'] ?? null,
            'profile_image' => $data['profile_image'] ?? null,
            'live_stream_url' => $data['live_stream_url'] ?? null,
            'status' => 'upcoming',
        ]);
    }
}
