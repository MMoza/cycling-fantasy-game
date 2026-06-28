<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\User;

use App\Application\DTOs\Admin\UserDTO;
use App\Models\User;

class ListUsersUseCase
{
    public function execute(): array
    {
        return User::orderBy('name')
            ->get()
            ->map(fn ($u) => new UserDTO(
                id: $u->id,
                name: $u->name,
                email: $u->email,
                isAdmin: $u->is_admin,
                leaguesCount: $u->leagues()->count(),
                createdAt: $u->created_at->format('Y-m-d'),
            ))
            ->toArray();
    }
}
