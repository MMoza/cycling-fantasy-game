<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateStageUseCase
{
    public function execute(string $editionId, string $id, array $data, ?UploadedFile $profileImage = null): void
    {
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        $updateData = collect($data)->except('profile_image')->toArray();

        if ($profileImage) {
            $path = $profileImage->store('stages/profiles', 'public');
            $updateData['profile_image'] = Storage::url($path);
        }

        $stage->update($updateData);
    }
}
