<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Rider;

use App\Infrastructure\Persistence\Models\RiderModel;

class UpdateRiderUseCase
{
    public function execute(string $id, array $data): void
    {
        $rider = RiderModel::findOrFail($id);
        $rider->update($data);
    }
}
