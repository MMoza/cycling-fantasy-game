<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\CompetitionType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreStageUseCase
{
    public function execute(string $editionId, array $data, ?UploadedFile $profileImage = null): StageModel
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);

        if ($edition->competition->type === CompetitionType::Classic) {
            $existing = StageModel::where('edition_id', $editionId)->count();
            if ($existing >= 1) {
                throw new ApplicationException('Las clásicas solo pueden tener una etapa.');
            }
        }

        $imagePath = null;

        if ($profileImage) {
            $path = $profileImage->store('stages/profiles', 'public');
            $imagePath = Storage::url($path);
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
            'profile_image' => $imagePath,
            'status' => 'upcoming',
        ]);
    }
}
