<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Email;

use App\Infrastructure\Persistence\Models\ScheduledEmailModel;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListEmailsUseCase
{
    public function execute(?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = ScheduledEmailModel::with('creator')
            ->orderByDesc('created_at');

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }
}
