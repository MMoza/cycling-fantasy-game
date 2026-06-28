<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Rider;

use App\Infrastructure\Persistence\Models\RiderModel;

class StoreRiderUseCase
{
    public function execute(array $data): RiderModel
    {
        return RiderModel::create($data);
    }
}
