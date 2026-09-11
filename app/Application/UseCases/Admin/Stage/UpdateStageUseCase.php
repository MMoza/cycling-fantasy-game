<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateStageUseCase
{
    public function execute(string $editionId, string $id, array $data): void
    {
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        $stage->update($data);

        if (array_key_exists('rider_ids', $data)) {
            $this->syncParticipants($stage->id, $editionId, $data['rider_ids']);
        }
    }

    private function syncParticipants(string $stageId, string $editionId, array $riderIds): void
    {
        StageParticipantModel::where('stage_id', $stageId)->delete();

        if ($riderIds === []) {
            return;
        }

        $participants = DB::table('competition_participants')
            ->where('edition_id', $editionId)
            ->whereIn('rider_id', $riderIds)
            ->select('rider_id', 'team_id')
            ->get();

        foreach ($participants as $p) {
            StageParticipantModel::create([
                'id' => Str::uuid()->toString(),
                'stage_id' => $stageId,
                'rider_id' => $p->rider_id,
                'team_id' => $p->team_id,
            ]);
        }
    }
}
