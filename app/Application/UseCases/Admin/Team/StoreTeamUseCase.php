<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Team;

use App\Infrastructure\Persistence\Models\TeamModel;

class StoreTeamUseCase
{
    public function execute(array $data): TeamModel
    {
        if (! empty($data['abbreviation'])) {
            $data['abbreviation'] = strtoupper($data['abbreviation']);
        }

        return TeamModel::create($data);
    }
}
