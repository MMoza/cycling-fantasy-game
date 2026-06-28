<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Team;

use App\Infrastructure\Persistence\Models\TeamModel;

class UpdateTeamUseCase
{
    public function execute(string $id, array $data): void
    {
        $team = TeamModel::findOrFail($id);

        if (! empty($data['abbreviation'])) {
            $data['abbreviation'] = strtoupper($data['abbreviation']);
        }

        $team->update($data);
    }
}
