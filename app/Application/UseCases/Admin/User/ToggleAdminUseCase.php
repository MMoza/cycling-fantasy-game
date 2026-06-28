<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\User;

use App\Models\User;

class ToggleAdminUseCase
{
    public function execute(string $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['is_admin' => ! $user->is_admin]);
    }
}
